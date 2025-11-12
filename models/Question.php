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

    public function getProductQuestionsPaginated($productId, $page = 1, $limit = 5) {
        $offset = ($page - 1) * $limit;
        
        // Obtener total de preguntas
        $countQuery = "SELECT COUNT(*) FROM preguntas_respuestas WHERE producto_id = :productId";
        $countStmt = $this->db->prepare($countQuery);
        $countStmt->execute(['productId' => $productId]);
        $totalQuestions = $countStmt->fetchColumn();
        
        // Obtener preguntas paginadas
        $query = "SELECT pr.*, u.nombre_completo as nombre_usuario 
                  FROM preguntas_respuestas pr
                  JOIN usuarios u ON pr.usuario_id = u.id
                  WHERE pr.producto_id = :productId
                  ORDER BY pr.fecha_pregunta DESC
                  LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':productId', $productId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'questions' => $questions,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => ceil($totalQuestions / $limit),
                'totalQuestions' => $totalQuestions,
                'itemsPerPage' => $limit
            ]
        ];
    }

    public function getUnansweredQuestions($productId) {
        $query = "SELECT pr.*, u.nombre_completo as nombre_usuario 
                  FROM preguntas_respuestas pr
                  JOIN usuarios u ON pr.usuario_id = u.id
                  WHERE pr.producto_id = :productId AND pr.respuesta IS NULL
                  ORDER BY pr.fecha_pregunta ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['productId' => $productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getQuestionById($questionId) {
        $query = "SELECT pr.*, u.nombre_completo as nombre_usuario 
                  FROM preguntas_respuestas pr
                  LEFT JOIN usuarios u ON pr.usuario_id = u.id
                  WHERE pr.id = :questionId LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['questionId' => $questionId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllQuestions($productId) {
        $query = "SELECT pr.*, u.nombre_completo as nombre_usuario 
                  FROM preguntas_respuestas pr
                  JOIN usuarios u ON pr.usuario_id = u.id
                  WHERE pr.producto_id = :productId
                  ORDER BY pr.fecha_pregunta ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['productId' => $productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function answerMultiple($answers) {
        error_log("=== ANSWER MULTIPLE START ===");
        error_log("Answers received: " . print_r($answers, true));
        
        $this->db->beginTransaction();
        
        try {
            foreach ($answers as $questionId => $respuesta) {
                error_log("Processing question ID: $questionId, answer: '$respuesta'");
                if (!empty($respuesta)) {
                    $query = "UPDATE preguntas_respuestas SET respuesta = :respuesta, fecha_respuesta = CURRENT_TIMESTAMP 
                              WHERE id = :id";
                    $stmt = $this->db->prepare($query);
                    $result = $stmt->execute(['id' => $questionId, 'respuesta' => $respuesta]);
                    error_log("Update result for question $questionId: " . ($result ? 'SUCCESS' : 'FAILED'));
                    error_log("Rows affected: " . $stmt->rowCount());
                }
            }
            
            $this->db->commit();
            error_log("=== ANSWER MULTIPLE SUCCESS ===");
            return true;
        } catch (Exception $e) {
            error_log("=== ANSWER MULTIPLE ERROR ===");
            error_log("Error: " . $e->getMessage());
            $this->db->rollBack();
            return false;
        }
    }
}