<?php
/**
 * Clase Logger
 * Registra errores, advertencias y excepciones del servidor en un archivo privado de logs.
 */

class Logger {
    private static $logFile = __DIR__ . '/../logs/app.log';

    public static function error(string $message, array $context = []) {
        self::write('ERROR', $message, $context);
    }

    public static function info(string $message, array $context = []) {
        self::write('INFO', $message, $context);
    }

    public static function exception(Throwable $e, array $extraContext = []) {
        $context = array_merge([
            'file'  => $e->getFile(),
            'line'  => $e->getLine(),
            'code'  => $e->getCode(),
            'trace' => $e->getTraceAsString()
        ], $extraContext);

        self::write('EXCEPTION', $e->getMessage(), $context);
    }

    private static function write(string $level, string $message, array $context = []) {
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }

        $date = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'N/A';
        $uri = $_SERVER['REQUEST_URI'] ?? 'N/A';

        $contextJson = !empty($context) ? ' | Context: ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $logLine = "[{$date}] [{$level}] [IP: {$ip}] [{$method} {$uri}] {$message}{$contextJson}" . PHP_EOL;

        @file_put_contents(self::$logFile, $logLine, FILE_APPEND);
    }
}
