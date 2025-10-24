<?php
namespace App\Controllers;

use App\Auth;
use App\Models\User;

class CharacterController extends BaseController
{
    private $auth;
    private $userModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->auth = new Auth();
        $this->userModel = new User();
        
        // Require authentication
        if (!$this->auth->isLoggedIn()) {
            $this->redirect('/');
        }
    }
    
    public function profile()
    {
        $user = $this->auth->getCurrentUser();
        $profile = $this->userModel->getProfile($user['id']);
        $stats = $this->userModel->getCharacterStats($user['id']);
        $skillPoints = $this->calculateAvailableSkillPoints($stats);
        
        $this->render('game/character', [
            'profile' => $profile,
            'stats' => $stats,
            'skillPoints' => $skillPoints
        ]);
    }
    
    public function upgradeAttribute()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $user = $this->auth->getCurrentUser();
        $attribute = $_POST['attribute'] ?? '';
        $points = (int)($_POST['points'] ?? 1);
        
        $validAttributes = ['strength', 'agility', 'intelligence', 'endurance', 'luck'];
        
        if (!in_array($attribute, $validAttributes)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid attribute']);
            return;
        }
        
        if ($points < 1 || $points > 10) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid points amount']);
            return;
        }
        
        $stats = $this->userModel->getCharacterStats($user['id']);
        $availablePoints = $this->calculateAvailableSkillPoints($stats);
        
        if ($availablePoints < $points) {
            $this->jsonResponse(['success' => false, 'message' => 'Not enough skill points']);
            return;
        }
        
        // Calculate cost (each point costs more based on current level)
        $currentValue = $stats[$attribute] ?? 10;
        $totalCost = 0;
        for ($i = 0; $i < $points; $i++) {
            $totalCost += ceil(($currentValue + $i) / 5) + 1;
        }
        
        if ($availablePoints < $totalCost) {
            $this->jsonResponse(['success' => false, 'message' => 'Not enough skill points for this upgrade']);
            return;
        }
        
        // Perform upgrade
        $newValue = $currentValue + $points;
        $updateData = [
            $attribute => $newValue,
            'skill_points_used' => ($stats['skill_points_used'] ?? 0) + $totalCost
        ];
        
        // Update health and energy based on attributes
        if ($attribute === 'endurance') {
            $healthIncrease = $points * 10;
            $updateData['max_health'] = $stats['max_health'] + $healthIncrease;
            $updateData['current_health'] = min($stats['current_health'] + $healthIncrease, $updateData['max_health']);
        }
        
        if ($attribute === 'agility') {
            $energyIncrease = $points * 5;
            $updateData['max_energy'] = $stats['max_energy'] + $energyIncrease;
            $updateData['current_energy'] = min($stats['current_energy'] + $energyIncrease, $updateData['max_energy']);
        }
        
        $this->userModel->updateCharacterStats($user['id'], $updateData);
        
        $this->jsonResponse([
            'success' => true,
            'message' => "Successfully upgraded {$attribute} by {$points} points",
            'newValue' => $newValue,
            'pointsUsed' => $totalCost
        ]);
    }
    
    public function gainExperience()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $user = $this->auth->getCurrentUser();
        $experience = (int)($_POST['experience'] ?? 0);
        $source = $_POST['source'] ?? 'unknown';
        
        if ($experience <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid experience amount']);
            return;
        }
        
        $stats = $this->userModel->getCharacterStats($user['id']);
        $newExperience = $stats['experience'] + $experience;
        $newLevel = $this->calculateLevel($newExperience);
        $leveledUp = $newLevel > $stats['level'];
        
        $updateData = ['experience' => $newExperience];
        
        if ($leveledUp) {
            $updateData['level'] = $newLevel;
            
            // Level up bonuses
            $healthBonus = ($newLevel - $stats['level']) * 5;
            $energyBonus = ($newLevel - $stats['level']) * 3;
            
            $updateData['max_health'] = $stats['max_health'] + $healthBonus;
            $updateData['max_energy'] = $stats['max_energy'] + $energyBonus;
            $updateData['current_health'] = $stats['max_health'] + $healthBonus;
            $updateData['current_energy'] = $stats['max_energy'] + $energyBonus;
        }
        
        $this->userModel->updateCharacterStats($user['id'], $updateData);
        
        // Log experience gain
        $this->userModel->logExperienceGain($user['id'], $experience, $source);
        
        $response = [
            'success' => true,
            'experienceGained' => $experience,
            'newExperience' => $newExperience,
            'leveledUp' => $leveledUp
        ];
        
        if ($leveledUp) {
            $response['newLevel'] = $newLevel;
            $response['bonuses'] = [
                'health' => $healthBonus,
                'energy' => $energyBonus
            ];
        }
        
        $this->jsonResponse($response);
    }
    
    private function calculateLevel($experience)
    {
        return floor(sqrt($experience / 100)) + 1;
    }
    
    private function calculateExperienceForLevel($level)
    {
        return pow($level - 1, 2) * 100;
    }
    
    private function calculateAvailableSkillPoints($stats)
    {
        $totalPoints = ($stats['level'] - 1) * 3; // 3 points per level
        $usedPoints = $stats['skill_points_used'] ?? 0;
        return max(0, $totalPoints - $usedPoints);
    }
}