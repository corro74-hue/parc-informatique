<?php
declare(strict_types=1);

namespace App\Core;

use App\Middleware\PermissionMiddleware;

final class Router
{
    /** @var array<string, array<string, array{handler: callable|array, options: array}>> */
    private array $routes = [
        'GET'    => [],
        'POST'   => [],
        'PUT'    => [],
        'DELETE' => [],
    ];

    public function get(string $path, callable|array $handler, array $options = []): void
    {
        $this->addRoute('GET', $path, $handler, $options);
    }

    public function post(string $path, callable|array $handler, array $options = []): void
    {
        $this->addRoute('POST', $path, $handler, $options);
    }

    public function put(string $path, callable|array $handler, array $options = []): void
    {
        $this->addRoute('PUT', $path, $handler, $options);
    }

    public function delete(string $path, callable|array $handler, array $options = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $options);
    }

    private function addRoute(string $method, string $path, callable|array $handler, array $options = []): void
    {
        $this->routes[$method][$path] = [
            'handler' => $handler,
            'options' => $options,
        ];
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method;
        $uri    = $request->uri;

        if (!isset($this->routes[$method])) {
            return new Response('Méthode non supportée', 405);
        }

        // ============================================
        // Route exacte (sans paramètre dynamique)
        // ============================================
        if (isset($this->routes[$method][$uri])) {
            $route = $this->routes[$method][$uri];
            return $this->invokeWithMiddleware(
                $route['handler'],
                $request,
                [],
                $route['options']
            );
        }

        // ============================================
        // Routes dynamiques (avec {id}, etc.)
        // ============================================
        foreach ($this->routes[$method] as $routePath => $route) {
            $pattern = $this->compileRoute($routePath);

            if (preg_match($pattern, $uri, $matches)) {
                // Ne garder que les paramètres nommés (pas les index numériques)
                $namedParams = array_filter(
                    $matches,
                    fn($key) => is_string($key),
                    ARRAY_FILTER_USE_KEY
                );
                return $this->invokeWithMiddleware(
                    $route['handler'],
                    $request,
                    $namedParams,
                    $route['options']
                );
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

    /**
     * Exécute les middlewares (si présents) puis le contrôleur.
     */
    private function invokeWithMiddleware(
        callable|array $handler,
        Request $request,
        array $params,
        array $options
    ): Response {
        // ============================================
        // Middleware : PermissionMiddleware
        // ============================================
        if (!empty($options['permission'])) {
            $permMiddleware = new PermissionMiddleware();
            $response = $permMiddleware->handle($options['permission']);
            if ($response instanceof Response) {
                return $response;
            }
        }

        return $this->invoke($handler, $request, $params);
    }

    private function invoke(callable|array $handler, Request $request, array $params): Response
    {
        // Les paramètres d'URL sont passés tels quels (string).
        // Le cast en int est fait DANS LES CONTRÔLEURS si nécessaire.
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