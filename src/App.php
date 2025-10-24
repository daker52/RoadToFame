<?php

namespace WastelandDominion;

use Dotenv\Dotenv;

class App
{
    private static $instance = null;
    private $config;
    private $router;
    private $database;
    
    private function __construct()
    {
        $this->loadEnvironment();
        $this->loadConfig();
        $this->setupErrorHandling();
        $this->initializeServices();
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function loadEnvironment(): void
    {
        // Load .env file only if Dotenv is available (dev environment)
        if (class_exists('\Dotenv\Dotenv')) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
            $dotenv->safeLoad();
        }
    }
    
    private function loadConfig(): void
    {
        $this->config = require __DIR__ . '/../config/config.php';
    }
    
    private function setupErrorHandling(): void
    {
        if ($this->config['app']['debug']) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(0);
            ini_set('display_errors', 0);
        }
        
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
    }
    
    private function initializeServices(): void
    {
        $this->database = new Database($this->config['database']);
        $this->router = new Router();
    }
    
    public function run(): void
    {
        try {
            $this->router->dispatch();
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }
    
    public function getConfig(string $key = null)
    {
        if ($key === null) {
            return $this->config;
        }
        
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return null;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
    
    public function getDatabase(): Database
    {
        return $this->database;
    }
    
    public function getRouter(): Router
    {
        return $this->router;
    }
    
    public function handleError(int $errno, string $errstr, string $errfile, int $errline): void
    {
        if (!(error_reporting() & $errno)) {
            return;
        }
        
        $error = [
            'type' => 'Error',
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'time' => date('Y-m-d H:i:s')
        ];
        
        $this->logError($error);
        
        if ($this->config['app']['debug']) {
            $this->displayError($error);
        } else {
            $this->displayGenericError();
        }
    }
    
    public function handleException(\Throwable $exception): void
    {
        $error = [
            'type' => 'Exception',
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'time' => date('Y-m-d H:i:s')
        ];
        
        $this->logError($error);
        
        if ($this->config['app']['debug']) {
            $this->displayError($error);
        } else {
            $this->displayGenericError();
        }
    }
    
    private function logError(array $error): void
    {
        $logFile = __DIR__ . '/../logs/error.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logEntry = "[{$error['time']}] {$error['type']}: {$error['message']} in {$error['file']}:{$error['line']}\n";
        if (isset($error['trace'])) {
            $logEntry .= "Stack trace:\n{$error['trace']}\n";
        }
        $logEntry .= str_repeat('-', 80) . "\n";
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    private function displayError(array $error): void
    {
        http_response_code(500);
        
        echo "<!DOCTYPE html>\n";
        echo "<html><head><title>Wasteland Dominion - Error</title></head><body>\n";
        echo "<h1>🔥 Wasteland Error Detected</h1>\n";
        echo "<p><strong>{$error['type']}:</strong> {$error['message']}</p>\n";
        echo "<p><strong>File:</strong> {$error['file']}:{$error['line']}</p>\n";
        if (isset($error['trace'])) {
            echo "<h3>Stack Trace:</h3><pre>{$error['trace']}</pre>\n";
        }
        echo "</body></html>";
        
        exit;
    }
    
    private function displayGenericError(): void
    {
        http_response_code(500);
        include __DIR__ . '/../templates/errors/500.php';
        exit;
    }
}