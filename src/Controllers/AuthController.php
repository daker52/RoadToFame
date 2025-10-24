<?php

namespace WastelandDominion\Controllers;

use WastelandDominion\Models\User;
use WastelandDominion\JWTHelper;

class AuthController extends BaseController
{
    private $userModel;
    private $jwtHelper;
    
    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->jwtHelper = new JWTHelper(
            $this->config['security']['jwt_secret'],
            $this->config['security']['session_lifetime']
        );
        
        // Start session for traditional session management
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function register(): void
    {
        $input = $this->getInput();
        
        // Validate input
        $errors = $this->validate($input, [
            'username' => 'required|min:3|max:30',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'password_confirm' => 'required'
        ]);
        
        // Check password confirmation
        if (empty($errors) && $input['password'] !== $input['password_confirm']) {
            $errors['password_confirm'][] = 'Password confirmation does not match';
        }
        
        // Check if username already exists
        if (empty($errors) && $this->userModel->isUsernameTaken($input['username'])) {
            $errors['username'][] = 'Username already exists';
        }
        
        // Check if email already exists
        if (empty($errors) && $this->userModel->isEmailTaken($input['email'])) {
            $errors['email'][] = 'Email already exists';
        }
        
        if (!empty($errors)) {
            $this->error('Validation failed', 422, $errors);
        }
        
        try {
            // Create user
            $userId = $this->userModel->createUser([
                'username' => $this->sanitize($input['username']),
                'email' => $this->sanitize($input['email']),
                'password' => $input['password'],
                'is_active' => true
            ]);
            
            // Get user data
            $user = $this->userModel->find($userId);
            
            // Create session
            $this->createUserSession($user);
            
            $this->success('Registration successful! Welcome to the Wasteland!', [
                'user' => $user,
                'redirect' => '/game/character-setup'
            ]);
            
        } catch (\Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            $this->error('Registration failed. Please try again.', 500);
        }
    }
    
    public function login(): void
    {
        $input = $this->getInput();
        
        // Validate input
        $errors = $this->validate($input, [
            'username' => 'required',
            'password' => 'required'
        ]);
        
        if (!empty($errors)) {
            $this->error('Validation failed', 422, $errors);
        }
        
        // Rate limiting (simple approach)
        $this->checkLoginRateLimit();
        
        try {
            // Find user by credentials
            $user = $this->userModel->findByCredentials(
                $this->sanitize($input['username']),
                $input['password']
            );
            
            if (!$user) {
                $this->recordFailedLogin();
                $this->error('Invalid username or password', 401);
            }
            
            if (!$user['is_active']) {
                $this->error('Account is inactive. Please contact support.', 403);
            }
            
            // Update last login
            $this->userModel->updateLastLogin($user['id']);
            
            // Create session
            $this->createUserSession($user);
            
            $this->success('Login successful! Welcome back to the Wasteland!', [
                'user' => $user,
                'redirect' => '/game/dashboard'
            ]);
            
        } catch (\Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $this->error('Login failed. Please try again.', 500);
        }
    }
    
    public function logout(): void
    {
        try {
            // Clear session
            if (isset($_SESSION['user_id'])) {
                $userId = $_SESSION['user_id'];
                
                // Remove JWT token from database if stored
                $this->db->delete(
                    'user_sessions',
                    'user_id = ?',
                    [$userId]
                );
            }
            
            // Destroy session
            session_destroy();
            
            $this->success('Logged out successfully', [
                'redirect' => '/'
            ]);
            
        } catch (\Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            $this->error('Logout failed', 500);
        }
    }
    
    public function profile(): void
    {
        $this->requireAuth();
        
        $userId = $_SESSION['user_id'];
        $profile = $this->userModel->getProfile($userId);
        
        if (!$profile) {
            $this->error('Profile not found', 404);
        }
        
        $this->success('Profile retrieved', [
            'profile' => $profile
        ]);
    }
    
    public function updateProfile(): void
    {
        $this->requireAuth();
        
        $input = $this->getInput();
        $userId = $_SESSION['user_id'];
        
        // Validate input
        $errors = $this->validate($input, [
            'display_name' => 'required|min:3|max:50'
        ]);
        
        if (!empty($errors)) {
            $this->error('Validation failed', 422, $errors);
        }
        
        try {
            // Update profile
            $updated = $this->db->update(
                'user_profiles',
                [
                    'display_name' => $this->sanitize($input['display_name'])
                ],
                'user_id = ?',
                [$userId]
            );
            
            if ($updated) {
                $this->success('Profile updated successfully');
            } else {
                $this->error('Profile update failed', 500);
            }
            
        } catch (\Exception $e) {
            error_log("Profile update error: " . $e->getMessage());
            $this->error('Profile update failed', 500);
        }
    }
    
    public function changePassword(): void
    {
        $this->requireAuth();
        
        $input = $this->getInput();
        $userId = $_SESSION['user_id'];
        
        // Validate input
        $errors = $this->validate($input, [
            'current_password' => 'required',
            'new_password' => 'required|min:6',
            'new_password_confirm' => 'required'
        ]);
        
        if (empty($errors) && $input['new_password'] !== $input['new_password_confirm']) {
            $errors['new_password_confirm'][] = 'Password confirmation does not match';
        }
        
        if (!empty($errors)) {
            $this->error('Validation failed', 422, $errors);
        }
        
        try {
            // Get current user
            $user = $this->userModel->find($userId);
            
            // Verify current password
            if (!$this->userModel->verifyPassword($input['current_password'], $user['password_hash'])) {
                $this->error('Current password is incorrect', 401);
            }
            
            // Update password
            $updated = $this->userModel->update($userId, [
                'password_hash' => password_hash($input['new_password'], PASSWORD_DEFAULT)
            ]);
            
            if ($updated) {
                $this->success('Password changed successfully');
            } else {
                $this->error('Password change failed', 500);
            }
            
        } catch (\Exception $e) {
            error_log("Password change error: " . $e->getMessage());
            $this->error('Password change failed', 500);
        }
    }
    
    private function createUserSession(array $user): void
    {
        // Create traditional session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['login_time'] = time();
        
        // Generate JWT token
        $tokenPayload = [
            'user_id' => $user['id'],
            'username' => $user['username']
        ];
        
        $token = $this->jwtHelper->generateToken($tokenPayload);
        
        // Store token in database
        $this->db->insert('user_sessions', [
            'user_id' => $user['id'],
            'session_token' => $token,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'expires_at' => date('Y-m-d H:i:s', time() + $this->config['security']['session_lifetime'])
        ]);
        
        // Set token in cookie for API requests
        setcookie(
            'wd_token',
            $token,
            time() + $this->config['security']['session_lifetime'],
            '/',
            '',
            false,
            true // HttpOnly
        );
    }
    
    private function checkLoginRateLimit(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $attempts = $_SESSION['login_attempts_' . $ip] ?? 0;
        $lastAttempt = $_SESSION['last_login_attempt_' . $ip] ?? 0;
        
        // Reset attempts if last attempt was more than 1 hour ago
        if (time() - $lastAttempt > 3600) {
            $attempts = 0;
        }
        
        if ($attempts >= $this->config['security']['rate_limit']['auth']) {
            $this->error('Too many login attempts. Please try again later.', 429);
        }
    }
    
    private function recordFailedLogin(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $_SESSION['login_attempts_' . $ip] = ($_SESSION['login_attempts_' . $ip] ?? 0) + 1;
        $_SESSION['last_login_attempt_' . $ip] = time();
    }
}