<?php

// Crafting System Implementation  
class CraftingController {
    private $database;
    
    public function __construct() {
        $this->database = new Database();
    }
    
    public function workshop() {
        if (!Auth::check()) {
            return Utils::redirect('/login');
        }
        
        $userId = Auth::userId();
        
        // Get crafting recipes available to player
        $recipes = $this->getAvailableRecipes($userId);
        
        // Get player's crafting materials
        $materials = $this->getCraftingMaterials($userId);
        
        // Get crafting stations/workshops
        $workshops = $this->getAvailableWorkshops($userId);
        
        // Get crafting skills
        $skills = $this->getCraftingSkills($userId);
        
        return Utils::render('game/crafting', [
            'recipes' => $recipes,
            'materials' => $materials,
            'workshops' => $workshops,
            'skills' => $skills,
            'activeProjects' => $this->getActiveProjects($userId)
        ]);
    }
    
    public function startCrafting() {
        if (!Auth::check()) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Not authenticated']);
        }
        
        $userId = Auth::userId();
        $recipeId = $_POST['recipe_id'] ?? null;
        $quantity = (int)($_POST['quantity'] ?? 1);
        $workshopId = $_POST['workshop_id'] ?? null;
        
        if (!$recipeId || $quantity < 1) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Invalid parameters']);
        }
        
        // Get recipe details
        $recipe = $this->getRecipe($recipeId);
        if (!$recipe) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Recipe not found']);
        }
        
        // Check if player has required skill level
        $playerSkill = $this->getPlayerSkill($userId, $recipe['skill_required']);
        if ($playerSkill < $recipe['skill_level']) {
            return Utils::jsonResponse([
                'success' => false, 
                'message' => "Vyžaduje {$recipe['skill_required']} level {$recipe['skill_level']}"
            ]);
        }
        
        // Check material requirements
        $materialsCheck = $this->checkMaterials($userId, $recipeId, $quantity);
        if (!$materialsCheck['success']) {
            return Utils::jsonResponse($materialsCheck);
        }
        
        // Check workshop requirements
        if ($recipe['workshop_required']) {
            $workshopCheck = $this->checkWorkshop($userId, $recipe['workshop_required'], $workshopId);
            if (!$workshopCheck['success']) {
                return Utils::jsonResponse($workshopCheck);
            }
        }
        
        try {
            $this->database->pdo->beginTransaction();
            
            // Consume materials
            $this->consumeMaterials($userId, $recipeId, $quantity);
            
            // Create crafting project
            $projectId = $this->createCraftingProject($userId, $recipeId, $quantity, $workshopId);
            
            // Grant crafting experience
            $expGained = $recipe['experience_reward'] * $quantity;
            $this->grantCraftingExp($userId, $recipe['skill_required'], $expGained);
            
            $this->database->pdo->commit();
            
            return Utils::jsonResponse([
                'success' => true,
                'message' => 'Crafting zahájen!',
                'project_id' => $projectId,
                'experience_gained' => $expGained
            ]);
            
        } catch (Exception $e) {
            $this->database->pdo->rollback();
            return Utils::jsonResponse(['success' => false, 'message' => 'Crafting failed: ' . $e->getMessage()]);
        }
    }
    
    public function completeCrafting() {
        if (!Auth::check()) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Not authenticated']);
        }
        
        $userId = Auth::userId();
        $projectId = $_POST['project_id'] ?? null;
        
        if (!$projectId) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Project ID required']);
        }
        
        // Get project details
        $project = $this->database->query("
            SELECT cp.*, r.result_item_id, r.result_quantity, r.name as recipe_name
            FROM crafting_projects cp
            JOIN recipes r ON cp.recipe_id = r.id
            WHERE cp.id = ? AND cp.user_id = ? AND cp.status = 'in_progress'
        ", [$projectId, $userId])->fetch(PDO::FETCH_ASSOC);
        
        if (!$project) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Project not found or already completed']);
        }
        
        // Check if crafting is finished
        if (strtotime($project['completion_time']) > time()) {
            $timeLeft = strtotime($project['completion_time']) - time();
            return Utils::jsonResponse([
                'success' => false, 
                'message' => 'Crafting není dokončen',
                'time_remaining' => $timeLeft
            ]);
        }
        
        try {
            $this->database->pdo->beginTransaction();
            
            // Mark project as completed
            $this->database->query("
                UPDATE crafting_projects 
                SET status = 'completed', completed_at = NOW() 
                WHERE id = ?
            ", [$projectId]);
            
            // Add crafted items to inventory
            $totalItems = $project['result_quantity'] * $project['quantity'];
            $this->addItemToInventory($userId, $project['result_item_id'], $totalItems);
            
            // Calculate quality bonus based on skill
            $qualityBonus = $this->calculateQualityBonus($userId, $project['recipe_id']);
            
            // Chance for bonus items based on skill
            if ($qualityBonus > 0.8 && rand(1, 100) <= 20) {
                $bonusItems = ceil($totalItems * 0.1);
                $this->addItemToInventory($userId, $project['result_item_id'], $bonusItems);
                $message = "Crafting dokončen! Získáno {$totalItems} + {$bonusItems} (kvalitní práce!)";
            } else {
                $message = "Crafting dokončen! Získáno {$totalItems} předmětů";
            }
            
            $this->database->pdo->commit();
            
            return Utils::jsonResponse([
                'success' => true,
                'message' => $message,
                'items_crafted' => $totalItems,
                'quality_bonus' => $qualityBonus
            ]);
            
        } catch (Exception $e) {
            $this->database->pdo->rollback();
            return Utils::jsonResponse(['success' => false, 'message' => 'Failed to complete crafting: ' . $e->getMessage()]);
        }
    }
    
    public function learnRecipe() {
        if (!Auth::check()) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Not authenticated']);
        }
        
        $userId = Auth::userId();
        $recipeId = $_POST['recipe_id'] ?? null;
        $method = $_POST['method'] ?? 'book'; // book, npc, discovery
        
        if (!$recipeId) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Recipe ID required']);
        }
        
        // Check if player already knows the recipe
        $known = $this->database->query("
            SELECT id FROM known_recipes 
            WHERE user_id = ? AND recipe_id = ?
        ", [$userId, $recipeId])->fetch(PDO::FETCH_ASSOC);
        
        if ($known) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Recept už znáš']);
        }
        
        $recipe = $this->getRecipe($recipeId);
        if (!$recipe) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Recipe not found']);
        }
        
        $cost = 0;
        $requiredItems = [];
        
        switch ($method) {
            case 'book':
                // Learning from schematic/book
                $schematicId = $recipe['schematic_item_id'];
                if ($schematicId) {
                    $hasSchematic = $this->database->query("
                        SELECT quantity FROM user_inventory 
                        WHERE user_id = ? AND item_id = ? AND quantity > 0
                    ", [$userId, $schematicId])->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$hasSchematic) {
                        return Utils::jsonResponse(['success' => false, 'message' => 'Nemáš potřebný manuál']);
                    }
                    
                    $requiredItems[] = ['id' => $schematicId, 'quantity' => 1];
                }
                break;
                
            case 'npc':
                // Learning from NPC trainer
                $cost = $recipe['learning_cost'] ?? 100;
                break;
                
            case 'discovery':
                // Discovery through experimentation
                $expCost = $recipe['discovery_exp_cost'] ?? 50;
                break;
        }
        
        try {
            $this->database->pdo->beginTransaction();
            
            // Handle payment/requirements
            if ($method === 'book' && !empty($requiredItems)) {
                foreach ($requiredItems as $item) {
                    $this->database->query("
                        UPDATE user_inventory 
                        SET quantity = quantity - ? 
                        WHERE user_id = ? AND item_id = ?
                    ", [$item['quantity'], $userId, $item['id']]);
                }
            } elseif ($method === 'npc' && $cost > 0) {
                // Deduct caps
                $this->database->query("
                    UPDATE characters 
                    SET caps = caps - ? 
                    WHERE user_id = ? AND caps >= ?
                ", [$cost, $userId, $cost]);
                
                if ($this->database->pdo->rowCount() === 0) {
                    throw new Exception('Nedostatek caps');
                }
            }
            
            // Learn the recipe
            $this->database->query("
                INSERT INTO known_recipes (user_id, recipe_id, learned_at, learning_method)
                VALUES (?, ?, NOW(), ?)
            ", [$userId, $recipeId, $method]);
            
            $this->database->pdo->commit();
            
            return Utils::jsonResponse([
                'success' => true,
                'message' => "Naučil jsi se recept: {$recipe['name']}"
            ]);
            
        } catch (Exception $e) {
            $this->database->pdo->rollback();
            return Utils::jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    public function upgradeWorkshop() {
        if (!Auth::check()) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Not authenticated']);
        }
        
        $userId = Auth::userId();
        $workshopType = $_POST['workshop_type'] ?? null;
        
        if (!$workshopType) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Workshop type required']);
        }
        
        // Get current workshop level
        $currentLevel = $this->getWorkshopLevel($userId, $workshopType);
        $nextLevel = $currentLevel + 1;
        
        // Get upgrade requirements
        $requirements = $this->getUpgradeRequirements($workshopType, $nextLevel);
        if (!$requirements) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Maximum level reached']);
        }
        
        // Check requirements
        $checksPass = true;
        $missingItems = [];
        
        foreach ($requirements['materials'] as $material) {
            $available = $this->database->query("
                SELECT quantity FROM user_inventory 
                WHERE user_id = ? AND item_id = ?
            ", [$userId, $material['item_id']])->fetch(PDO::FETCH_ASSOC);
            
            if (!$available || $available['quantity'] < $material['quantity']) {
                $checksPass = false;
                $missingItems[] = $material;
            }
        }
        
        if (!$checksPass) {
            return Utils::jsonResponse([
                'success' => false, 
                'message' => 'Nedostatek materiálů',
                'missing_items' => $missingItems
            ]);
        }
        
        try {
            $this->database->pdo->beginTransaction();
            
            // Consume materials
            foreach ($requirements['materials'] as $material) {
                $this->database->query("
                    UPDATE user_inventory 
                    SET quantity = quantity - ? 
                    WHERE user_id = ? AND item_id = ?
                ", [$material['quantity'], $userId, $material['item_id']]);
            }
            
            // Upgrade workshop
            if ($currentLevel === 0) {
                // Create new workshop
                $this->database->query("
                    INSERT INTO player_workshops (user_id, workshop_type, level, created_at)
                    VALUES (?, ?, ?, NOW())
                ", [$userId, $workshopType, $nextLevel]);
            } else {
                // Upgrade existing
                $this->database->query("
                    UPDATE player_workshops 
                    SET level = ?, upgraded_at = NOW() 
                    WHERE user_id = ? AND workshop_type = ?
                ", [$nextLevel, $userId, $workshopType]);
            }
            
            $this->database->pdo->commit();
            
            return Utils::jsonResponse([
                'success' => true,
                'message' => "Workshop {$workshopType} upgradován na level {$nextLevel}!"
            ]);
            
        } catch (Exception $e) {
            $this->database->pdo->rollback();
            return Utils::jsonResponse(['success' => false, 'message' => 'Upgrade failed: ' . $e->getMessage()]);
        }
    }
    
    // Helper methods
    private function getAvailableRecipes($userId) {
        return $this->database->query("
            SELECT r.*, kr.learned_at,
                   i.name as result_item_name,
                   i.icon as result_item_icon
            FROM recipes r
            LEFT JOIN known_recipes kr ON r.id = kr.recipe_id AND kr.user_id = ?
            LEFT JOIN items i ON r.result_item_id = i.id
            WHERE kr.id IS NOT NULL OR r.discoverable = 1
            ORDER BY r.category, r.name
        ", [$userId])->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getCraftingMaterials($userId) {
        return $this->database->query("
            SELECT ui.*, i.name, i.icon, ic.name as category_name
            FROM user_inventory ui
            JOIN items i ON ui.item_id = i.id  
            JOIN item_categories ic ON i.category_id = ic.id
            WHERE ui.user_id = ? AND ic.craftable_material = 1
            ORDER BY ic.name, i.name
        ", [$userId])->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getAvailableWorkshops($userId) {
        return $this->database->query("
            SELECT * FROM player_workshops 
            WHERE user_id = ?
            ORDER BY workshop_type
        ", [$userId])->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getCraftingSkills($userId) {
        return $this->database->query("
            SELECT skill_name, level, experience, 
                   CASE 
                       WHEN level < 10 THEN (level + 1) * 100
                       ELSE (level + 1) * 150
                   END as exp_to_next
            FROM character_skills 
            WHERE user_id = ? AND skill_type = 'crafting'
        ", [$userId])->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getActiveProjects($userId) {
        return $this->database->query("
            SELECT cp.*, r.name as recipe_name, r.result_item_id,
                   i.name as result_item_name, i.icon as result_item_icon
            FROM crafting_projects cp
            JOIN recipes r ON cp.recipe_id = r.id
            JOIN items i ON r.result_item_id = i.id
            WHERE cp.user_id = ? AND cp.status = 'in_progress'
            ORDER BY cp.completion_time ASC
        ", [$userId])->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getRecipe($recipeId) {
        return $this->database->query("
            SELECT * FROM recipes WHERE id = ?
        ", [$recipeId])->fetch(PDO::FETCH_ASSOC);
    }
    
    private function checkMaterials($userId, $recipeId, $quantity) {
        $materials = $this->database->query("
            SELECT rm.*, i.name as item_name
            FROM recipe_materials rm
            JOIN items i ON rm.item_id = i.id
            WHERE rm.recipe_id = ?
        ", [$recipeId])->fetchAll(PDO::FETCH_ASSOC);
        
        $missing = [];
        
        foreach ($materials as $material) {
            $required = $material['quantity'] * $quantity;
            
            $available = $this->database->query("
                SELECT quantity FROM user_inventory 
                WHERE user_id = ? AND item_id = ?
            ", [$userId, $material['item_id']])->fetch(PDO::FETCH_ASSOC);
            
            $have = $available['quantity'] ?? 0;
            
            if ($have < $required) {
                $missing[] = [
                    'name' => $material['item_name'],
                    'required' => $required,
                    'have' => $have,
                    'need' => $required - $have
                ];
            }
        }
        
        if (!empty($missing)) {
            return [
                'success' => false,
                'message' => 'Nedostatek materiálů',
                'missing_materials' => $missing
            ];
        }
        
        return ['success' => true];
    }
    
    private function createCraftingProject($userId, $recipeId, $quantity, $workshopId) {
        $recipe = $this->getRecipe($recipeId);
        $craftingTime = $recipe['crafting_time'] * $quantity; // seconds
        
        // Workshop efficiency bonus
        if ($workshopId) {
            $workshop = $this->database->query("
                SELECT level FROM player_workshops 
                WHERE id = ? AND user_id = ?
            ", [$workshopId, $userId])->fetch(PDO::FETCH_ASSOC);
            
            if ($workshop) {
                $efficiency = 1 - ($workshop['level'] * 0.1); // 10% faster per level
                $craftingTime *= max(0.5, $efficiency); // Minimum 50% time
            }
        }
        
        $completionTime = date('Y-m-d H:i:s', time() + $craftingTime);
        
        $this->database->query("
            INSERT INTO crafting_projects (
                user_id, recipe_id, quantity, workshop_id,
                status, started_at, completion_time
            ) VALUES (?, ?, ?, ?, 'in_progress', NOW(), ?)
        ", [$userId, $recipeId, $quantity, $workshopId, $completionTime]);
        
        return $this->database->pdo->lastInsertId();
    }
}
?>