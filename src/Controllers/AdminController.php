<?php
namespace App\Controllers;

use App\Auth;
use App\Models\User;
use Exception;

class AdminController extends BaseController
{
    private $auth;
    private $userModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->auth = new Auth();
        $this->userModel = new User();
        
        // Check if user is logged in and is admin
        if (!$this->auth->isLoggedIn() || !$this->isAdmin()) {
            $this->redirect('/');
        }
    }
    
    private function isAdmin(): bool
    {
        $user = $this->auth->getCurrentUser();
        return $user && isset($user['is_admin']) && $user['is_admin'] == 1;
    }
    
    public function index()
    {
        $stats = $this->getDashboardStats();
        $this->render('admin/dashboard', ['stats' => $stats]);
    }
    
    public function users()
    {
        $page = (int)($_GET['page'] ?? 1);
        $search = $_GET['search'] ?? '';
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $users = $this->userModel->getUsers($search, $perPage, $offset);
        $totalUsers = $this->userModel->getUserCount($search);
        $totalPages = ceil($totalUsers / $perPage);
        
        $this->render('admin/users', [
            'users' => $users,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'totalUsers' => $totalUsers
        ]);
    }
    
    public function userAction()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $action = $_POST['action'] ?? '';
        $userId = (int)($_POST['user_id'] ?? 0);
        
        if (!$userId) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid user ID']);
            return;
        }
        
        try {
            switch ($action) {
                case 'ban':
                    $result = $this->userModel->banUser($userId);
                    break;
                case 'unban':
                    $result = $this->userModel->unbanUser($userId);
                    break;
                case 'delete':
                    $result = $this->userModel->deleteUser($userId);
                    break;
                case 'promote':
                    $result = $this->userModel->promoteToAdmin($userId);
                    break;
                case 'demote':
                    $result = $this->userModel->demoteFromAdmin($userId);
                    break;
                default:
                    $this->jsonResponse(['success' => false, 'message' => 'Invalid action']);
                    return;
            }
            
            if ($result) {
                $this->jsonResponse(['success' => true, 'message' => 'Action completed successfully']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Action failed']);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    public function settings()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateSettings();
            return;
        }
        
        $settings = $this->getSystemSettings();
        $this->render('admin/settings', ['settings' => $settings]);
    }
    
    private function updateSettings()
    {
        $allowedSettings = [
            'site_name',
            'maintenance_mode',
            'registration_enabled',
            'max_users_per_city',
            'daily_quest_limit'
        ];
        
        $updates = [];
        foreach ($allowedSettings as $setting) {
            if (isset($_POST[$setting])) {
                $updates[$setting] = $_POST[$setting];
            }
        }
        
        if (!empty($updates)) {
            $this->userModel->updateSystemSettings($updates);
            $this->jsonResponse(['success' => true, 'message' => 'Settings updated successfully']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'No settings to update']);
        }
    }
    
    private function getDashboardStats(): array
    {
        return [
            'total_users' => $this->userModel->getUserCount(),
            'active_users' => $this->userModel->getActiveUserCount(),
            'banned_users' => $this->userModel->getBannedUserCount(),
            'new_users_today' => $this->userModel->getNewUsersToday(),
            'online_users' => $this->userModel->getOnlineUserCount()
        ];
    }
    
    private function getSystemSettings(): array
    {
        return [
            'site_name' => 'Wasteland Dominion',
            'maintenance_mode' => false,
            'registration_enabled' => true,
            'max_users_per_city' => 1000,
            'daily_quest_limit' => 10
        ];
    }
}