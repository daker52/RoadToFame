<?php

namespace WastelandDominion\Middleware;

class RateLimitMiddleware
{
    private $limits;
    private $storage;
    
    public function __construct(array $limits = [])
    {
        $this->limits = array_merge([
            'api' => 100,     // requests per minute
            'auth' => 5,      // login attempts per minute
            'default' => 60   // default rate limit
        ], $limits);
        
        // Use file-based storage for simplicity
        $this->storage = sys_get_temp_dir() . '/wasteland_rate_limit/';
        if (!is_dir($this->storage)) {
            mkdir($this->storage, 0755, true);
        }
    }
    
    /**
     * Handle middleware execution
     */
    public function handle(callable $next, string $type = 'default')
    {
        $clientId = $this->getClientId();
        $limit = $this->limits[$type] ?? $this->limits['default'];
        
        if (!$this->checkRateLimit($clientId, $type, $limit)) {
            $this->rateLimitExceeded();
            return;
        }
        
        // Record this request
        $this->recordRequest($clientId, $type);
        
        // Continue to next middleware or controller
        return $next();
    }
    
    /**
     * Get unique client identifier
     */
    private function getClientId(): string
    {
        // Use IP + User Agent for identification
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        return md5($ip . $userAgent);
    }
    
    /**
     * Check if request is within rate limit
     */
    private function checkRateLimit(string $clientId, string $type, int $limit): bool
    {
        $filename = $this->storage . "{$clientId}_{$type}.txt";
        
        if (!file_exists($filename)) {
            return true;
        }
        
        $requests = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $currentTime = time();
        $validRequests = [];
        
        // Filter requests from last minute
        foreach ($requests as $timestamp) {
            if ($currentTime - (int)$timestamp < 60) {
                $validRequests[] = $timestamp;
            }
        }
        
        // Update file with valid requests
        file_put_contents($filename, implode("\n", $validRequests));
        
        return count($validRequests) < $limit;
    }
    
    /**
     * Record current request
     */
    private function recordRequest(string $clientId, string $type): void
    {
        $filename = $this->storage . "{$clientId}_{$type}.txt";
        $currentTime = time();
        
        file_put_contents($filename, $currentTime . "\n", FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Handle rate limit exceeded
     */
    private function rateLimitExceeded(): void
    {
        http_response_code(429);
        header('Content-Type: application/json');
        
        echo json_encode([
            'success' => false,
            'error' => 'Rate limit exceeded',
            'message' => 'Too many requests. Please try again later.',
            'retry_after' => 60
        ]);
        
        exit;
    }
    
    /**
     * Clean up old rate limit files
     */
    public function cleanup(): void
    {
        $files = glob($this->storage . '*.txt');
        $cutoff = time() - 3600; // Remove files older than 1 hour
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
            }
        }
    }
}