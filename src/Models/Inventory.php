<?php
namespace App\Models;

use App\Models\BaseModel;

class Inventory extends BaseModel
{
    protected $table = 'user_inventory';
    
    public function getUserInventory($userId, $category = null)
    {
        $sql = "
            SELECT 
                ui.*,
                i.name,
                i.description,
                i.category,
                i.rarity,
                i.value,
                i.weight,
                i.stackable,
                i.usable,
                i.equipment_slot,
                i.stats_bonus,
                ic.name as category_name,
                ic.icon as category_icon
            FROM user_inventory ui
            JOIN items i ON ui.item_id = i.id
            JOIN item_categories ic ON i.category = ic.id
            WHERE ui.user_id = ? AND ui.quantity > 0
        ";
        
        $params = [$userId];
        
        if ($category) {
            $sql .= " AND i.category = ?";
            $params[] = $category;
        }
        
        $sql .= " ORDER BY ic.sort_order ASC, i.rarity DESC, i.name ASC";
        
        return $this->db->query($sql, $params)->fetchAll();
    }
    
    public function getInventoryCapacity($userId)
    {
        $result = $this->db->query("
            SELECT 
                up.inventory_slots,
                SUM(ui.quantity * i.weight) as total_weight,
                COUNT(DISTINCT ui.item_id) as used_slots
            FROM user_profiles up
            LEFT JOIN user_inventory ui ON up.user_id = ui.user_id AND ui.quantity > 0
            LEFT JOIN items i ON ui.item_id = i.id
            WHERE up.user_id = ?
            GROUP BY up.user_id, up.inventory_slots
        ", [$userId])->fetch();
        
        return [
            'max_slots' => $result['inventory_slots'] ?? 50,
            'used_slots' => $result['used_slots'] ?? 0,
            'total_weight' => $result['total_weight'] ?? 0,
            'max_weight' => 1000 // Could be dynamic based on character stats
        ];
    }
    
    public function addItem($userId, $itemId, $quantity = 1)
    {
        $item = $this->getItemById($itemId);
        if (!$item) return false;
        
        // Check inventory capacity
        $capacity = $this->getInventoryCapacity($userId);
        $newWeight = $capacity['total_weight'] + ($item['weight'] * $quantity);
        
        if ($newWeight > $capacity['max_weight']) {
            return ['success' => false, 'message' => 'Inventory weight limit exceeded'];
        }
        
        if ($item['stackable']) {
            // Add to existing stack or create new
            $existing = $this->db->query("
                SELECT * FROM user_inventory 
                WHERE user_id = ? AND item_id = ?
            ", [$userId, $itemId])->fetch();
            
            if ($existing) {
                $this->db->query("
                    UPDATE user_inventory 
                    SET quantity = quantity + ?, updated_at = NOW()
                    WHERE user_id = ? AND item_id = ?
                ", [$quantity, $userId, $itemId]);
            } else {
                // Check slot limit
                if ($capacity['used_slots'] >= $capacity['max_slots']) {
                    return ['success' => false, 'message' => 'Inventory slot limit exceeded'];
                }
                
                $this->db->query("
                    INSERT INTO user_inventory (user_id, item_id, quantity, acquired_at)
                    VALUES (?, ?, ?, NOW())
                ", [$userId, $itemId, $quantity]);
            }
        } else {
            // Non-stackable items need individual slots
            if (($capacity['used_slots'] + $quantity) > $capacity['max_slots']) {
                return ['success' => false, 'message' => 'Inventory slot limit exceeded'];
            }
            
            for ($i = 0; $i < $quantity; $i++) {
                $this->db->query("
                    INSERT INTO user_inventory (user_id, item_id, quantity, acquired_at)
                    VALUES (?, ?, 1, NOW())
                ", [$userId, $itemId]);
            }
        }
        
        return ['success' => true, 'message' => "Added {$quantity}x {$item['name']} to inventory"];
    }
    
    public function removeItem($userId, $itemId, $quantity = 1)
    {
        $existing = $this->db->query("
            SELECT * FROM user_inventory 
            WHERE user_id = ? AND item_id = ? AND quantity >= ?
        ", [$userId, $itemId, $quantity])->fetch();
        
        if (!$existing) {
            return false;
        }
        
        if ($existing['quantity'] > $quantity) {
            $this->db->query("
                UPDATE user_inventory 
                SET quantity = quantity - ?, updated_at = NOW()
                WHERE user_id = ? AND item_id = ?
            ", [$quantity, $userId, $itemId]);
        } else {
            $this->db->query("
                DELETE FROM user_inventory 
                WHERE user_id = ? AND item_id = ?
            ", [$userId, $itemId]);
        }
        
        return true;
    }
    
    public function useItem($userId, $itemId, $quantity = 1)
    {
        $item = $this->getItemById($itemId);
        if (!$item || !$item['usable']) {
            return ['success' => false, 'message' => 'Item is not usable'];
        }
        
        if (!$this->removeItem($userId, $itemId, $quantity)) {
            return ['success' => false, 'message' => 'Item not found in inventory'];
        }
        
        // Apply item effects
        $effects = json_decode($item['use_effects'], true) ?: [];
        $this->applyItemEffects($userId, $effects, $quantity);
        
        return [
            'success' => true, 
            'message' => "Used {$quantity}x {$item['name']}",
            'effects' => $effects
        ];
    }
    
    public function equipItem($userId, $itemId)
    {
        $item = $this->getItemById($itemId);
        if (!$item || !$item['equipment_slot']) {
            return ['success' => false, 'message' => 'Item is not equipable'];
        }
        
        // Check if user has the item
        $hasItem = $this->db->query("
            SELECT * FROM user_inventory 
            WHERE user_id = ? AND item_id = ? AND quantity > 0
        ", [$userId, $itemId])->fetch();
        
        if (!$hasItem) {
            return ['success' => false, 'message' => 'Item not found in inventory'];
        }
        
        // Unequip current item in slot if any
        $currentEquipped = $this->db->query("
            SELECT * FROM user_equipment 
            WHERE user_id = ? AND slot = ?
        ", [$userId, $item['equipment_slot']])->fetch();
        
        if ($currentEquipped) {
            $this->unequipItem($userId, $currentEquipped['item_id']);
        }
        
        // Equip new item
        $this->db->query("
            INSERT INTO user_equipment (user_id, item_id, slot, equipped_at)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE item_id = ?, equipped_at = NOW()
        ", [$userId, $itemId, $item['equipment_slot'], $itemId]);
        
        // Apply stat bonuses
        $this->updateEquipmentStats($userId);
        
        return ['success' => true, 'message' => "Equipped {$item['name']}"];
    }
    
    public function unequipItem($userId, $itemId)
    {
        $this->db->query("
            DELETE FROM user_equipment 
            WHERE user_id = ? AND item_id = ?
        ", [$userId, $itemId]);
        
        $this->updateEquipmentStats($userId);
        return true;
    }
    
    private function updateEquipmentStats($userId)
    {
        $equipment = $this->db->query("
            SELECT i.stats_bonus 
            FROM user_equipment ue
            JOIN items i ON ue.item_id = i.id
            WHERE ue.user_id = ?
        ", [$userId])->fetchAll();
        
        $totalStats = [
            'strength' => 0,
            'agility' => 0,
            'intelligence' => 0,
            'endurance' => 0,
            'luck' => 0
        ];
        
        foreach ($equipment as $item) {
            $stats = json_decode($item['stats_bonus'], true) ?: [];
            foreach ($stats as $stat => $value) {
                if (isset($totalStats[$stat])) {
                    $totalStats[$stat] += $value;
                }
            }
        }
        
        // Update user profile with equipment bonuses
        $this->db->query("
            UPDATE user_profiles 
            SET equipment_bonus_strength = ?,
                equipment_bonus_agility = ?,
                equipment_bonus_intelligence = ?,
                equipment_bonus_endurance = ?,
                equipment_bonus_luck = ?
            WHERE user_id = ?
        ", [
            $totalStats['strength'],
            $totalStats['agility'], 
            $totalStats['intelligence'],
            $totalStats['endurance'],
            $totalStats['luck'],
            $userId
        ]);
    }
    
    private function applyItemEffects($userId, $effects, $quantity)
    {
        foreach ($effects as $effect => $value) {
            $totalValue = $value * $quantity;
            
            switch ($effect) {
                case 'heal_health':
                    $this->db->query("
                        UPDATE user_profiles 
                        SET current_health = LEAST(current_health + ?, max_health)
                        WHERE user_id = ?
                    ", [$totalValue, $userId]);
                    break;
                    
                case 'restore_energy':
                    $this->db->query("
                        UPDATE user_profiles 
                        SET current_energy = LEAST(current_energy + ?, max_energy)
                        WHERE user_id = ?
                    ", [$totalValue, $userId]);
                    break;
                    
                case 'boost_strength':
                case 'boost_agility':
                case 'boost_intelligence':
                case 'boost_endurance':
                case 'boost_luck':
                    // Temporary boost - would need a separate system for timed effects
                    $this->addTemporaryEffect($userId, $effect, $totalValue, 3600); // 1 hour
                    break;
            }
        }
    }
    
    private function addTemporaryEffect($userId, $effect, $value, $duration)
    {
        $this->db->query("
            INSERT INTO user_temporary_effects (user_id, effect_type, effect_value, expires_at)
            VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND))
        ", [$userId, $effect, $value, $duration]);
    }
    
    private function getItemById($itemId)
    {
        return $this->db->query("
            SELECT * FROM items WHERE id = ?
        ", [$itemId])->fetch();
    }
}