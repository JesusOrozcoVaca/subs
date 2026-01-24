<?php
require_once __DIR__ . '/../config/database.php';

class Bid {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($bidData) {
        $query = "INSERT INTO pujas (producto_id, usuario_id, valor, fecha_puja_ms) 
                  VALUES (:producto_id, :usuario_id, :valor, :fecha_puja_ms)";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute($bidData);
    }

    public function getProductBids($productId) {
        $query = "SELECT p.*, u.nombre_completo as nombre_usuario 
                  FROM pujas p
                  JOIN usuarios u ON p.usuario_id = u.id
                  WHERE p.producto_id = :productId
                  ORDER BY p.valor ASC, p.fecha_puja_ms ASC, p.fecha_puja ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['productId' => $productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLowestBid($productId) {
        $query = "SELECT MIN(valor) as lowest_bid FROM pujas WHERE producto_id = :productId";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['productId' => $productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['lowest_bid'];
    }

    public function getUserLastBid($productId, $userId) {
        $query = "SELECT valor, fecha_puja, fecha_puja_ms
                  FROM pujas
                  WHERE producto_id = :productId AND usuario_id = :userId
                  ORDER BY fecha_puja_ms DESC, fecha_puja DESC
                  LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'productId' => $productId,
            'userId' => $userId
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getUserBids($productId, $userId) {
        $query = "SELECT valor, fecha_puja, fecha_puja_ms
                  FROM pujas
                  WHERE producto_id = :productId AND usuario_id = :userId
                  ORDER BY fecha_puja_ms DESC, fecha_puja DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'productId' => $productId,
            'userId' => $userId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBidsByProductAndUser($productId, $userId) {
        $query = "SELECT valor, fecha_puja, fecha_puja_ms
                  FROM pujas
                  WHERE producto_id = :productId AND usuario_id = :userId
                  ORDER BY fecha_puja_ms DESC, fecha_puja DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'productId' => $productId,
            'userId' => $userId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isValidBid($productId, $bidValue) {
        $product = $this->getProductDetails($productId);
        $lowestBid = $this->getLowestBid($productId);
        
        if ($lowestBid === null) {
            // Primera oferta, debe ser menor que el presupuesto referencial
            return $bidValue < $product['presupuesto_referencial'];
        }

        $minVariation = $lowestBid * ($product['variacion_minima'] / 100);
        return $bidValue <= ($lowestBid - $minVariation);
    }

    private function getProductDetails($productId) {
        $query = "SELECT presupuesto_referencial, variacion_minima FROM productos WHERE id = :productId";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['productId' => $productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}