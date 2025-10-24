<?php

// Combat System Implementation
class CombatController {
    private $database;
    
    public function __construct() {
        $this->database = new Database();
    }
    
    public function initiate() {
        if (!Auth::check()) {
            return Utils::redirect('/login');
        }
        
        $userId = Auth::userId();
        $enemyType = $_POST['enemy_type'] ?? 'raider';
        $location = $_POST['location'] ?? 'wasteland';
        
        // Check if already in combat
        $existingCombat = $this->database->query("
            SELECT * FROM active_combats 
            WHERE user_id = ? AND status = 'active'
        ", [$userId])->fetch(PDO::FETCH_ASSOC);
        
        if ($existingCombat) {
            return Utils::redirect('/combat/' . $existingCombat['id']);
        }
        
        // Create new combat instance
        $enemy = $this->generateEnemy($enemyType, $location);
        $combatId = $this->createCombat($userId, $enemy);
        
        return Utils::redirect('/combat/' . $combatId);
    }
    
    public function combat($combatId) {
        if (!Auth::check()) {
            return Utils::redirect('/login');
        }
        
        $userId = Auth::userId();
        
        // Get combat data
        $combat = $this->getCombatData($combatId, $userId);
        if (!$combat) {
            return Utils::redirect('/map');
        }
        
        // Get character data
        $character = $this->database->query("
            SELECT * FROM characters WHERE user_id = ?
        ", [$userId])->fetch(PDO::FETCH_ASSOC);
        
        // Get equipped weapon
        $weapon = $this->getEquippedWeapon($userId);
        
        // Get available actions
        $actions = $this->getAvailableActions($userId, $weapon);
        
        return Utils::render('game/combat', [
            'combat' => $combat,
            'character' => $character,
            'weapon' => $weapon,
            'actions' => $actions,
            'combatLog' => $this->getCombatLog($combatId)
        ]);
    }
    
    public function action() {
        if (!Auth::check()) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Not authenticated']);
        }
        
        $userId = Auth::userId();
        $combatId = $_POST['combat_id'] ?? null;
        $actionType = $_POST['action'] ?? null;
        $targetId = $_POST['target_id'] ?? null;
        
        if (!$combatId || !$actionType) {
            return Utils::jsonResponse(['success' => false, 'message' => 'Missing parameters']);
        }
        
        // Verify combat ownership and status
        $combat = $this->getCombatData($combatId, $userId);
        if (!$combat || $combat['status'] !== 'active') {
            return Utils::jsonResponse(['success' => false, 'message' => 'Invalid combat']);
        }
        
        // Check if it's player's turn
        if ($combat['current_turn'] !== 'player') {
            return Utils::jsonResponse(['success' => false, 'message' => 'Not your turn']);
        }
        
        $result = $this->processPlayerAction($combatId, $actionType, $targetId);
        
        // If combat is still active, process enemy turn
        if ($result['success'] && $result['combatStatus'] === 'active') {
            $enemyResult = $this->processEnemyTurn($combatId);
            $result['enemyAction'] = $enemyResult;
        }
        
        return Utils::jsonResponse($result);
    }
    
    private function processPlayerAction($combatId, $actionType, $targetId = null) {
        $combat = $this->database->query("
            SELECT * FROM active_combats WHERE id = ?
        ", [$combatId])->fetch(PDO::FETCH_ASSOC);
        
        $character = $this->database->query("
            SELECT * FROM characters WHERE user_id = ?
        ", [$combat['user_id']])->fetch(PDO::FETCH_ASSOC);
        
        $enemy = json_decode($combat['enemy_data'], true);
        
        switch ($actionType) {
            case 'attack':
                return $this->processAttack($combatId, $character, $enemy, 'melee');
                
            case 'shoot':
                return $this->processAttack($combatId, $character, $enemy, 'ranged');
                
            case 'defend':
                return $this->processDefend($combatId, $character);
                
            case 'use_item':
                return $this->processUseItem($combatId, $character, $targetId);
                
            case 'flee':
                return $this->processFlee($combatId, $character, $enemy);
                
            default:
                return ['success' => false, 'message' => 'Unknown action'];
        }
    }
    
    private function processAttack($combatId, $character, $enemy, $attackType) {
        // Get weapon stats
        $weapon = $this->getEquippedWeapon($character['user_id']);
        
        // Calculate damage
        $baseDamage = $weapon ? $weapon['damage'] : 5; // Unarmed damage
        $damage = $baseDamage + floor($character['strength'] / 2);
        
        // Apply accuracy modifiers
        $accuracy = 75 + $character['perception'] * 2;
        if ($attackType === 'ranged') {
            $accuracy += 10;
        }
        
        // Check if attack hits
        $hitChance = rand(1, 100);
        $hit = $hitChance <= $accuracy;
        
        if ($hit) {
            // Apply damage modifiers
            $critChance = 5 + $character['luck'];
            $isCrit = rand(1, 100) <= $critChance;
            
            if ($isCrit) {
                $damage *= 2;
            }
            
            // Apply armor reduction
            $armorReduction = $enemy['armor'] ?? 0;
            $finalDamage = max(1, $damage - $armorReduction);
            
            // Update enemy health
            $enemy['health'] -= $finalDamage;
            
            // Log the action
            $logMessage = "Útok {$attackType} zasáhl za {$finalDamage} poškození" . ($isCrit ? " (kritický zásah!)" : "");
            
        } else {
            $logMessage = "Útok minul!";
            $finalDamage = 0;
        }
        
        // Update combat
        $this->updateCombat($combatId, $enemy, 'enemy');
        $this->addCombatLog($combatId, 'player', $logMessage);
        
        // Check if enemy is defeated
        if ($enemy['health'] <= 0) {
            return $this->endCombat($combatId, 'victory');
        }
        
        return [
            'success' => true,
            'hit' => $hit,
            'damage' => $finalDamage ?? 0,
            'critical' => $isCrit ?? false,
            'message' => $logMessage,
            'combatStatus' => 'active',
            'enemyHealth' => $enemy['health']
        ];
    }
    
    private function processDefend($combatId, $character) {
        // Defending increases armor for next enemy attack
        $this->database->query("
            UPDATE active_combats 
            SET player_defense_bonus = 5,
                current_turn = 'enemy'
            WHERE id = ?
        ", [$combatId]);
        
        $this->addCombatLog($combatId, 'player', 'Připravuješ se na obranu (+5 brnění pro další kolo)');
        
        return [
            'success' => true,
            'message' => 'Obrana aktivována',
            'combatStatus' => 'active'
        ];
    }
    
    private function processUseItem($combatId, $character, $itemId) {
        // Check if player has the item
        $item = $this->database->query("
            SELECT ui.*, i.name, i.usable, i.use_effects
            FROM user_inventory ui
            JOIN items i ON ui.item_id = i.id
            WHERE ui.user_id = ? AND ui.item_id = ? AND ui.quantity > 0
        ", [$character['user_id'], $itemId])->fetch(PDO::FETCH_ASSOC);
        
        if (!$item || !$item['usable']) {
            return ['success' => false, 'message' => 'Předmět nelze použít'];
        }
        
        // Apply item effects
        $effects = json_decode($item['use_effects'], true) ?: [];
        $this->applyItemEffects($character['user_id'], $effects);
        
        // Remove item from inventory
        $this->database->query("
            UPDATE user_inventory 
            SET quantity = quantity - 1 
            WHERE user_id = ? AND item_id = ?
        ", [$character['user_id'], $itemId]);
        
        // Switch turn
        $this->database->query("
            UPDATE active_combats 
            SET current_turn = 'enemy'
            WHERE id = ?
        ", [$combatId]);
        
        $this->addCombatLog($combatId, 'player', "Použito: {$item['name']}");
        
        return [
            'success' => true,
            'message' => "Použit předmět: {$item['name']}",
            'combatStatus' => 'active'
        ];
    }
    
    private function processFlee($combatId, $character, $enemy) {
        // Calculate flee chance based on agility vs enemy speed
        $fleeChance = 50 + ($character['agility'] * 3) - ($enemy['speed'] ?? 10);
        $fleeChance = max(10, min(90, $fleeChance)); // 10-90% range
        
        $fled = rand(1, 100) <= $fleeChance;
        
        if ($fled) {
            $this->addCombatLog($combatId, 'player', 'Úspěšný útěk z boje!');
            return $this->endCombat($combatId, 'fled');
        } else {
            $this->addCombatLog($combatId, 'player', 'Útěk se nezdařil!');
            $this->database->query("
                UPDATE active_combats 
                SET current_turn = 'enemy'
                WHERE id = ?
            ", [$combatId]);
            
            return [
                'success' => true,
                'message' => 'Útěk se nezdařil',
                'combatStatus' => 'active'
            ];
        }
    }
    
    private function processEnemyTurn($combatId) {
        $combat = $this->database->query("
            SELECT * FROM active_combats WHERE id = ?
        ", [$combatId])->fetch(PDO::FETCH_ASSOC);
        
        $character = $this->database->query("
            SELECT * FROM characters WHERE user_id = ?
        ", [$combat['user_id']])->fetch(PDO::FETCH_ASSOC);
        
        $enemy = json_decode($combat['enemy_data'], true);
        
        // Simple AI: 70% attack, 20% defend, 10% special
        $action = rand(1, 100);
        
        if ($action <= 70) {
            return $this->enemyAttack($combatId, $character, $enemy);
        } elseif ($action <= 90) {
            return $this->enemyDefend($combatId, $enemy);
        } else {
            return $this->enemySpecialAction($combatId, $character, $enemy);
        }
    }
    
    private function enemyAttack($combatId, $character, $enemy) {
        $damage = $enemy['damage'] + rand(-2, 2);
        
        // Apply player armor and defense bonus
        $armor = $this->getArmorValue($character['user_id']);
        $defenseBonus = $combat['player_defense_bonus'] ?? 0;
        $totalArmor = $armor + $defenseBonus;
        
        $finalDamage = max(1, $damage - $totalArmor);
        
        // Apply damage to character
        $newHealth = max(0, $character['health'] - $finalDamage);
        $this->database->query("
            UPDATE characters 
            SET health = ? 
            WHERE user_id = ?
        ", [$newHealth, $character['user_id']]);
        
        // Reset defense bonus
        $this->database->query("
            UPDATE active_combats 
            SET player_defense_bonus = 0,
                current_turn = 'player'
            WHERE id = ?
        ", [$combatId]);
        
        $this->addCombatLog($combatId, 'enemy', "{$enemy['name']} útočí za {$finalDamage} poškození!");
        
        // Check if player is defeated
        if ($newHealth <= 0) {
            return $this->endCombat($combatId, 'defeat');
        }
        
        return [
            'action' => 'attack',
            'damage' => $finalDamage,
            'playerHealth' => $newHealth
        ];
    }
    
    private function enemyDefend($combatId, $enemy) {
        // Enemy gains temporary armor
        $enemy['temp_armor'] = ($enemy['temp_armor'] ?? 0) + 3;
        
        $this->updateCombat($combatId, $enemy, 'player');
        $this->addCombatLog($combatId, 'enemy', "{$enemy['name']} se připravuje na obranu");
        
        return [
            'action' => 'defend',
            'message' => 'Nepřítel se brání'
        ];
    }
    
    private function enemySpecialAction($combatId, $character, $enemy) {
        // Special actions based on enemy type
        switch ($enemy['type']) {
            case 'raider':
                return $this->raiderBerserk($combatId, $character, $enemy);
            case 'mutant':
                return $this->mutantRegeneration($combatId, $enemy);
            case 'robot':
                return $this->robotOvercharge($combatId, $character, $enemy);
            default:
                return $this->enemyAttack($combatId, $character, $enemy);
        }
    }
    
    private function endCombat($combatId, $result) {
        $combat = $this->database->query("
            SELECT * FROM active_combats WHERE id = ?
        ", [$combatId])->fetch(PDO::FETCH_ASSOC);
        
        $enemy = json_decode($combat['enemy_data'], true);
        
        // Update combat status
        $this->database->query("
            UPDATE active_combats 
            SET status = 'completed', 
                result = ?,
                ended_at = NOW()
            WHERE id = ?
        ", [$result, $combatId]);
        
        $rewards = [];
        
        if ($result === 'victory') {
            // Grant experience
            $exp = $enemy['level'] * 10 + rand(5, 15);
            $this->database->query("
                UPDATE characters 
                SET experience = experience + ? 
                WHERE user_id = ?
            ", [$exp, $combat['user_id']]);
            
            // Grant loot
            $loot = $this->generateLoot($enemy);
            foreach ($loot as $item) {
                $this->addItemToInventory($combat['user_id'], $item['id'], $item['quantity']);
            }
            
            $rewards = [
                'experience' => $exp,
                'loot' => $loot
            ];
            
            $this->addCombatLog($combatId, 'system', "Vítězství! Získáno {$exp} XP");
        }
        
        return [
            'success' => true,
            'combatStatus' => 'completed',
            'result' => $result,
            'rewards' => $rewards,
            'message' => $this->getResultMessage($result)
        ];
    }
    
    private function generateEnemy($type, $location) {
        $enemies = [
            'raider' => [
                'name' => 'Pustinný Nájezdník',
                'type' => 'raider',
                'level' => rand(1, 5),
                'health' => rand(80, 120),
                'damage' => rand(15, 25),
                'armor' => rand(2, 8),
                'speed' => rand(8, 12),
                'image' => 'raider.svg'
            ],
            'mutant' => [
                'name' => 'Zmutovaný Tvor',
                'type' => 'mutant', 
                'level' => rand(2, 6),
                'health' => rand(100, 150),
                'damage' => rand(20, 30),
                'armor' => rand(5, 12),
                'speed' => rand(6, 10),
                'image' => 'mutant.svg'
            ],
            'robot' => [
                'name' => 'Bojový Robot',
                'type' => 'robot',
                'level' => rand(3, 8),
                'health' => rand(120, 180),
                'damage' => rand(25, 35),
                'armor' => rand(10, 20),
                'speed' => rand(5, 8),
                'image' => 'robot.svg'
            ]
        ];
        
        $enemy = $enemies[$type] ?? $enemies['raider'];
        $enemy['max_health'] = $enemy['health'];
        
        return $enemy;
    }
    
    private function generateLoot($enemy) {
        $loot = [];
        
        // Base caps reward
        $caps = $enemy['level'] * 10 + rand(5, 20);
        $loot[] = ['id' => 1, 'quantity' => $caps]; // Assuming caps have ID 1
        
        // Random equipment based on enemy type
        $lootTables = [
            'raider' => [2, 3, 4], // Weapon IDs
            'mutant' => [5, 6, 7], // Material IDs  
            'robot' => [8, 9, 10]  // Tech IDs
        ];
        
        $possibleLoot = $lootTables[$enemy['type']] ?? $lootTables['raider'];
        
        // 60% chance for additional loot
        if (rand(1, 100) <= 60) {
            $itemId = $possibleLoot[array_rand($possibleLoot)];
            $quantity = rand(1, 3);
            $loot[] = ['id' => $itemId, 'quantity' => $quantity];
        }
        
        return $loot;
    }
    
    // Helper methods
    private function getCombatData($combatId, $userId) {
        return $this->database->query("
            SELECT * FROM active_combats 
            WHERE id = ? AND user_id = ?
        ", [$combatId, $userId])->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getEquippedWeapon($userId) {
        return $this->database->query("
            SELECT i.* FROM user_inventory ui
            JOIN items i ON ui.item_id = i.id
            WHERE ui.user_id = ? AND ui.equipped = 1 AND i.equipment_slot = 'weapon'
        ", [$userId])->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getArmorValue($userId) {
        $armor = $this->database->query("
            SELECT COALESCE(SUM(JSON_UNQUOTE(JSON_EXTRACT(i.stats_bonus, '$.armor'))), 0) as total_armor
            FROM user_inventory ui
            JOIN items i ON ui.item_id = i.id
            WHERE ui.user_id = ? AND ui.equipped = 1
        ", [$userId])->fetch(PDO::FETCH_ASSOC);
        
        return $armor['total_armor'] ?? 0;
    }
    
    private function addCombatLog($combatId, $actor, $message) {
        $this->database->query("
            INSERT INTO combat_logs (combat_id, actor, message, created_at)
            VALUES (?, ?, ?, NOW())
        ", [$combatId, $actor, $message]);
    }
}
?>