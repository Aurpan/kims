<?php
namespace App\Core;

class Router
{
    private array $routes = [];
    private string $currentUrl;
    private string $currentMethod;

    public function __construct()
    {
        $this->currentUrl = $this->parseUrl();
        $this->currentMethod = $_SERVER['REQUEST_METHOD'];
    }

    public function get(string $path, string $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, string $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function put(string $path, string $handler): void
    {
        $this->routes['PUT'][$path] = $handler;
    }

    public function delete(string $path, string $handler): void
    {
        $this->routes['DELETE'][$path] = $handler;
    }

    public function dispatch(): void
    {
        $method = $this->currentMethod;
        $url = $this->currentUrl;

        // Check exact match first
        if (isset($this->routes[$method][$url])) {
            $this->callHandler($this->routes[$method][$url]);
            return;
        }

        // Check for route parameters
        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $pattern => $handler) {
                if ($this->matchRoute($pattern, $url, $params)) {
                    $_GET = array_merge($_GET, $params);
                    $this->callHandler($handler);
                    return;
                }
            }
        }

        // Route not found
        header("HTTP/1.0 404 Not Found");
        echo "404 - Page Not Found";
    }

    private function parseUrl(): string
    {
        $url = $_GET['url'] ?? '';

        if (!$url) {
            $url = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
            $url = str_replace('/public/', '', $url);
        }

        return trim($url, '/');
    }

    private function matchRoute(string $pattern, string $url, ?array &$params = []): bool
    {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $url, $matches)) {
            $params = array_filter($matches, fn($key) => is_string($key), ARRAY_FILTER_USE_KEY);
            return true;
        }

        return false;
    }

    private function callHandler(string $handler): void
    {
        [$controller, $method] = explode('@', $handler);
        $controller = '\\App\\Controllers\\' . $controller;

        if (!class_exists($controller)) {
            die("Controller $controller not found");
        }

        if (!method_exists($controller, $method)) {
            die("Method $method not found in $controller");
        }

        $controllerInstance = new $controller();
        $controllerInstance->$method();
    }
}
