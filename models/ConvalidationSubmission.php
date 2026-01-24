<?php
require_once __DIR__ . '/../config/database.php';

class ConvalidationSubmission {
    private $db;

    public function __construct($db = null) {
        $this->db = $db ?: Database::getInstance()->getConnection();
    }

    public function exists($productoId, $usuarioId) {
        $query = "SELECT COUNT(*) FROM convalidaciones WHERE producto_id = :producto_id AND usuario_id = :usuario_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'producto_id' => $productoId,
            'usuario_id' => $usuarioId
        ]);

        return $stmt->fetchColumn() > 0;
    }

    public function create($productoId, $usuarioId, $detalleTexto, $createdAt) {
        $query = "INSERT INTO convalidaciones (producto_id, usuario_id, detalle_texto, created_at, updated_at)
                  VALUES (:producto_id, :usuario_id, :detalle_texto, :created_at, :updated_at)";

        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            'producto_id' => $productoId,
            'usuario_id' => $usuarioId,
            'detalle_texto' => $detalleTexto,
            'created_at' => $createdAt,
            'updated_at' => $createdAt
        ]);

        return $result ? (int)$this->db->lastInsertId() : null;
    }

    public function getByProductAndUser($productoId, $usuarioId) {
        $query = "SELECT id, producto_id, usuario_id, detalle_texto, created_at, updated_at
                  FROM convalidaciones
                  WHERE producto_id = :producto_id AND usuario_id = :usuario_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'producto_id' => $productoId,
            'usuario_id' => $usuarioId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}

