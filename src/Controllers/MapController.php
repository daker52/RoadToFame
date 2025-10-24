<?php
namespace App\Controllers;

use App\Auth;
use App\Models\WorldMap;
use App\Models\User;

class MapController extends BaseController
{
    private $worldMap;
    private $auth;
    private $userModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->worldMap = new WorldMap();
        $this->auth = new Auth();
        $this->userModel = new User();
        
        // Require authentication
        if (!$this->auth->isLoggedIn()) {
            $this->redirect('/');
        }
    }
    
    public function index()
    {
        $user = $this->auth->getCurrentUser();
        $locations = $this->worldMap->getAllLocations();
        $userProfile = $this->userModel->getProfile($user['id']);
        
        $this->render('game/map', [
            'locations' => $locations,
            'currentLocation' => $userProfile['last_location_id'] ?? 1,
            'userProfile' => $userProfile
        ]);
    }
    
    public function location($id)
    {
        $user = $this->auth->getCurrentUser();
        $location = $this->worldMap->getLocationById($id);
        
        if (!$location) {
            $this->jsonResponse(['success' => false, 'message' => 'Location not found'], 404);
            return;
        }
        
        $resources = $this->worldMap->getResourcesAtLocation($id);
        $connections = $this->worldMap->getConnectedLocations($id);
        $userProfile = $this->userModel->getProfile($user['id']);
        
        $this->jsonResponse([
            'success' => true,
            'location' => $location,
            'resources' => $resources,
            'connections' => $connections,
            'canTravel' => true // Will be calculated based on energy and level
        ]);
    }
    
    public function travel()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $user = $this->auth->getCurrentUser();
        $destinationId = (int)($_POST['destination_id'] ?? 0);
        
        if (!$destinationId) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid destination']);
            return;
        }
        
        if (!$this->worldMap->canTravelToLocation($user['id'], $destinationId)) {
            $this->jsonResponse(['success' => false, 'message' => 'Cannot travel to this location']);
            return;
        }
        
        if ($this->worldMap->travelToLocation($user['id'], $destinationId)) {
            $newLocation = $this->worldMap->getLocationById($destinationId);
            $this->jsonResponse([
                'success' => true,
                'message' => 'Travel successful',
                'location' => $newLocation
            ]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Travel failed']);
        }
    }
    
    public function explore()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $user = $this->auth->getCurrentUser();
        $userProfile = $this->userModel->getProfile($user['id']);
        
        // Check if user has enough energy
        if ($userProfile['current_energy'] < 10) {
            $this->jsonResponse(['success' => false, 'message' => 'Not enough energy to explore']);
            return;
        }
        
        // Simulate exploration results
        $results = $this->simulateExploration($user['id'], $userProfile['last_location_id']);
        
        // Update user energy
        $this->userModel->update($user['id'], [
            'current_energy' => $userProfile['current_energy'] - 10
        ]);
        
        $this->jsonResponse([
            'success' => true,
            'results' => $results,
            'energyUsed' => 10
        ]);
    }
    
    private function simulateExploration($userId, $locationId)
    {
        $location = $this->worldMap->getLocationById($locationId);
        $resources = $this->worldMap->getResourcesAtLocation($locationId);
        
        $results = [
            'experience' => rand(5, 15),
            'items' => [],
            'events' => []
        ];
        
        // Random chance to find resources
        foreach ($resources as $resource) {
            if (rand(1, 100) <= $resource['spawn_rate']) {
                $amount = rand(1, 3);
                $results['items'][] = [
                    'name' => $resource['name'],
                    'amount' => $amount,
                    'rarity' => $resource['rarity']
                ];
            }
        }
        
        // Random events based on location danger level
        if (rand(1, 100) <= $location['danger_level'] * 10) {
            $events = [
                'You encountered hostile raiders!',
                'You found an abandoned shelter.',
                'You discovered a hidden cache.',
                'You spotted dangerous radiation.'
            ];
            $results['events'][] = $events[array_rand($events)];
        }
        
        return $results;
    }
}