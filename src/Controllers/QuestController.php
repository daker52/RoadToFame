<?php
namespace App\Controllers;

use App\Auth;
use App\Models\Quest;
use App\Models\User;

class QuestController extends BaseController
{
    private $auth;
    private $questModel;
    private $userModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->auth = new Auth();
        $this->questModel = new Quest();
        $this->userModel = new User();
        
        // Require authentication
        if (!$this->auth->isLoggedIn()) {
            $this->redirect('/');
        }
    }
    
    public function index()
    {
        $user = $this->auth->getCurrentUser();
        $userProfile = $this->userModel->getProfile($user['id']);
        $availableQuests = $this->questModel->getAvailableQuests($user['id'], $userProfile['last_location_id']);
        $activeQuests = $this->questModel->getUserActiveQuests($user['id']);
        
        $this->render('game/quests', [
            'availableQuests' => $availableQuests,
            'activeQuests' => $activeQuests,
            'userProfile' => $userProfile
        ]);
    }
    
    public function quest($id)
    {
        $user = $this->auth->getCurrentUser();
        $quest = $this->questModel->getQuestById($id);
        
        if (!$quest) {
            $this->jsonResponse(['success' => false, 'message' => 'Quest not found'], 404);
            return;
        }
        
        $instance = $this->questModel->getUserQuestInstance($user['id'], $id);
        $objectives = $this->questModel->getQuestObjectives($id);
        
        $this->jsonResponse([
            'success' => true,
            'quest' => $quest,
            'instance' => $instance,
            'objectives' => $objectives
        ]);
    }
    
    public function accept()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $user = $this->auth->getCurrentUser();
        $questId = (int)($_POST['quest_id'] ?? 0);
        
        if (!$questId) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid quest ID']);
            return;
        }
        
        if ($this->questModel->acceptQuest($user['id'], $questId)) {
            $quest = $this->questModel->getQuestById($questId);
            $this->jsonResponse([
                'success' => true,
                'message' => 'Quest accepted successfully',
                'quest' => $quest
            ]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to accept quest']);
        }
    }
    
    public function abandon()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $user = $this->auth->getCurrentUser();
        $questId = (int)($_POST['quest_id'] ?? 0);
        
        if (!$questId) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid quest ID']);
            return;
        }
        
        if ($this->questModel->abandonQuest($user['id'], $questId)) {
            $this->jsonResponse([
                'success' => true,
                'message' => 'Quest abandoned'
            ]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to abandon quest']);
        }
    }
    
    public function complete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $user = $this->auth->getCurrentUser();
        $questId = (int)($_POST['quest_id'] ?? 0);
        
        if (!$questId) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid quest ID']);
            return;
        }
        
        // Check if quest can be completed
        if (!$this->questModel->checkQuestCompletion($user['id'], $questId)) {
            $this->jsonResponse(['success' => false, 'message' => 'Quest objectives not completed']);
            return;
        }
        
        if ($this->questModel->completeQuest($user['id'], $questId)) {
            $quest = $this->questModel->getQuestById($questId);
            $rewards = json_decode($quest['rewards'], true);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Quest completed successfully!',
                'rewards' => $rewards
            ]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to complete quest']);
        }
    }
    
    public function updateProgress()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $user = $this->auth->getCurrentUser();
        $questId = (int)($_POST['quest_id'] ?? 0);
        $objectiveId = $_POST['objective_id'] ?? '';
        $progress = (int)($_POST['progress'] ?? 0);
        
        if (!$questId || !$objectiveId) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid parameters']);
            return;
        }
        
        $instance = $this->questModel->getUserQuestInstance($user['id'], $questId);
        if (!$instance || $instance['status'] !== 'in_progress') {
            $this->jsonResponse(['success' => false, 'message' => 'Quest not active']);
            return;
        }
        
        $currentProgress = json_decode($instance['progress'], true) ?: [];
        $currentProgress[$objectiveId] = max($currentProgress[$objectiveId] ?? 0, $progress);
        
        if ($this->questModel->updateQuestProgress($user['id'], $questId, $currentProgress)) {
            // Check if quest is now complete
            $isComplete = $this->questModel->checkQuestCompletion($user['id'], $questId);
            
            $this->jsonResponse([
                'success' => true,
                'progress' => $currentProgress,
                'isComplete' => $isComplete
            ]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to update progress']);
        }
    }
    
    public function npcDialog($npcId)
    {
        $user = $this->auth->getCurrentUser();
        
        // Get NPC information
        $npc = $this->db->query("
            SELECT * FROM npcs WHERE id = ? AND is_active = 1
        ", [$npcId])->fetch();
        
        if (!$npc) {
            $this->jsonResponse(['success' => false, 'message' => 'NPC not found'], 404);
            return;
        }
        
        // Get available quests from this NPC
        $quests = $this->questModel->getAvailableQuests($user['id']);
        $npcQuests = array_filter($quests, function($quest) use ($npcId) {
            return $quest['npc_id'] == $npcId;
        });
        
        // Get dialog options
        $dialog = json_decode($npc['dialog_options'], true) ?: [];
        
        $this->jsonResponse([
            'success' => true,
            'npc' => $npc,
            'quests' => array_values($npcQuests),
            'dialog' => $dialog
        ]);
    }
}