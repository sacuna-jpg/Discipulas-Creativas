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
            $this->headers = [];
        }

        // En servidores proxy/FastCGI como InfinityFree/OpenResty, capturar CONTENT_TYPE y HTTP_AUTHORIZATION
        if (isset($_SERVER['CONTENT_TYPE']) && !isset($this->headers['Content-Type'])) {
            $this->headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
        }
        if (isset($_SERVER['CONTENT_LENGTH']) && !isset($this->headers['Content-Length'])) {
            $this->headers['Content-Length'] = $_SERVER['CONTENT_LENGTH'];
        }
        if (isset($_SERVER['HTTP_AUTHORIZATION']) && !isset($this->headers['Authorization'])) {
            $this->headers['Authorization'] = $_SERVER['HTTP_AUTHORIZATION'];
        }

        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                if (!isset($this->headers[$header])) {
                    $this->headers[$header] = $value;
                }
            }
        }
    }

    private function parseBody() {
        $contentType = $this->getHeader('Content-Type') ?? ($_SERVER['CONTENT_TYPE'] ?? '');
        $rawInput = file_get_contents('php://input');
        $trimmed = trim($rawInput ?: '');

        // Detectar JSON tanto por cabecera como por sintaxis (primer caracter { o [)
        if (
            stripos($contentType, 'application/json') !== false ||
            (strlen($trimmed) > 0 && ($trimmed[0] === '{' || $trimmed[0] === '['))
        ) {
            $decoded = json_decode($trimmed, true);
            $this->body = is_array($decoded) ? $decoded : [];
        } else {
            $this->body = !empty($_POST) ? $_POST : [];
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
        // 1. Cabecera estándar Authorization
        $authHeader = $this->getHeader('Authorization') 
            ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? null) 
            ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null);

        if ($authHeader && preg_match('/Bearer\s+(.+)/i', $authHeader, $matches)) {
            return trim($matches[1]);
        }

        // 2. Cabecera personalizada X-Auth-Token (inmune al filtrado de Apache/FastCGI)
        $xAuth = $this->getHeader('X-Auth-Token') 
            ?? ($_SERVER['HTTP_X_AUTH_TOKEN'] ?? null);
        if (!empty($xAuth)) {
            return trim($xAuth);
        }

        // 3. Fallback en parámetro query (?token=...)
        $queryToken = $this->query('token');
        if (!empty($queryToken)) {
            return trim($queryToken);
        }

        return null;
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
