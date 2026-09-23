<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, array<string, callable|array>> */
    private array $routes = [
        'GET'    => [],
        'POST'   => [],
        'PUT'    => [],
        'DELETE' => [],
    ];

    public function get(string $path, callable|array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, callable|array $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, callable|array $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable|array $handler): void
    {
        $this->routes[$method][$path] = $handler;
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method;
        $uri    = $request->uri;

        if (!isset($this->routes[$method])) {
            return new Response('Méthode non supportée', 405);
        }

        if (isset($this->routes[$method][$uri])) {
            return $this->invoke($this->routes[$method][$uri], $request, []);
        }

        foreach ($this->routes[$method] as $route => $handler) {
            $pattern = $this->compileRoute($route);

            if (preg_match($pattern, $uri, $matches)) {
                // Ne garder que les paramètres nommés (pas les index numériques)
                $namedParams = array_filter(
                    $matches,
                    fn($key) => is_string($key),
                    ARRAY_FILTER_USE_KEY
                );
                return $this->invoke($handler, $request, $namedParams);
            }
        }

        return $this->notFound();
    }

    private function compileRoute(string $route): string
    {
        $pattern = preg_replace(
            '#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#',
            '(?P<$1>[^/]+)',
            $route
        );
        return '#^' . $pattern . '$#';
    }

    private function invoke(callable|array $handler, Request $request, array $params): Response
    {
        // Convertir les paramètres nommés en paramètres positionnels
        // pour éviter l'erreur "Cannot use positional argument after named argument"
        $positionalParams = array_values($params);

        if (is_callable($handler)) {
            $result = $handler($request, ...$positionalParams);
        } elseif (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;
            $controller = new $class();
            $result = $controller->$method($request, ...$positionalParams);
        } else {
            throw new \RuntimeException('Handler de route invalide.');
        }

        return $result instanceof Response ? $result : Response::html((string) $result);
    }

    private function notFound(): Response
    {
        $view = dirname(__DIR__, 2) . '/resources/views/errors/404.php';
        $content = is_file($view)
            ? (function () use ($view) { ob_start(); require $view; return ob_get_clean(); })()
            : '<h1>404 — Page non trouvée</h1>';

        return new Response($content, 404);
    }
}