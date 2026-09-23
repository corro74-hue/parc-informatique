<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = [], ?string $layout = 'app'): Response
    {
        $viewPath = dirname(__DIR__, 2) . '/resources/views/' . str_replace('.', '/', $view) . '.php';

        if (!is_file($viewPath)) {
            throw new \RuntimeException("Vue introuvable : $view");
        }

        $content = (function () use ($viewPath, $data) {
            extract($data, EXTR_SKIP);
            ob_start();
            require $viewPath;
            return ob_get_clean();
        })();

        if ($layout) {
            $layoutPath = dirname(__DIR__, 2) . '/resources/views/layouts/' . $layout . '.php';
            if (is_file($layoutPath)) {
                $content = (function () use ($layoutPath, $content, $data) {
                    extract($data, EXTR_SKIP);
                    ob_start();
                    require $layoutPath;
                    return ob_get_clean();
                })();
            }
        }

        return Response::html($content);
    }

    protected function json(mixed $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    protected function redirect(string $url, int $status = 302): Response
    {
        return Response::redirect($url, $status);
    }
}