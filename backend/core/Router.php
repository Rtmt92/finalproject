<?php
declare(strict_types=1);

class Router {
    private string $url;
    private array $routes = [];

    public function __construct(string $url) {
        $this->url = rtrim($url, '/');
    }

    public function get(string $path, callable $callback) {
        $this->addRoute('GET', $path, $callback);
    }

    public function post(string $path, callable $callback) {
        $this->addRoute('POST', $path, $callback);
    }

    public function put(string $path, callable $callback) {
        $this->addRoute('PUT', $path, $callback);
        $this->addRoute('PATCH', $path, $callback);
    }

    public function delete(string $path, callable $callback) {
        $this->addRoute('DELETE', $path, $callback);
    }

    private function addRoute(string $method, string $path, callable $callback): void {
        $pattern = preg_replace('#:([\w]+)#', '([^/]+)', $path);
        $pattern = '#^' . rtrim($pattern, '/') . '$#';
        $this->routes[] = compact('method', 'pattern', 'callback');
    }

    public function run(): void {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($requestMethod === $route['method'] && preg_match($route['pattern'], $this->url, $matches)) {
                array_shift($matches); // remove full match
                call_user_func_array($route['callback'], $matches);
                return;
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'Route non trouvée']);
    }
}
