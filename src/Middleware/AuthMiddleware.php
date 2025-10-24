<?php

namespace WastelandDominion\Middleware;

use WastelandDominion\Controllers\BaseController;

class AuthMiddleware extends BaseController
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
        
        // Continue to next middleware or controller
        return $next();
    }
    
    /**
     * Check if user is authenticated
     */
    protected function isAuthenticated(): bool
    {
        // Check session
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0) {
            return true;
        }
        
        // Check JWT token
        $token = $this->getJwtToken();
        if ($token && $this->validateJwtToken($token)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get JWT token from request
     */
    private function getJwtToken(): ?string
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        
        if (strpos($authHeader, 'Bearer ') === 0) {
            return substr($authHeader, 7);
        }
        
        return null;
    }
    
    /**
     * Validate JWT token
     */
    private function validateJwtToken(string $token): bool
    {
        try {
            // This would use Firebase JWT library in real implementation
            // For now, just return true if token exists
            return !empty($token);
            
        } catch (\Exception $e) {
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
            $currentUrl = $_SERVER['REQUEST_URI'];
            header('Location: /login?redirect=' . urlencode($currentUrl));
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