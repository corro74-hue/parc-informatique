<?php
declare(strict_types=1);

namespace App\Core;

final class Request
{
    private function __construct(
        public readonly string $method,
        public readonly string $uri,
        public readonly array $query,
        public readonly array $body,
        public readonly array $files,
        public readonly array $server,
        public readonly array $cookies,
        public readonly array $headers,
    ) {}

    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = parse_url($uri, PHP_URL_PATH) ?: '/';

        $basePath = '/parc-informatique/public';
        if (str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }
        $uri = '/' . trim($uri, '/');

        return new self(
            method:  $method,
            uri:     $uri,
            query:   $_GET,
            body:    $_POST,
            files:   $_FILES,
            server:  $_SERVER,
            cookies: $_COOKIE,
            headers: self::extractHeaders(),
        );
    }

    private static function extractHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$name] = $value;
            }
        }
        return $headers;
    }

    /**
     * Récupère une valeur (POST en priorité, puis GET).
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    /**
     * Récupère une valeur depuis la query string ($_GET).
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * Récupère une valeur depuis le body ($_POST).
     */
    public function post(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    public function isPost(): bool { return $this->method === 'POST'; }
    public function isGet(): bool  { return $this->method === 'GET'; }
    public function isAjax(): bool
    {
        return strtolower($this->headers['X-Requested-With'] ?? '') === 'xmlhttprequest';
    }
}