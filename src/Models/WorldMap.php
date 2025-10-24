<?php
namespace App\Models;

use App\Models\BaseModel;

class WorldMap extends BaseModel
{
    protected $table = 'world_locations';
    
    public function getAllLocations()
    {
        return $this->db->query("
            SELECT 
                wl.*,
                (SELECT COUNT(*) FROM users u 
                 JOIN user_profiles up ON u.id = up.user_id 
                 WHERE up.last_location_id = wl.id) as player_count
            FROM world_locations wl 
            WHERE wl.is_active = 1 
            ORDER BY wl.danger_level ASC, wl.name ASC
        ")->fetchAll();
    }
    
    public function getLocationById($id)
    {
        return $this->db->query("
            SELECT wl.*, 
                   COUNT(DISTINCT ur.id) as resource_count,
                   COUNT(DISTINCT uq.id) as available_quests
            FROM world_locations wl
            LEFT JOIN user_resources ur ON wl.id = ur.location_id
            LEFT JOIN user_quests uq ON wl.id = uq.location_id AND uq.status = 'available'
            WHERE wl.id = ? AND wl.is_active = 1
            GROUP BY wl.id
        ", [$id])->fetch();
    }
    
    public function getLocationsByDangerLevel($minLevel, $maxLevel)
    {
        return $this->db->query("
            SELECT * FROM world_locations 
            WHERE danger_level BETWEEN ? AND ? 
            AND is_active = 1 
            ORDER BY name ASC
        ", [$minLevel, $maxLevel])->fetchAll();
    }
    
    public function getResourcesAtLocation($locationId)
    {
        return $this->db->query("
            SELECT 
                r.name,
                r.description,
                r.rarity,
                lr.spawn_rate,
                lr.max_amount,
                lr.current_amount
            FROM location_resources lr
            JOIN resources r ON lr.resource_id = r.id
            WHERE lr.location_id = ? AND lr.is_active = 1
            ORDER BY r.rarity DESC, r.name ASC
        ", [$locationId])->fetchAll();
    }
    
    public function getConnectedLocations($locationId)
    {
        return $this->db->query("
            SELECT 
                wl.id,
                wl.name,
                wl.description,
                wl.danger_level,
                lc.travel_time,
                lc.travel_cost,
                lc.is_available
            FROM location_connections lc
            JOIN world_locations wl ON lc.destination_id = wl.id
            WHERE lc.origin_id = ? AND lc.is_available = 1 AND wl.is_active = 1
            ORDER BY lc.travel_time ASC
        ", [$locationId])->fetchAll();
    }
    
    public function canTravelToLocation($userId, $destinationId)
    {
        $user = $this->db->query("
            SELECT up.last_location_id, up.current_energy, up.level
            FROM user_profiles up
            WHERE up.user_id = ?
        ", [$userId])->fetch();
        
        if (!$user) return false;
        
        $connection = $this->db->query("
            SELECT lc.*, wl.required_level
            FROM location_connections lc
            JOIN world_locations wl ON lc.destination_id = wl.id
            WHERE lc.origin_id = ? AND lc.destination_id = ? AND lc.is_available = 1
        ", [$user['last_location_id'], $destinationId])->fetch();
        
        if (!$connection) return false;
        
        return $user['current_energy'] >= $connection['travel_cost'] && 
               $user['level'] >= $connection['required_level'];
    }
    
    public function travelToLocation($userId, $destinationId)
    {
        if (!$this->canTravelToLocation($userId, $destinationId)) {
            return false;
        }
        
        $user = $this->db->query("
            SELECT up.last_location_id, up.current_energy
            FROM user_profiles up
            WHERE up.user_id = ?
        ", [$userId])->fetch();
        
        $connection = $this->db->query("
            SELECT travel_cost FROM location_connections
            WHERE origin_id = ? AND destination_id = ?
        ", [$user['last_location_id'], $destinationId])->fetch();
        
        // Update user location and energy
        $newEnergy = $user['current_energy'] - $connection['travel_cost'];
        
        $this->db->query("
            UPDATE user_profiles 
            SET last_location_id = ?, current_energy = ?
            WHERE user_id = ?
        ", [$destinationId, $newEnergy, $userId]);
        
        // Log travel
        $this->db->query("
            INSERT INTO user_travel_log (user_id, from_location_id, to_location_id, energy_cost, traveled_at)
            VALUES (?, ?, ?, ?, NOW())
        ", [$userId, $user['last_location_id'], $destinationId, $connection['travel_cost']]);
        
        return true;
    }
}