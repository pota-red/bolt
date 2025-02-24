<?php

namespace Pota\Bolt;

use Google\Cloud\PubSub\PubSubClient;
use Psr\Http\Message\ResponseInterface;

class Router {

    public const string HTTP_GET = 'GET';
    public const string HTTP_POST = 'POST';
    public const string HTTP_PUT = 'PUT';
    public const string HTTP_PATCH = 'PATCH';
    public const string HTTP_DELETE = 'DELETE';

    public array $routes = [];
    private Bolt $app;

    public function __construct(Bolt $app) {
        $this->app = $app;
    }

    private function isMethod(string $method): bool {
        $methods = [self::HTTP_GET, self::HTTP_POST, self::HTTP_PUT, self::HTTP_PATCH, self::HTTP_DELETE];
        return in_array(strtoupper($method), $methods);
    }

    private function isAvailable(string $method, string $path): bool {
        if ($this->isMethod($method)) {
            $path = trim(strtolower($path));
            return array_key_exists($method, $this->routes) && array_key_exists($path, $this->routes[$method]);
        }
        return false;
    }

    public function register(string $httpMethod, string $uriPath, string $handler): void {
        $httpMethod = strtoupper($httpMethod);
        if (!$this->isMethod($httpMethod)) {
            throw new \Exception("HTTP method {$httpMethod} not supported");
        }
        if (!array_key_exists($httpMethod, $this->routes)) {
            $this->routes[$httpMethod] = [];
        }
        $uriPath = trim(strtolower($uriPath));
        if (array_key_exists($uriPath, $this->routes[$httpMethod])) {
            throw new \Exception("Duplicate URI path {$uriPath}");
        }
        $handler = [$this->app, $handler];
        if (!is_callable($handler)) {
            throw new \Exception("Handler is not callable");
        }
        $this->routes[$httpMethod][$uriPath] = $handler;
    }

    public function get(string $uriPath, string $handler): void {
        $this->register(self::HTTP_GET, $uriPath, $handler);
    }

    public function post(string $uriPath, string $handler): void {
        $this->register(self::HTTP_POST, $uriPath, $handler);
    }

    public function handle(string $httpMethod, string $uriPath, ...$args): mixed {
        if (!$this->isAvailable($httpMethod, $uriPath)) {
            $httpMethod = strtoupper($httpMethod);
            $uriPath = trim(strtolower($uriPath));
            return call_user_func($this->routes[$httpMethod][$uriPath], $args);
        } else {
            throw new \Exception("Invalid route {$httpMethod} {$uriPath}");
        }
    }

}
