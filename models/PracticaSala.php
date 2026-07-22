<?php
require_once __DIR__ . '/../config/database.php';

class PracticaSala {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($includeArchived = true) {
        $sql = "SELECT s.*, u.nombre_completo AS creador_nombre,
                       (SELECT COUNT(*) FROM practicas_rondas r WHERE r.sala_id = s.id) AS total_rondas,
                       (SELECT r2.id FROM practicas_rondas r2
                         WHERE r2.sala_id = s.id AND r2.estado IN ('programada','en_curso')
                         ORDER BY r2.numero DESC LIMIT 1) AS ronda_abierta_id
                FROM practicas_salas s
                LEFT JOIN usuarios u ON u.id = s.created_by";
        if (!$includeArchived) {
            $sql .= " WHERE s.estado_sala <> 'archivada'";
        }
        $sql .= " ORDER BY s.created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM practicas_salas WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create($data) {
        $codigo = $this->generateCodigo();
        $now = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare(
            "INSERT INTO practicas_salas
             (codigo, titulo, descripcion, presupuesto_referencial, variacion_minima, duracion_minutos,
              zona_horaria, estado_sala, created_by, created_at, updated_at)
             VALUES
             (:codigo, :titulo, :descripcion, :presupuesto_referencial, :variacion_minima, :duracion_minutos,
              :zona_horaria, :estado_sala, :created_by, :created_at, :updated_at)"
        );
        $ok = $stmt->execute([
            'codigo' => $codigo,
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'] ?? null,
            'presupuesto_referencial' => $data['presupuesto_referencial'],
            'variacion_minima' => $data['variacion_minima'],
            'duracion_minutos' => $data['duracion_minutos'],
            'zona_horaria' => $data['zona_horaria'] ?? 'America/Guayaquil',
            'estado_sala' => $data['estado_sala'] ?? 'activa',
            'created_by' => $data['created_by'],
            'created_at' => $now,
            'updated_at' => $now
        ]);
        return $ok ? (int)$this->db->lastInsertId() : false;
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare(
            "UPDATE practicas_salas SET
                titulo = :titulo,
                descripcion = :descripcion,
                presupuesto_referencial = :presupuesto_referencial,
                variacion_minima = :variacion_minima,
                duracion_minutos = :duracion_minutos,
                zona_horaria = :zona_horaria,
                estado_sala = :estado_sala,
                updated_at = :updated_at
             WHERE id = :id"
        );
        return $stmt->execute([
            'id' => $id,
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'] ?? null,
            'presupuesto_referencial' => $data['presupuesto_referencial'],
            'variacion_minima' => $data['variacion_minima'],
            'duracion_minutos' => $data['duracion_minutos'],
            'zona_horaria' => $data['zona_horaria'] ?? 'America/Guayaquil',
            'estado_sala' => $data['estado_sala'],
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function setEstado($id, $estado) {
        $stmt = $this->db->prepare(
            "UPDATE practicas_salas SET estado_sala = :estado, updated_at = :updated_at WHERE id = :id"
        );
        return $stmt->execute([
            'id' => $id,
            'estado' => $estado,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    private function generateCodigo() {
        $year = date('Y');
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM practicas_salas WHERE codigo LIKE :prefix"
        );
        $stmt->execute(['prefix' => 'PRACT-' . $year . '-%']);
        $n = ((int)$stmt->fetchColumn()) + 1;
        return sprintf('PRACT-%s-%03d', $year, $n);
    }
}
