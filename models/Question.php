<?php
require_once __DIR__ . '/../config/database.php';

class Question {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($questionData) {
        $query = "INSERT INTO preguntas_respuestas (producto_id, usuario_id, pregunta) 
                  VALUES (:producto_id, :usuario_id, :pregunta)";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute($questionData);
    }

    public function answer($id, $respuesta) {
        $query = "UPDATE preguntas_respuestas SET respuesta = :respuesta, fecha_respuesta = CURRENT_TIMESTAMP 
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $id, 'respuesta' => $respuesta]);
    }

    public function getProductQuestions($productId) {
        $query = "SELECT pr.*, u.nombre_completo as nombre_usuario 
                  FROM preguntas_respuestas pr
                  JOIN usuarios u ON pr.usuario_id = u.id
                  WHERE pr.producto_id = :productId
                  ORDER BY pr.fecha_pregunta DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['productId' => $productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}