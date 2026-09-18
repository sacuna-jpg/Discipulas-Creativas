<?php
/**
 * Clase Database (Singleton PDO)
 * Gestiona una única conexión persistente y segura con la base de datos MySQL.
 */

require_once __DIR__ . '/Logger.php';

class Database {
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../config/database.php';

            $dsn = sprintf(
                "%s:host=%s;port=%s;dbname=%s;charset=%s",
                $config['driver'],
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
            } catch (PDOException $e) {
                Logger::exception($e, ['action' => 'database_connection_failure']);
                Response::error('No se pudo establecer conexión con la base de datos.', 'DATABASE_CONNECTION_ERROR');
            }
        }

        return self::$instance;
    }
}
