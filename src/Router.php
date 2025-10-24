<?php

namespace WastelandDominion;

class Router
{
    private $routes = [];
    private $middlewares = [];
    
    public function get(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }
    
    public function post(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }
    
    public function put(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middlewares);
    }
    
    public function delete(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middlewares);
    }
    
    private function addRoute(string $method, string $path, $handler, array $middlewares): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middlewares' => $middlewares,
            'pattern' => $this->createPattern($path)
        ];
    }
    
    private function createPattern(string $path): string
    {
        // Convert route parameters like {id} to regex patterns
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
    
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove leading slash for consistency
        $uri = ltrim($uri, '/');
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            if (preg_match($route['pattern'], $uri, $matches)) {
                array_shift($matches); // Remove full match
                
                // Execute middlewares
                foreach ($route['middlewares'] as $middleware) {
                    $this->executeMiddleware($middleware);
                }
                
                // Execute handler
                $this->executeHandler($route['handler'], $matches);
                return;
            }
        }
        
        // No route found
        $this->handleNotFound();
    }
    
    private function executeMiddleware($middleware): void
    {
        if (is_string($middleware) && class_exists($middleware)) {
            $middlewareInstance = new $middleware();
            if (method_exists($middlewareInstance, 'handle')) {
                $middlewareInstance->handle();
            }
        } elseif (is_callable($middleware)) {
            call_user_func($middleware);
        }
    }
    
    private function executeHandler($handler, array $params = []): void
    {
        if (is_string($handler)) {
            // Handle "Controller@method" format
            if (strpos($handler, '@') !== false) {
                [$controllerName, $method] = explode('@', $handler);
                $controllerClass = "WastelandDominion\\Controllers\\{$controllerName}";
                
                if (class_exists($controllerClass)) {
                    $controller = new $controllerClass();
                    if (method_exists($controller, $method)) {
                        call_user_func_array([$controller, $method], $params);
                        return;
                    }
                }
            }
            
            // Handle file includes
            $this->includeFile($handler);
        } elseif (is_callable($handler)) {
            call_user_func_array($handler, $params);
        } else {
            throw new \Exception("Invalid route handler");
        }
    }
    
    private function includeFile(string $file): void
    {
        $filePath = __DIR__ . '/../public/' . ltrim($file, '/');
        
        if (file_exists($filePath)) {
            include $filePath;
        } else {
            throw new \Exception("Route file not found: {$filePath}");
        }
    }
    
    private function handleNotFound(): void
    {
        http_response_code(404);
        
        // Try to include 404 template
        $notFoundTemplate = __DIR__ . '/../templates/errors/404.php';
        if (file_exists($notFoundTemplate)) {
            include $notFoundTemplate;
        } else {
            echo json_encode([
                'error' => 'Not Found',
                'message' => 'The requested resource was not found.',
                'code' => 404
            ]);
        }
    }
    
    public function group(string $prefix, callable $callback, array $middlewares = []): void
    {
        $originalMiddlewares = $this->middlewares;
        $this->middlewares = array_merge($this->middlewares, $middlewares);
        
        // Store current routes count
        $routesBeforeGroup = count($this->routes);
        
        // Execute the group callback
        call_user_func($callback, $this);
        
        // Add prefix to all routes added in this group
        for ($i = $routesBeforeGroup; $i < count($this->routes); $i++) {
            $this->routes[$i]['path'] = $prefix . $this->routes[$i]['path'];
            $this->routes[$i]['pattern'] = $this->createPattern($this->routes[$i]['path']);
            $this->routes[$i]['middlewares'] = array_merge($middlewares, $this->routes[$i]['middlewares']);
        }
        
        // Restore original middlewares
        $this->middlewares = $originalMiddlewares;
    }
}