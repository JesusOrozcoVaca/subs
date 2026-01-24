<?php
require_once __DIR__ . '/../config/database.php';

class OfferRating {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function hasSubmission($productoId, $usuarioId) {
        $query = "SELECT COUNT(*) FROM ofertas_detalle WHERE producto_id = :producto_id AND usuario_id = :usuario_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'producto_id' => $productoId,
            'usuario_id' => $usuarioId
        ]);

        return $stmt->fetchColumn() > 0;
    }

    public function getProductOfferRatings($productoId) {
        $query = "SELECT u.id AS usuario_id,
                         u.nombre_completo,
                         u.cedula,
                         od.created_at AS fecha_oferta,
                         oc.calificacion,
                         oc.comentario
                  FROM ofertas_detalle od
                  INNER JOIN usuarios u ON u.id = od.usuario_id
                  LEFT JOIN ofertas_calificaciones oc
                    ON oc.producto_id = od.producto_id
                   AND oc.usuario_id = od.usuario_id
                  WHERE od.producto_id = :producto_id
                  ORDER BY u.nombre_completo";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['producto_id' => $productoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserOfferRating($productoId, $usuarioId) {
        $query = "SELECT calificacion, comentario, created_at, updated_at
                  FROM ofertas_calificaciones
                  WHERE producto_id = :producto_id AND usuario_id = :usuario_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'producto_id' => $productoId,
            'usuario_id' => $usuarioId
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function upsertRating($productoId, $usuarioId, $calificacion, $comentario) {
        $query = "INSERT INTO ofertas_calificaciones (producto_id, usuario_id, calificacion, comentario, created_at, updated_at)
                  VALUES (:producto_id, :usuario_id, :calificacion, :comentario, NOW(), NOW())
                  ON DUPLICATE KEY UPDATE calificacion = VALUES(calificacion),
                                          comentario = VALUES(comentario),
                                          updated_at = NOW()";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'producto_id' => $productoId,
            'usuario_id' => $usuarioId,
            'calificacion' => $calificacion,
            'comentario' => $comentario
        ]);
    }
}

