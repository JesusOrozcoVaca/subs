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

    public function create($productoId, $usuarioId, $tiempoEntrega, $plazoOferta, $ofertaInicialUser, $descripcion) {
        $query = "INSERT INTO ofertas_detalle (producto_id, usuario_id, tiempo_entrega, plazo_oferta, oferta_inicial_user, descripcion, created_at, updated_at)
                  VALUES (:producto_id, :usuario_id, :tiempo_entrega, :plazo_oferta, :oferta_inicial_user, :descripcion, NOW(), NOW())";

        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'producto_id' => $productoId,
            'usuario_id' => $usuarioId,
            'tiempo_entrega' => $tiempoEntrega,
            'plazo_oferta' => $plazoOferta,
            'oferta_inicial_user' => $ofertaInicialUser,
            'descripcion' => $descripcion
        ]);
    }

    public function getByProductAndUser($productoId, $usuarioId) {
        $query = "SELECT producto_id, usuario_id, tiempo_entrega, plazo_oferta, oferta_inicial_user, descripcion, created_at, updated_at
                  FROM ofertas_detalle
                  WHERE producto_id = :producto_id AND usuario_id = :usuario_id";

        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'producto_id' => $productoId,
            'usuario_id' => $usuarioId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getInitialOfferInfo($productoId, $usuarioId) {
        $query = "SELECT od.oferta_inicial_user,
                         oi.created_at AS fecha_oferta_inicial
                  FROM ofertas_detalle od
                  INNER JOIN ofertas_iniciales oi
                          ON oi.producto_id = od.producto_id
                         AND oi.usuario_id = od.usuario_id
                  WHERE od.producto_id = :producto_id
                    AND od.usuario_id = :usuario_id
                  LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'producto_id' => $productoId,
            'usuario_id' => $usuarioId
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}

