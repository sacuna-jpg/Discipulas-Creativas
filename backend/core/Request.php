<?php
/**
 * Clase Request
 * Encapsula la información de la petición HTTP entrante (método, ruta, headers, query y JSON body).
 */

class Request {
    private string $method;
    private string $uri;
    private array $headers = [];
    private array $queryParams = [];
    private array $body = [];
    public ?array $user = null; // Almacenará la usuaria autenticada

    public function __construct() {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->parseUri();
        $this->parseHeaders();
        $this->queryParams = $_GET ?? [];
        $this->parseBody();
    }

    private function parseUri() {
        $rawUri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($rawUri, PHP_URL_PATH);

        // Remover prefijos de subcarpetas si se ejecuta en subdirectorios de XAMPP (ej. /DiscipulasCreativas/backend)
        $scriptName = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        if ($scriptName !== '/' && $scriptName !== '\\' && strpos($path, $scriptName) === 0) {
            $path = substr($path, strlen($scriptName));
        }

        $this->uri = '/' . trim($path, '/');
    }

    private function parseHeaders() {
        if (function_exists('getallheaders')) {
            $this->headers = getallheaders() ?: [];
        } else {
            foreach ($_SERVER as $key => $value) {
                if (substr($key, 0, 5) === 'HTTP_') {
                    $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                    $this->headers[$header] = $value;
                }
            }
        }
    }

    private function parseBody() {
        $contentType = $this->getHeader('Content-Type') ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $input = file_get_contents('php://input');
            $decoded = json_decode($input, true);
            $this->body = is_array($decoded) ? $decoded : [];
        } else {
            $this->body = $_POST ?? [];
        }
    }

    public function getMethod(): string {
        return $this->method;
    }

    public function getUri(): string {
        return $this->uri;
    }

    public function getHeader(string $name): ?string {
        foreach ($this->headers as $key => $value) {
            if (strcasecmp($key, $name) === 0) {
                return $value;
            }
        }
        return null;
    }

    public function getBearerToken(): ?string {
        $authHeader = $this->getHeader('Authorization');
        if ($authHeader && preg_match('/Bearer\s+(.+)/i', $authHeader, $matches)) {
            return trim($matches[1]);
        }
        // Soporte alternativo mediante cabecera X-Auth-Token
        return $this->getHeader('X-Auth-Token');
    }

    public function query(string $key, $default = null) {
        return $this->queryParams[$key] ?? $default;
    }

    public function input(string $key, $default = null) {
        return $this->body[$key] ?? $default;
    }

    public function all(): array {
        return $this->body;
    }
}
