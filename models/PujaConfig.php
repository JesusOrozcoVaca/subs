<?php
require_once BASE_PATH . '/config/database.php';

class PujaConfig {
    private $db;
    private $timeZoneColumnExists;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->timeZoneColumnExists = null;
    }

    private function timeZoneColumnExists() {
        if ($this->timeZoneColumnExists !== null) {
            return $this->timeZoneColumnExists;
        }

        $stmt = $this->db->query("SHOW COLUMNS FROM configuracion_puja LIKE 'zona_horaria'");
        $this->timeZoneColumnExists = (bool)$stmt->fetch();
        return $this->timeZoneColumnExists;
    }

    public function getByProductId($productoId) {
        $query = "SELECT * FROM configuracion_puja WHERE producto_id = :producto_id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['producto_id' => $productoId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function saveConfig($productoId, $duracionMinutos, $horaInicioUtc, $zonaHoraria) {
        $existing = $this->getByProductId($productoId);
        $useTimeZone = $this->timeZoneColumnExists();

        if ($existing) {
            if ($useTimeZone) {
                $query = "UPDATE configuracion_puja
                          SET duracion_minutos = :duracion_minutos,
                              hora_inicio = :hora_inicio,
                              zona_horaria = :zona_horaria
                          WHERE id = :id";
                $params = [
                    'duracion_minutos' => $duracionMinutos,
                    'hora_inicio' => $horaInicioUtc,
                    'zona_horaria' => $zonaHoraria,
                    'id' => $existing['id']
                ];
            } else {
                $query = "UPDATE configuracion_puja
                          SET duracion_minutos = :duracion_minutos,
                              hora_inicio = :hora_inicio
                          WHERE id = :id";
                $params = [
                    'duracion_minutos' => $duracionMinutos,
                    'hora_inicio' => $horaInicioUtc,
                    'id' => $existing['id']
                ];
            }
        } else {
            if ($useTimeZone) {
                $query = "INSERT INTO configuracion_puja (producto_id, duracion_minutos, hora_inicio, zona_horaria)
                          VALUES (:producto_id, :duracion_minutos, :hora_inicio, :zona_horaria)";
                $params = [
                    'producto_id' => $productoId,
                    'duracion_minutos' => $duracionMinutos,
                    'hora_inicio' => $horaInicioUtc,
                    'zona_horaria' => $zonaHoraria
                ];
            } else {
                $query = "INSERT INTO configuracion_puja (producto_id, duracion_minutos, hora_inicio)
                          VALUES (:producto_id, :duracion_minutos, :hora_inicio)";
                $params = [
                    'producto_id' => $productoId,
                    'duracion_minutos' => $duracionMinutos,
                    'hora_inicio' => $horaInicioUtc
                ];
            }
        }

        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }
}
