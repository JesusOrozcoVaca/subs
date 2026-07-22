<?php
require_once __DIR__ . '/../config/database.php';

class PracticaRonda {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getById($id) {
        $stmt = $this->db->prepare(
            "SELECT r.*, s.titulo AS sala_titulo, s.codigo AS sala_codigo,
                    s.presupuesto_referencial, s.variacion_minima, s.estado_sala,
                    s.descripcion AS sala_descripcion,
                    ug.nombre_completo AS ganador_nombre
             FROM practicas_rondas r
             INNER JOIN practicas_salas s ON s.id = r.sala_id
             LEFT JOIN usuarios ug ON ug.id = r.ganador_usuario_id
             WHERE r.id = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getBySalaId($salaId) {
        $stmt = $this->db->prepare(
            "SELECT r.*,
                    u.nombre_completo AS ganador_nombre
             FROM practicas_rondas r
             LEFT JOIN usuarios u ON u.id = r.ganador_usuario_id
             WHERE r.sala_id = :sala_id
             ORDER BY r.numero DESC"
        );
        $stmt->execute(['sala_id' => $salaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOpenBySalaId($salaId) {
        $stmt = $this->db->prepare(
            "SELECT * FROM practicas_rondas
             WHERE sala_id = :sala_id AND estado IN ('programada','en_curso')
             ORDER BY numero DESC LIMIT 1"
        );
        $stmt->execute(['sala_id' => $salaId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function listJoinableForParticipants() {
        $stmt = $this->db->query(
            "SELECT r.*, s.titulo AS sala_titulo, s.codigo AS sala_codigo,
                    s.presupuesto_referencial, s.variacion_minima, s.descripcion AS sala_descripcion
             FROM practicas_rondas r
             INNER JOIN practicas_salas s ON s.id = r.sala_id
             WHERE s.estado_sala = 'activa'
               AND r.estado IN ('programada','en_curso')
             ORDER BY r.hora_inicio ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNextNumero($salaId) {
        $stmt = $this->db->prepare("SELECT COALESCE(MAX(numero), 0) + 1 FROM practicas_rondas WHERE sala_id = :sala_id");
        $stmt->execute(['sala_id' => $salaId]);
        return (int)$stmt->fetchColumn();
    }

    public function create($data) {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare(
            "INSERT INTO practicas_rondas
             (sala_id, numero, hora_inicio, duracion_minutos, zona_horaria, estado, created_by, created_at, updated_at)
             VALUES
             (:sala_id, :numero, :hora_inicio, :duracion_minutos, :zona_horaria, :estado, :created_by, :created_at, :updated_at)"
        );
        $ok = $stmt->execute([
            'sala_id' => $data['sala_id'],
            'numero' => $data['numero'],
            'hora_inicio' => $data['hora_inicio'],
            'duracion_minutos' => $data['duracion_minutos'],
            'zona_horaria' => $data['zona_horaria'] ?? 'America/Guayaquil',
            'estado' => $data['estado'] ?? 'programada',
            'created_by' => $data['created_by'],
            'created_at' => $now,
            'updated_at' => $now
        ]);
        return $ok ? (int)$this->db->lastInsertId() : false;
    }

    public function updateEstado($id, $estado, $extra = []) {
        $fields = ['estado = :estado', 'updated_at = :updated_at'];
        $params = [
            'id' => $id,
            'estado' => $estado,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        if (array_key_exists('started_at', $extra)) {
            $fields[] = 'started_at = :started_at';
            $params['started_at'] = $extra['started_at'];
        }
        if (array_key_exists('ended_at', $extra)) {
            $fields[] = 'ended_at = :ended_at';
            $params['ended_at'] = $extra['ended_at'];
        }
        if (array_key_exists('ganador_usuario_id', $extra)) {
            $fields[] = 'ganador_usuario_id = :ganador_usuario_id';
            $params['ganador_usuario_id'] = $extra['ganador_usuario_id'];
        }
        if (array_key_exists('ganador_valor', $extra)) {
            $fields[] = 'ganador_valor = :ganador_valor';
            $params['ganador_valor'] = $extra['ganador_valor'];
        }
        $sql = 'UPDATE practicas_rondas SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}
