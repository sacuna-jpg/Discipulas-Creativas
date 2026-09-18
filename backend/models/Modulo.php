<?php
/**
 * Modelo Modulo
 */

require_once __DIR__ . '/../core/Database.php';

class Modulo {
    public static function allWithPreguntas(): array {
        $db = Database::getConnection();

        // 1. Obtener todos los módulos activos
        $stmt = $db->query("
            SELECT id, numero, titulo, descripcion, youtube_id, duracion
            FROM modulos
            WHERE activo = 1
            ORDER BY numero ASC
        ");
        $modulos = $stmt->fetchAll();

        if (empty($modulos)) {
            return [];
        }

        // 2. Obtener todas las preguntas asociadas
        $stmtPreguntas = $db->query("
            SELECT id, modulo_id, orden, enunciado
            FROM preguntas
            ORDER BY modulo_id ASC, orden ASC
        ");
        $preguntas = $stmtPreguntas->fetchAll();

        // Agrupar preguntas por modulo_id
        $preguntasPorModulo = [];
        foreach ($preguntas as $p) {
            $preguntasPorModulo[$p['modulo_id']][] = [
                'id'        => (int) $p['id'],
                'orden'     => (int) $p['orden'],
                'enunciado' => $p['enunciado']
            ];
        }

        // Adjuntar preguntas a cada módulo
        foreach ($modulos as &$mod) {
            $modId = $mod['id'];
            $mod['numero'] = (int) $mod['numero'];
            $mod['preguntas'] = $preguntasPorModulo[$modId] ?? [];
        }

        return $modulos;
    }

    public static function findById(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM modulos WHERE id = ? AND activo = 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
