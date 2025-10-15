<?php
require_once BASE_PATH . '/config/database.php';

class Product {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllProducts() {
        $query = "SELECT * FROM productos";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllActive() {
        $query = "SELECT * FROM productos WHERE estado_proceso != 'Finalizado'";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductById($id) {
        $query = "SELECT * FROM productos WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createProduct($data) {
        $query = "INSERT INTO productos (entidad, objeto_proceso, cpc_id, codigo, tipo_compra, presupuesto_referencial, 
                  tipo_contratacion, forma_pago, plazo_entrega, vigencia_oferta, funcionario_encargado, descripcion, variacion_minima, estado_proceso) 
                  VALUES (:entidad, :objeto_proceso, :cpc_id, :codigo, :tipo_compra, :presupuesto_referencial, 
                  :tipo_contratacion, :forma_pago, :plazo_entrega, :vigencia_oferta, :funcionario_encargado, :descripcion, :variacion_minima, 'Preguntas y Respuestas')";
        
        $data['codigo'] = $this->generateProductCode($data);
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute($data);
    }

    public function updateProduct($id, $data) {
        $query = "UPDATE productos SET 
                  entidad = :entidad, 
                  objeto_proceso = :objeto_proceso, 
                  cpc_id = :cpc_id, 
                  tipo_compra = :tipo_compra, 
                  presupuesto_referencial = :presupuesto_referencial, 
                  tipo_contratacion = :tipo_contratacion, 
                  forma_pago = :forma_pago, 
                  plazo_entrega = :plazo_entrega, 
                  vigencia_oferta = :vigencia_oferta, 
                  funcionario_encargado = :funcionario_encargado, 
                  descripcion = :descripcion, 
                  variacion_minima = :variacion_minima 
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    public function updateProductStatus($id, $status) {
        $query = "UPDATE productos SET estado_proceso = :estado WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $id, 'estado' => $status]);
    }

    public function deleteProduct($id) {
        $query = "DELETE FROM productos WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $id]);
    }

    public function getParticipants($id) {
        $query = "SELECT u.id, u.nombre_completo, pp.estado
                  FROM usuarios u
                  JOIN participantes_producto pp ON u.id = pp.usuario_id
                  WHERE pp.producto_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateParticipantStatus($productId, $participantId, $status) {
        $query = "UPDATE participantes_producto 
                  SET estado = :status 
                  WHERE producto_id = :productId AND usuario_id = :participantId";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'status' => $status,
            'productId' => $productId,
            'participantId' => $participantId
        ]);
    }

    public function getParticipantProducts($userId) {
        $query = "SELECT DISTINCT p.* 
                  FROM productos p
                  JOIN cpc c ON p.cpc_id = c.id
                  JOIN usuarios_cpc uc ON c.id = uc.cpc_id
                  WHERE uc.usuario_id = :userId
                  ORDER BY p.fecha_creacion DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute(['userId' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getParticipantStatus($productId, $userId) {
        // Primero, obtenemos el CPC del producto
        $query = "SELECT cpc_id FROM productos WHERE id = :productId";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['productId' => $productId]);
        $productCpc = $stmt->fetchColumn();
    
        // Luego, verificamos si el usuario tiene este CPC
        $query = "SELECT COUNT(*) FROM usuarios_cpc 
                  WHERE usuario_id = :userId AND cpc_id = :cpcId";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['userId' => $userId, 'cpcId' => $productCpc]);
        $hasMatchingCpc = $stmt->fetchColumn() > 0;
    
        if ($hasMatchingCpc) {
            return "Registrado en el proceso";
        } else {
            return "No se encuentra registrado en este proceso. Agregue el CPC para poder participar.";
        }
    }

    public function calculateDates($productId) {
        $product = $this->getProductById($productId);
        $fechaPublicacion = new DateTime($product['fecha_creacion']);
        
        $dates = [
            'Fecha de publicación' => $fechaPublicacion,
            'Fecha límite de Preguntas' => clone $fechaPublicacion,
            'Fecha límite de Respuestas' => clone $fechaPublicacion,
            'Fecha límite entrega Ofertas' => clone $fechaPublicacion,
            'Fecha límite solicitar Convalidación' => clone $fechaPublicacion,
            'Fecha límite respuesta Convalidación' => clone $fechaPublicacion,
            'Fecha límite de Calificación' => clone $fechaPublicacion,
            'Fecha Inicio de Puja' => clone $fechaPublicacion,
            'Fecha Final de Puja' => clone $fechaPublicacion,
            'Fecha estimada de Adjudicación' => clone $fechaPublicacion
        ];

        $interval = new DateInterval('P3D');

        foreach ($dates as $key => &$date) {
            if ($key !== 'Fecha de publicación') {
                $date->add($interval);
            }
        }

        return $dates;
    }

    public function getProductByCode($code) {
        $query = "SELECT * FROM productos WHERE codigo = :code";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['code' => $code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getProductDocuments($productId) {
        $query = "SELECT * FROM documentos_producto WHERE producto_id = :productId";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['productId' => $productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function generateProductCode($data) {
        $tipoCompra = $data['tipo_compra'];
        $entidadInitials = $this->getInitials($data['entidad']);
        $year = date('Y');
        $count = $this->getProductCount() + 1;
        
        return sprintf("%s-%s-%s-%03d", $tipoCompra, $entidadInitials, $year, $count);
    }

    private function getInitials($string) {
        $words = explode(' ', $string);
        return implode('', array_map(function($word) { return strtoupper($word[0]); }, $words));
    }

    private function getProductCount() {
        $query = "SELECT COUNT(*) FROM productos";
        $stmt = $this->db->query($query);
        return $stmt->fetchColumn();
    }

    public function updatePhase($productId, $newPhase) {
        $query = "UPDATE productos SET estado_proceso = :estado WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'estado' => $newPhase,
            'id' => $productId
        ]);
    }

    public function getProductsByCpcId($cpcId) {
        $query = "SELECT * FROM productos WHERE cpc_id = :cpc_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['cpc_id' => $cpcId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}