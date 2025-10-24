<?php
namespace App\Models;

use App\Models\BaseModel;

class Quest extends BaseModel
{
    protected $table = 'quests';
    
    public function getAvailableQuests($userId, $locationId = null)
    {
        $sql = "
            SELECT 
                q.*,
                qt.name as type_name,
                qt.description as type_description,
                npc.name as npc_name,
                npc.description as npc_description,
                CASE 
                    WHEN qi.id IS NOT NULL THEN qi.status
                    ELSE 'available'
                END as user_status
            FROM quests q
            JOIN quest_types qt ON q.quest_type_id = qt.id
            LEFT JOIN npcs npc ON q.npc_id = npc.id
            LEFT JOIN quest_instances qi ON q.id = qi.quest_id AND qi.user_id = ?
            WHERE q.is_active = 1 
            AND (q.required_level <= (SELECT level FROM user_profiles WHERE user_id = ?))
            AND (qi.id IS NULL OR qi.status IN ('available', 'in_progress'))
        ";
        
        $params = [$userId, $userId];
        
        if ($locationId) {
            $sql .= " AND q.location_id = ?";
            $params[] = $locationId;
        }
        
        $sql .= " ORDER BY q.required_level ASC, q.created_at DESC";
        
        return $this->db->query($sql, $params)->fetchAll();
    }
    
    public function getQuestById($questId)
    {
        return $this->db->query("
            SELECT 
                q.*,
                qt.name as type_name,
                qt.description as type_description,
                npc.name as npc_name,
                npc.description as npc_description,
                wl.name as location_name
            FROM quests q
            JOIN quest_types qt ON q.quest_type_id = qt.id
            LEFT JOIN npcs npc ON q.npc_id = npc.id
            LEFT JOIN world_locations wl ON q.location_id = wl.id
            WHERE q.id = ?
        ", [$questId])->fetch();
    }
    
    public function getUserQuestInstance($userId, $questId)
    {
        return $this->db->query("
            SELECT qi.*, q.title, q.description, q.objectives
            FROM quest_instances qi
            JOIN quests q ON qi.quest_id = q.id
            WHERE qi.user_id = ? AND qi.quest_id = ?
        ", [$userId, $questId])->fetch();
    }
    
    public function getUserActiveQuests($userId)
    {
        return $this->db->query("
            SELECT 
                qi.*,
                q.title,
                q.description,
                q.objectives,
                q.rewards,
                qt.name as type_name,
                wl.name as location_name
            FROM quest_instances qi
            JOIN quests q ON qi.quest_id = q.id
            JOIN quest_types qt ON q.quest_type_id = qt.id
            LEFT JOIN world_locations wl ON q.location_id = wl.id
            WHERE qi.user_id = ? AND qi.status = 'in_progress'
            ORDER BY qi.started_at DESC
        ", [$userId])->fetchAll();
    }
    
    public function acceptQuest($userId, $questId)
    {
        // Check if quest exists and is available
        $quest = $this->getQuestById($questId);
        if (!$quest) {
            return false;
        }
        
        // Check if user already has this quest
        $existing = $this->getUserQuestInstance($userId, $questId);
        if ($existing && $existing['status'] !== 'failed') {
            return false;
        }
        
        // Check user level requirement
        $userProfile = $this->db->query("
            SELECT level FROM user_profiles WHERE user_id = ?
        ", [$userId])->fetch();
        
        if ($userProfile['level'] < $quest['required_level']) {
            return false;
        }
        
        // Create quest instance
        return $this->db->query("
            INSERT INTO quest_instances (user_id, quest_id, status, progress, started_at)
            VALUES (?, ?, 'in_progress', '{}', NOW())
        ", [$userId, $questId]);
    }
    
    public function updateQuestProgress($userId, $questId, $progress)
    {
        return $this->db->query("
            UPDATE quest_instances 
            SET progress = ?, updated_at = NOW()
            WHERE user_id = ? AND quest_id = ? AND status = 'in_progress'
        ", [json_encode($progress), $userId, $questId]);
    }
    
    public function completeQuest($userId, $questId)
    {
        $quest = $this->getQuestById($questId);
        $instance = $this->getUserQuestInstance($userId, $questId);
        
        if (!$quest || !$instance || $instance['status'] !== 'in_progress') {
            return false;
        }
        
        // Update quest status
        $this->db->query("
            UPDATE quest_instances 
            SET status = 'completed', completed_at = NOW()
            WHERE user_id = ? AND quest_id = ?
        ", [$userId, $questId]);
        
        // Give rewards
        $rewards = json_decode($quest['rewards'], true);
        $this->giveQuestRewards($userId, $rewards);
        
        // Log completion
        $this->db->query("
            INSERT INTO quest_completion_log (user_id, quest_id, completed_at, rewards_given)
            VALUES (?, ?, NOW(), ?)
        ", [$userId, $questId, $quest['rewards']]);
        
        return true;
    }
    
    public function abandonQuest($userId, $questId)
    {
        return $this->db->query("
            UPDATE quest_instances 
            SET status = 'abandoned', updated_at = NOW()
            WHERE user_id = ? AND quest_id = ? AND status = 'in_progress'
        ", [$userId, $questId]);
    }
    
    private function giveQuestRewards($userId, $rewards)
    {
        if (isset($rewards['experience'])) {
            $this->db->query("
                UPDATE user_profiles 
                SET experience = experience + ?
                WHERE user_id = ?
            ", [$rewards['experience'], $userId]);
        }
        
        if (isset($rewards['items'])) {
            foreach ($rewards['items'] as $itemId => $quantity) {
                $this->db->query("
                    INSERT INTO user_inventory (user_id, item_id, quantity, acquired_at)
                    VALUES (?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE quantity = quantity + ?
                ", [$userId, $itemId, $quantity, $quantity]);
            }
        }
        
        if (isset($rewards['resources'])) {
            foreach ($rewards['resources'] as $resourceType => $amount) {
                $this->db->query("
                    INSERT INTO user_resources (user_id, resource_type, amount, updated_at)
                    VALUES (?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE amount = amount + ?
                ", [$userId, $resourceType, $amount, $amount]);
            }
        }
    }
    
    public function getQuestObjectives($questId)
    {
        $quest = $this->getQuestById($questId);
        if (!$quest) return [];
        
        return json_decode($quest['objectives'], true) ?: [];
    }
    
    public function checkQuestCompletion($userId, $questId)
    {
        $instance = $this->getUserQuestInstance($userId, $questId);
        if (!$instance || $instance['status'] !== 'in_progress') {
            return false;
        }
        
        $objectives = $this->getQuestObjectives($questId);
        $progress = json_decode($instance['progress'], true) ?: [];
        
        foreach ($objectives as $objective) {
            $objectiveId = $objective['id'];
            $required = $objective['required'];
            $current = $progress[$objectiveId] ?? 0;
            
            if ($current < $required) {
                return false;
            }
        }
        
        return true;
    }
}