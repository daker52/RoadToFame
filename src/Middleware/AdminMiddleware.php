<?php

namespace WastelandDominion\Middleware;

use WastelandDominion\Controllers\BaseController;

class AdminMiddleware extends BaseController
{
    /**
     * Handle middleware execution
     */
    public function handle(callable $next)
    {
        // Check if user is authenticated
        if (!$this->isAuthenticated()) {
            $this->redirectToLogin();
            return;
        }
        
        // Check if user is admin
        if (!$this->isAdmin()) {
            $this->accessDenied();
            return;
        }
        
        // Continue to next middleware or controller
        return $next();
    }
    
    /**
     * Check if user is authenticated
     */
    protected function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
    }
    
    /**
     * Check if current user is admin
     */
    private function isAdmin(): bool
    {
        if (!$this->isAuthenticated()) {
            return false;
        }
        
        $userId = $_SESSION['user_id'];
        
        try {
            $stmt = $this->db->prepare("
                SELECT is_admin FROM users WHERE id = ? AND is_active = 1
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            return $user && (bool)$user['is_admin'];
            
        } catch (\Exception $e) {
            // Log error and deny access
            error_log("AdminMiddleware error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Redirect to login page
     */
    private function redirectToLogin(): void
    {
        if ($this->isJsonRequest()) {
            $this->error('Authentication required', 401);
        } else {
            header('Location: /login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
    }
    
    /**
     * Show access denied response
     */
    private function accessDenied(): void
    {
        if ($this->isJsonRequest()) {
            $this->error('Admin access required', 403);
        } else {
            http_response_code(403);
            echo '<!DOCTYPE html><html><head><title>Access Denied</title></head><body>';
            echo '<h1>🚫 Access Denied</h1>';
            echo '<p>You need admin privileges to access this area.</p>';
            echo '<a href="/">Return to Home</a>';
            echo '</body></html>';
            exit;
        }
    }
    
    /**
     * Check if request expects JSON response
     */
    private function isJsonRequest(): bool
    {
        $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        return strpos($acceptHeader, 'application/json') !== false ||
               strpos($contentType, 'application/json') !== false ||
               strpos($_SERVER['REQUEST_URI'], '/api/') === 0;
    }
}