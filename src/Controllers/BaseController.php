<?php

namespace WastelandDominion\Controllers;

use WastelandDominion\App;

abstract class BaseController
{
    protected $app;
    protected $db;
    protected $config;
    
    public function __construct()
    {
        $this->app = App::getInstance();
        $this->db = $this->app->getDatabase();
        $this->config = $this->app->getConfig();
    }
    
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function success(string $message = 'Success', array $data = []): void
    {
        $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }
    
    protected function error(string $message = 'Error', int $statusCode = 400, array $errors = []): void
    {
        $this->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }
    
    protected function view(string $template, array $data = []): void
    {
        extract($data);
        
        $templatePath = __DIR__ . '/../../templates/' . $template . '.php';
        
        if (!file_exists($templatePath)) {
            throw new \Exception("Template not found: {$template}");
        }
        
        include $templatePath;
    }
    
    protected function redirect(string $url, int $statusCode = 302): void
    {
        http_response_code($statusCode);
        header("Location: {$url}");
        exit;
    }
    
    protected function getInput(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($contentType, 'application/json') !== false) {
            $json = file_get_contents('php://input');
            return json_decode($json, true) ?? [];
        }
        
        return array_merge($_GET, $_POST);
    }
    
    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $rulesList = explode('|', $rule);
            $value = $data[$field] ?? null;
            
            foreach ($rulesList as $singleRule) {
                $ruleParts = explode(':', $singleRule);
                $ruleName = $ruleParts[0];
                $ruleValue = $ruleParts[1] ?? null;
                
                switch ($ruleName) {
                    case 'required':
                        if (empty($value)) {
                            $errors[$field][] = ucfirst($field) . ' is required';
                        }
                        break;
                        
                    case 'email':
                        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field][] = ucfirst($field) . ' must be a valid email';
                        }
                        break;
                        
                    case 'min':
                        if (!empty($value) && strlen($value) < (int)$ruleValue) {
                            $errors[$field][] = ucfirst($field) . " must be at least {$ruleValue} characters";
                        }
                        break;
                        
                    case 'max':
                        if (!empty($value) && strlen($value) > (int)$ruleValue) {
                            $errors[$field][] = ucfirst($field) . " must not exceed {$ruleValue} characters";
                        }
                        break;
                        
                    case 'unique':
                        if (!empty($value)) {
                            $table = $ruleValue;
                            $existing = $this->db->fetch("SELECT id FROM {$table} WHERE {$field} = ?", [$value]);
                            if ($existing) {
                                $errors[$field][] = ucfirst($field) . ' already exists';
                            }
                        }
                        break;
                }
            }
        }
        
        return $errors;
    }
    
    protected function sanitize(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    protected function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    protected function getCurrentUser(): ?array
    {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        return $this->db->fetch(
            "SELECT * FROM users WHERE id = ?",
            [$_SESSION['user_id']]
        );
    }
    
    protected function requireAuth(): void
    {
        if (!$this->isAuthenticated()) {
            $this->error('Authentication required', 401);
        }
    }
}