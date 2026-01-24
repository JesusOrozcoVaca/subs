<?php
require_once __DIR__ . '/../config/database.php';

class InitialOfferSubmission {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function exists($productoId, $usuarioId) {
        $query = "SELECT COUNT(*) FROM ofertas_iniciales WHERE producto_id = :producto_id AND usuario_id = :usuario_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'producto_id' => $productoId,
            'usuario_id' => $usuarioId
        ]);

        return $stmt->fetchColumn() > 0;
    }

    public function create($productoId, $usuarioId, $codigo, $createdAt) {
        $query = "INSERT INTO ofertas_iniciales (producto_id, usuario_id, codigo, created_at, updated_at)
                  VALUES (:producto_id, :usuario_id, :codigo, :created_at, :updated_at)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'producto_id' => $productoId,
            'usuario_id' => $usuarioId,
            'codigo' => $codigo,
            'created_at' => $createdAt,
            'updated_at' => $createdAt
        ]);
    }

    public function getByProductAndUser($productoId, $usuarioId) {
        $query = "SELECT producto_id, usuario_id, codigo, created_at, updated_at
                  FROM ofertas_iniciales
                  WHERE producto_id = :producto_id AND usuario_id = :usuario_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'producto_id' => $productoId,
            'usuario_id' => $usuarioId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
