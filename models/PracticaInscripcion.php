<?php
require_once __DIR__ . '/../config/database.php';

class PracticaInscripcion {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByRondaAndUser($rondaId, $usuarioId) {
        $stmt = $this->db->prepare(
            "SELECT * FROM practicas_inscripciones
             WHERE ronda_id = :ronda_id AND usuario_id = :usuario_id LIMIT 1"
        );
        $stmt->execute(['ronda_id' => $rondaId, 'usuario_id' => $usuarioId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getActiveByRondaAndUser($rondaId, $usuarioId) {
        $row = $this->getByRondaAndUser($rondaId, $usuarioId);
        if ($row && (int)$row['activo'] === 1) {
            return $row;
        }
        return null;
    }

    public function listByRonda($rondaId) {
        $stmt = $this->db->prepare(
            "SELECT i.*, u.nombre_completo, u.correo_electronico,
                    COALESCE(u.es_bot, 0) AS es_bot
             FROM practicas_inscripciones i
             INNER JOIN usuarios u ON u.id = i.usuario_id
             WHERE i.ronda_id = :ronda_id
             ORDER BY u.es_bot ASC, u.nombre_completo"
        );
        $stmt->execute(['ronda_id' => $rondaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listActiveByRonda($rondaId) {
        $stmt = $this->db->prepare(
            "SELECT i.*, u.nombre_completo, COALESCE(u.es_bot, 0) AS es_bot
             FROM practicas_inscripciones i
             INNER JOIN usuarios u ON u.id = i.usuario_id
             WHERE i.ronda_id = :ronda_id AND i.activo = 1
             ORDER BY u.es_bot ASC, u.nombre_completo"
        );
        $stmt->execute(['ronda_id' => $rondaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function join($rondaId, $usuarioId, $ofertaInicial) {
        $existing = $this->getByRondaAndUser($rondaId, $usuarioId);
        if ($existing) {
            if ((int)$existing['activo'] !== 1) {
                return ['success' => false, 'message' => 'Su inscripción fue desactivada por el administrador.'];
            }
            return ['success' => true, 'inscription' => $existing, 'already' => true];
        }

        $stmt = $this->db->prepare(
            "INSERT INTO practicas_inscripciones (ronda_id, usuario_id, oferta_inicial, activo, joined_at)
             VALUES (:ronda_id, :usuario_id, :oferta_inicial, 1, :joined_at)"
        );
        $ok = $stmt->execute([
            'ronda_id' => $rondaId,
            'usuario_id' => $usuarioId,
            'oferta_inicial' => $ofertaInicial,
            'joined_at' => date('Y-m-d H:i:s')
        ]);
        if (!$ok) {
            return ['success' => false, 'message' => 'No se pudo registrar la inscripción.'];
        }
        return [
            'success' => true,
            'inscription' => $this->getByRondaAndUser($rondaId, $usuarioId),
            'already' => false
        ];
    }

    public function setActivo($id, $activo) {
        $stmt = $this->db->prepare("UPDATE practicas_inscripciones SET activo = :activo WHERE id = :id");
        return $stmt->execute(['id' => $id, 'activo' => $activo ? 1 : 0]);
    }
}
