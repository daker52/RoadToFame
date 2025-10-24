<?php

namespace WastelandDominion\Controllers;

use WastelandDominion\Models\User;
use WastelandDominion\Models\WorldMap;
use WastelandDominion\Models\Quest;

class GameController extends BaseController
{
    private $userModel;
    private $worldMapModel;
    private $questModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->worldMapModel = new WorldMap();
        $this->questModel = new Quest();
        
        // Require authentication for all game actions
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
            return;
        }
    }
    
    /**
     * Main game dashboard
     */
    public function dashboard()
    {
        $userId = $this->getCurrentUserId();
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }
        
        // Get player stats
        $playerStats = $this->userModel->getPlayerStats($userId);
        
        // Get current location
        $currentLocation = $this->worldMapModel->find($playerStats['current_location_id'] ?? 1);
        
        // Get active quests
        $activeQuests = $this->questModel->getActiveQuests($userId);
        
        // Get recent activities
        $recentActivities = $this->userModel->getRecentActivities($userId, 10);
        
        $this->render('game/dashboard', [
            'user' => $user,
            'playerStats' => $playerStats,
            'currentLocation' => $currentLocation,
            'activeQuests' => $activeQuests,
            'recentActivities' => $recentActivities,
            'pageTitle' => 'Game Dashboard'
        ]);
    }
    
    /**
     * Character setup for new players
     */
    public function characterSetup()
    {
        $userId = $this->getCurrentUserId();
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }
        
        // Check if character is already set up
        $playerStats = $this->userModel->getPlayerStats($userId);
        if ($playerStats && $playerStats['character_created']) {
            $this->redirect('/game/dashboard');
            return;
        }
        
        $this->render('game/character-setup', [
            'user' => $user,
            'pageTitle' => 'Character Setup'
        ]);
    }
    
    /**
     * Save character setup
     */
    public function saveCharacterSetup()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Invalid request method', 405);
            return;
        }
        
        $userId = $this->getCurrentUserId();
        
        // Validate input
        $characterName = trim($_POST['character_name'] ?? '');
        $strength = (int)($_POST['strength'] ?? 5);
        $agility = (int)($_POST['agility'] ?? 5);
        $intelligence = (int)($_POST['intelligence'] ?? 5);
        $endurance = (int)($_POST['endurance'] ?? 5);
        $luck = (int)($_POST['luck'] ?? 5);
        
        // Validate character name
        if (empty($characterName) || strlen($characterName) < 3) {
            $this->error('Character name must be at least 3 characters long');
            return;
        }
        
        // Validate attribute distribution (total should be 25-35)
        $totalAttributes = $strength + $agility + $intelligence + $endurance + $luck;
        if ($totalAttributes < 25 || $totalAttributes > 35) {
            $this->error('Invalid attribute distribution');
            return;
        }
        
        try {
            // Create character profile
            $characterData = [
                'user_id' => $userId,
                'character_name' => $characterName,
                'level' => 1,
                'experience' => 0,
                'health' => 100,
                'energy' => 100,
                'caps' => $this->config['game']['default_starting_caps'],
                'diamonds' => $this->config['game']['default_starting_diamonds'],
                'strength' => $strength,
                'agility' => $agility,
                'intelligence' => $intelligence,
                'endurance' => $endurance,
                'luck' => $luck,
                'current_location_id' => 1, // Start in Vault City
                'character_created' => 1
            ];
            
            $this->userModel->createPlayerProfile($characterData);
            
            // Give starting items
            $this->giveStartingItems($userId);
            
            $this->success('Character created successfully', [
                'redirect' => '/game/dashboard'
            ]);
            
        } catch (Exception $e) {
            $this->error('Failed to create character: ' . $e->getMessage());
        }
    }
    
    /**
     * Display city information
     */
    public function city($cityId)
    {
        $userId = $this->getCurrentUserId();
        $cityId = (int)$cityId;
        
        $city = $this->worldMapModel->find($cityId);
        
        if (!$city) {
            $this->error('City not found', 404);
            return;
        }
        
        // Get available quests in this city
        $availableQuests = $this->questModel->getAvailableQuests($userId, $cityId);
        
        // Get city NPCs and merchants
        $npcs = $this->worldMapModel->getCityNPCs($cityId);
        
        // Get player's current stats
        $playerStats = $this->userModel->getPlayerStats($userId);
        
        $this->render('game/city', [
            'city' => $city,
            'availableQuests' => $availableQuests,
            'npcs' => $npcs,
            'playerStats' => $playerStats,
            'pageTitle' => $city['name']
        ]);
    }
    
    /**
     * Display location information
     */
    public function location($locationId)
    {
        $userId = $this->getCurrentUserId();
        $locationId = (int)$locationId;
        
        $location = $this->worldMapModel->find($locationId);
        
        if (!$location) {
            $this->error('Location not found', 404);
            return;
        }
        
        // Check if player can access this location
        $playerStats = $this->userModel->getPlayerStats($userId);
        if (!$this->canAccessLocation($playerStats, $location)) {
            $this->error('You cannot access this location yet');
            return;
        }
        
        // Get exploration options
        $explorationOptions = $this->worldMapModel->getExplorationOptions($locationId);
        
        // Get location enemies
        $enemies = $this->worldMapModel->getLocationEnemies($locationId);
        
        $this->render('game/location', [
            'location' => $location,
            'explorationOptions' => $explorationOptions,
            'enemies' => $enemies,
            'playerStats' => $playerStats,
            'pageTitle' => $location['name']
        ]);
    }
    
    /**
     * Get current authenticated user ID
     */
    private function getCurrentUserId(): int
    {
        // This should be implemented based on your auth system
        // For now, assuming session-based auth
        return $_SESSION['user_id'] ?? 0;
    }
    
    /**
     * Check if user is authenticated
     */
    private function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
    }
    
    /**
     * Give starting items to new character
     */
    private function giveStartingItems($userId): void
    {
        $startingItems = [
            ['item_id' => 1, 'quantity' => 1], // Rusty Knife
            ['item_id' => 7, 'quantity' => 1], // Leather Jacket  
            ['item_id' => 9, 'quantity' => 3], // Stimpak
            ['item_id' => 11, 'quantity' => 2], // Nuka Cola
            ['item_id' => 12, 'quantity' => 5]  // Scrap Metal
        ];
        
        foreach ($startingItems as $item) {
            $this->userModel->addItemToInventory(
                $userId, 
                $item['item_id'], 
                $item['quantity']
            );
        }
    }
    
    /**
     * Check if player can access a location
     */
    private function canAccessLocation($playerStats, $location): bool
    {
        // Basic level requirement check
        if ($playerStats['level'] < $location['level_requirement']) {
            return false;
        }
        
        // Check if player has enough energy for travel
        if ($playerStats['energy'] < $location['travel_cost']) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Render template with game-specific layout
     */
    private function render($template, $data = [])
    {
        // Add common game data
        $data['gameConfig'] = [
            'maxEnergy' => $this->config['game']['max_energy'],
            'energyRegenTime' => $this->config['game']['energy_regen_time']
        ];
        
        // Use base controller render method
        parent::render($template, $data);
    }
}