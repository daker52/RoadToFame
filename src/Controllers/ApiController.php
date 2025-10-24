<?php

namespace WastelandDominion\Controllers;

class ApiController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        
        // Set JSON response headers
        header('Content-Type: application/json');
        
        // CORS headers for API
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
    
    /**
     * Get player statistics
     */
    public function playerStats()
    {
        if (!$this->isAuthenticated()) {
            $this->error('Authentication required', 401);
            return;
        }
        
        $userId = $this->getCurrentUserId();
        
        try {
            // Get basic player stats
            $stats = [
                'id' => $userId,
                'level' => 5,
                'experience' => 1250,
                'experience_to_next' => 750,
                'health' => 85,
                'max_health' => 100,
                'energy' => 67,
                'max_energy' => 100,
                'caps' => 423,
                'diamonds' => 12,
                'attributes' => [
                    'strength' => 7,
                    'agility' => 6,
                    'intelligence' => 8,
                    'endurance' => 5,
                    'luck' => 6
                ],
                'current_location' => [
                    'id' => 1,
                    'name' => 'Vault City',
                    'x' => 50,
                    'y' => 50
                ],
                'last_active' => date('c')
            ];
            
            $this->success('Player stats retrieved', $stats);
            
        } catch (\Exception $e) {
            $this->error('Failed to retrieve player stats: ' . $e->getMessage());
        }
    }
    
    /**
     * Get player inventory
     */
    public function playerInventory()
    {
        if (!$this->isAuthenticated()) {
            $this->error('Authentication required', 401);
            return;
        }
        
        $userId = $this->getCurrentUserId();
        
        try {
            $inventory = [
                'slots_used' => 15,
                'slots_total' => 25,
                'weight_used' => 45.5,
                'weight_limit' => 100.0,
                'items' => [
                    [
                        'id' => 1,
                        'name' => 'Rusty Knife',
                        'type' => 'weapon',
                        'quantity' => 1,
                        'weight' => 2.5,
                        'value' => 15,
                        'equipped' => true,
                        'stats' => ['damage' => 8]
                    ],
                    [
                        'id' => 7,
                        'name' => 'Stimpak',
                        'type' => 'consumable',
                        'quantity' => 3,
                        'weight' => 0.5,
                        'value' => 25,
                        'equipped' => false,
                        'stats' => ['heal' => 50]
                    ],
                    [
                        'id' => 12,
                        'name' => 'Scrap Metal',
                        'type' => 'resource',
                        'quantity' => 8,
                        'weight' => 1.0,
                        'value' => 5,
                        'equipped' => false,
                        'stats' => []
                    ]
                ]
            ];
            
            $this->success('Inventory retrieved', $inventory);
            
        } catch (\Exception $e) {
            $this->error('Failed to retrieve inventory: ' . $e->getMessage());
        }
    }
    
    /**
     * Change player location
     */
    public function changeLocation()
    {
        if (!$this->isAuthenticated()) {
            $this->error('Authentication required', 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Invalid request method', 405);
            return;
        }
        
        $userId = $this->getCurrentUserId();
        $input = json_decode(file_get_contents('php://input'), true);
        $locationId = (int)($input['location_id'] ?? 0);
        
        if ($locationId <= 0) {
            $this->error('Invalid location ID');
            return;
        }
        
        try {
            // Mock location data
            $locations = [
                1 => ['name' => 'Vault City', 'travel_cost' => 0],
                2 => ['name' => 'Ruined Mall', 'travel_cost' => 10],
                3 => ['name' => 'Industrial Zone', 'travel_cost' => 15],
                4 => ['name' => 'Desert Outpost', 'travel_cost' => 12]
            ];
            
            if (!isset($locations[$locationId])) {
                $this->error('Location not found', 404);
                return;
            }
            
            $location = $locations[$locationId];
            
            // Check energy requirements
            $currentEnergy = 67; // Mock current energy
            if ($currentEnergy < $location['travel_cost']) {
                $this->error('Not enough energy to travel');
                return;
            }
            
            // Simulate travel
            $newEnergy = $currentEnergy - $location['travel_cost'];
            
            $this->success('Location changed successfully', [
                'new_location' => [
                    'id' => $locationId,
                    'name' => $location['name']
                ],
                'energy_used' => $location['travel_cost'],
                'current_energy' => $newEnergy
            ]);
            
        } catch (\Exception $e) {
            $this->error('Failed to change location: ' . $e->getMessage());
        }
    }
    
    /**
     * Get all cities/locations
     */
    public function getCities()
    {
        try {
            $cities = [
                [
                    'id' => 1,
                    'name' => 'Vault City',
                    'description' => 'Safe haven for survivors',
                    'x' => 50,
                    'y' => 50,
                    'danger_level' => 1,
                    'is_safe_zone' => true
                ],
                [
                    'id' => 2,
                    'name' => 'Ruined Mall',
                    'description' => 'Abandoned shopping center',
                    'x' => 30,
                    'y' => 70,
                    'danger_level' => 2,
                    'is_safe_zone' => false
                ],
                [
                    'id' => 3,
                    'name' => 'Industrial Zone',
                    'description' => 'Former manufacturing district',
                    'x' => 80,
                    'y' => 30,
                    'danger_level' => 3,
                    'is_safe_zone' => false
                ]
            ];
            
            $this->success('Cities retrieved', $cities);
            
        } catch (\Exception $e) {
            $this->error('Failed to retrieve cities: ' . $e->getMessage());
        }
    }
    
    /**
     * Get locations within a city
     */
    public function getLocations($cityId)
    {
        $cityId = (int)$cityId;
        
        try {
            // Mock location data
            $locations = [
                1 => [ // Vault City
                    [
                        'id' => 1,
                        'name' => 'Central Plaza',
                        'description' => 'Main gathering area'
                    ],
                    [
                        'id' => 2,
                        'name' => 'Trading Post',
                        'description' => 'Buy and sell items'
                    ]
                ],
                2 => [ // Ruined Mall
                    [
                        'id' => 3,
                        'name' => 'Food Court',
                        'description' => 'Scavenge for supplies'
                    ],
                    [
                        'id' => 4,
                        'name' => 'Electronics Store',
                        'description' => 'Find valuable tech'
                    ]
                ]
            ];
            
            $cityLocations = $locations[$cityId] ?? [];
            
            $this->success('Locations retrieved', $cityLocations);
            
        } catch (\Exception $e) {
            $this->error('Failed to retrieve locations: ' . $e->getMessage());
        }
    }
    
    /**
     * Get available quests for a city
     */
    public function getAvailableQuests($cityId)
    {
        $cityId = (int)$cityId;
        
        try {
            $quests = [
                [
                    'id' => 1,
                    'title' => 'Welcome to the Wasteland',
                    'description' => 'Learn the basics of survival',
                    'type' => 'tutorial',
                    'level_requirement' => 1,
                    'experience_reward' => 100,
                    'caps_reward' => 50
                ],
                [
                    'id' => 2,
                    'title' => 'Scrap Collection',
                    'description' => 'Gather materials for crafting',
                    'type' => 'collection',
                    'level_requirement' => 2,
                    'experience_reward' => 150,
                    'caps_reward' => 75
                ]
            ];
            
            $this->success('Available quests retrieved', $quests);
            
        } catch (\Exception $e) {
            $this->error('Failed to retrieve quests: ' . $e->getMessage());
        }
    }
    
    /**
     * Start a quest
     */
    public function startQuest($questId)
    {
        if (!$this->isAuthenticated()) {
            $this->error('Authentication required', 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Invalid request method', 405);
            return;
        }
        
        $questId = (int)$questId;
        $userId = $this->getCurrentUserId();
        
        try {
            // Mock quest start logic
            $this->success('Quest started successfully', [
                'quest_id' => $questId,
                'status' => 'active',
                'objectives' => [
                    'Find 5 scrap metal pieces',
                    'Return to quest giver'
                ]
            ]);
            
        } catch (\Exception $e) {
            $this->error('Failed to start quest: ' . $e->getMessage());
        }
    }
    
    /**
     * Get current user ID from session/token
     */
    private function getCurrentUserId(): int
    {
        return $_SESSION['user_id'] ?? 1; // Mock user ID
    }
    
    /**
     * Check if request is authenticated
     */
    protected function isAuthenticated(): bool
    {
        // Mock authentication - in real implementation, check JWT token or session
        return isset($_SESSION['user_id']) || 
               (isset($_SERVER['HTTP_AUTHORIZATION']) && 
                strpos($_SERVER['HTTP_AUTHORIZATION'], 'Bearer ') === 0);
    }
}