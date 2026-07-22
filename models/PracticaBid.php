<?php
require_once __DIR__ . '/../config/database.php';

class PracticaBid {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($bidData) {
        $stmt = $this->db->prepare(
            "INSERT INTO practicas_pujas (ronda_id, usuario_id, valor, fecha_puja_ms)
             VALUES (:ronda_id, :usuario_id, :valor, :fecha_puja_ms)"
        );
        return $stmt->execute($bidData);
    }

    public function getLowestBid($rondaId) {
        $stmt = $this->db->prepare("SELECT MIN(valor) AS lowest_bid FROM practicas_pujas WHERE ronda_id = :ronda_id");
        $stmt->execute(['ronda_id' => $rondaId]);
        $val = $stmt->fetch(PDO::FETCH_ASSOC)['lowest_bid'] ?? null;
        return $val !== null ? $val : null;
    }

    public function getUserLastBid($rondaId, $userId) {
        $stmt = $this->db->prepare(
            "SELECT valor, fecha_puja_ms, created_at AS fecha_puja
             FROM practicas_pujas
             WHERE ronda_id = :ronda_id AND usuario_id = :usuario_id
             ORDER BY fecha_puja_ms DESC
             LIMIT 1"
        );
        $stmt->execute(['ronda_id' => $rondaId, 'usuario_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getUserBids($rondaId, $userId) {
        $stmt = $this->db->prepare(
            "SELECT valor, fecha_puja_ms, created_at AS fecha_puja
             FROM practicas_pujas
             WHERE ronda_id = :ronda_id AND usuario_id = :usuario_id
             ORDER BY fecha_puja_ms DESC"
        );
        $stmt->execute(['ronda_id' => $rondaId, 'usuario_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRoundBids($rondaId) {
        $stmt = $this->db->prepare(
            "SELECT p.*, u.nombre_completo AS nombre_usuario
             FROM practicas_pujas p
             JOIN usuarios u ON u.id = p.usuario_id
             WHERE p.ronda_id = :ronda_id
             ORDER BY p.valor ASC, p.fecha_puja_ms ASC"
        );
        $stmt->execute(['ronda_id' => $rondaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
