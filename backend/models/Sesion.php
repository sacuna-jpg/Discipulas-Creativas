<?php
/**
 * Modelo Sesion
 * Manejo de tokens de autenticación para la API REST.
 */

require_once __DIR__ . '/../core/Database.php';

class Sesion {
    public static function create(int $usuarioId, int $daysValid = 30): string {
        $db = Database::getConnection();
        $token = bin2hex(random_bytes(32)); // Token criptográfico de 64 caracteres
        $expiraAt = date('Y-m-d H:i:s', strtotime("+{$daysValid} days"));
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        $stmt = $db->prepare("
            INSERT INTO sesiones (usuario_id, token, ip_address, user_agent, expira_at)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$usuarioId, $token, $ip, $userAgent, $expiraAt]);

        return $token;
    }

    public static function findValidUserByToken(string $token): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT s.id AS sesion_id, s.token, s.expira_at,
                   u.id, u.nombre, u.correo, u.distrito_id, u.iglesia_id,
                   d.nombre AS distrito_nombre,
                   i.nombre AS iglesia_nombre
            FROM sesiones s
            INNER JOIN usuarios u ON s.usuario_id = u.id
            LEFT JOIN distritos d ON u.distrito_id = d.id
            LEFT JOIN iglesias i ON u.iglesia_id = i.id
            WHERE s.token = ? AND s.expira_at > NOW()
        ");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if ($user) {
            // Actualizar último acceso
            $update = $db->prepare("UPDATE sesiones SET ultimo_acceso = NOW() WHERE token = ?");
            $update->execute([$token]);
            return $user;
        }

        return null;
    }

    public static function revoke(string $token): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM sesiones WHERE token = ?");
        return $stmt->execute([$token]);
    }

    public static function cleanExpired(): void {
        $db = Database::getConnection();
        $db->exec("DELETE FROM sesiones WHERE expira_at <= NOW()");
    }
}
