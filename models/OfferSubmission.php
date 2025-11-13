<?php
require_once __DIR__ . '/../config/database.php';

class OfferSubmission {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function exists($productoId, $usuarioId) {
        $query = "SELECT COUNT(*) FROM ofertas_detalle WHERE producto_id = :producto_id AND usuario_id = :usuario_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'producto_id' => $productoId,
            'usuario_id' => $usuarioId
        ]);

        return $stmt->fetchColumn() > 0;
    }

    public function create($productoId, $usuarioId, $tiempoEntrega, $plazoOferta, $descripcion) {
        $query = "INSERT INTO ofertas_detalle (producto_id, usuario_id, tiempo_entrega, plazo_oferta, descripcion, created_at, updated_at)
                  VALUES (:producto_id, :usuario_id, :tiempo_entrega, :plazo_oferta, :descripcion, NOW(), NOW())";

        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'producto_id' => $productoId,
            'usuario_id' => $usuarioId,
            'tiempo_entrega' => $tiempoEntrega,
            'plazo_oferta' => $plazoOferta,
            'descripcion' => $descripcion
        ]);
    }

    public function getByProductAndUser($productoId, $usuarioId) {
        $query = "SELECT producto_id, usuario_id, tiempo_entrega, plazo_oferta, descripcion, created_at, updated_at
                  FROM ofertas_detalle
                  WHERE producto_id = :producto_id AND usuario_id = :usuario_id";

        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'producto_id' => $productoId,
            'usuario_id' => $usuarioId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}

