<?php
/**
 * Clase Router
 * Enrutador REST ligero con soporte para middlewares y controladores.
 */

require_once __DIR__ . '/Request.php';
require_once __DIR__ . '/Response.php';
require_once __DIR__ . '/Logger.php';

class Router {
    private array $routes = [];

    public function get(string $path, array $handler, array $middlewares = []) {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, array $handler, array $middlewares = []) {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    public function put(string $path, array $handler, array $middlewares = []) {
        $this->addRoute('PUT', $path, $handler, $middlewares);
    }

    public function delete(string $path, array $handler, array $middlewares = []) {
        $this->addRoute('DELETE', $path, $handler, $middlewares);
    }

    private function addRoute(string $method, string $path, array $handler, array $middlewares) {
        $normalizedPath = '/' . trim($path, '/');
        $this->routes[] = [
            'method'      => $method,
            'path'        => $normalizedPath,
            'handler'     => $handler,
            'middlewares' => $middlewares
        ];
    }

    public function dispatch(Request $request) {
        $requestMethod = $request->getMethod();
        $requestUri = $request->getUri();

        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && $this->matchPath($route['path'], $requestUri)) {
                try {
                    // Ejecutar Middlewares registrados para la ruta
                    foreach ($route['middlewares'] as $middlewareClass) {
                        $middleware = new $middlewareClass();
                        $middleware->handle($request);
                    }

                    // Ejecutar Handler (Closure o Controlador)
                    if (is_callable($route['handler'])) {
                        return call_user_func($route['handler'], $request);
                    }

                    if (is_array($route['handler'])) {
                        [$controllerClass, $action] = $route['handler'];
                        $controller = new $controllerClass();

                        if (!method_exists($controller, $action)) {
                            Logger::error("Método {$action} no existe en {$controllerClass}");
                            Response::error('Recurso no implementado en el servidor.', 'METHOD_NOT_FOUND');
                        }

                        return $controller->$action($request);
                    }

                } catch (Throwable $e) {
                    // Registrar el error detallado de forma privada en logs/app.log
                    Logger::exception($e, [
                        'route'  => $route['path'],
                        'method' => $requestMethod
                    ]);

                    // Responder al frontend con JSON estándar limpio sin exponer información sensible
                    Response::error('Ocurrió un error inesperado en el servidor. Por favor intenta más tarde.', 'INTERNAL_SERVER_ERROR');
                }
            }
        }

        // Si ninguna ruta coincide
        Response::notFound("La ruta [{$requestMethod}] {$requestUri} no fue encontrada en esta API.");
    }

    private function matchPath(string $routePath, string $requestUri): bool {
        return strtolower($routePath) === strtolower($requestUri);
    }
}
