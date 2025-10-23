<?php
require_once BASE_PATH . '/config/database.php';

class Product {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllProducts() {
        $query = "SELECT p.*, ep.descripcion as estado_descripcion 
                  FROM productos p 
                  LEFT JOIN estados_producto ep ON p.estado_id = ep.id";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllActive() {
        // Primero, verificar qué estados tienen los productos
        $checkQuery = "SELECT p.id, p.estado_id, ep.descripcion, ep.codigo 
                       FROM productos p 
                       LEFT JOIN estados_producto ep ON p.estado_id = ep.id 
                       ORDER BY p.id";
        error_log("=== CHECKING ALL PRODUCTS AND THEIR STATES ===");
        $checkStmt = $this->db->query($checkQuery);
        $allProducts = $checkStmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Total products in database: " . count($allProducts));
        foreach ($allProducts as $product) {
            error_log("Product ID: " . $product['id'] . ", Estado ID: " . $product['estado_id'] . ", Estado: " . $product['descripcion'] . ", Codigo: " . $product['codigo']);
        }
        
        // Cambiar la consulta para mostrar todos los productos, no solo los que no son 'fin'
        $query = "SELECT p.*, ep.descripcion as estado_descripcion, ep.codigo as estado_codigo
                  FROM productos p 
                  LEFT JOIN estados_producto ep ON p.estado_id = ep.id
                  ORDER BY p.id";
        error_log("=== GET ALL ACTIVE PRODUCTS (MODIFIED) ===");
        error_log("SQL Query: " . $query);
        
        $stmt = $this->db->query($query);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Products found: " . count($products));
        foreach ($products as $product) {
            error_log("Product ID: " . $product['id'] . ", Estado: " . $product['estado_descripcion'] . ", Codigo: " . $product['estado_codigo']);
        }
        
        return $products;
    }

    public function getProductById($id) {
        $query = "SELECT p.*, ep.descripcion as estado_descripcion 
                  FROM productos p 
                  LEFT JOIN estados_producto ep ON p.estado_id = ep.id
                  WHERE p.id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createProduct($data) {
        $query = "INSERT INTO productos (entidad, objeto_proceso, cpc_id, codigo, tipo_compra, presupuesto_referencial, 
                  tipo_contratacion, forma_pago, plazo_entrega, vigencia_oferta, funcionario_encargado, descripcion, variacion_minima, estado_id) 
                  VALUES (:entidad, :objeto_proceso, :cpc_id, :codigo, :tipo_compra, :presupuesto_referencial, 
                  :tipo_contratacion, :forma_pago, :plazo_entrega, :vigencia_oferta, :funcionario_encargado, :descripcion, :variacion_minima, :estado_id)";
        
        $data['codigo'] = $this->generateProductCode($data);
        $data['estado_id'] = 1; // Preguntas y Respuestas por defecto
        
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
                  variacion_minima = :variacion_minima,
                  estado_id = :estado_id
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    public function updateProductStatus($id, $estadoId) {
        error_log("=== UPDATE PRODUCT STATUS ===");
        error_log("Product ID: " . $id);
        error_log("Estado ID: " . $estadoId);
        
        try {
            // Verificar si la columna estado_id existe
            $checkQuery = "SHOW COLUMNS FROM productos LIKE 'estado_id'";
            $checkStmt = $this->db->query($checkQuery);
            $columnExists = $checkStmt->fetch();
            
            if (!$columnExists) {
                error_log("ERROR: Column 'estado_id' does not exist in 'productos' table");
                return false;
            }
            
            $query = "UPDATE productos SET estado_id = :estado_id WHERE id = :id";
            error_log("SQL Query: " . $query);
            
            $stmt = $this->db->prepare($query);
            if (!$stmt) {
                error_log("Error preparing statement: " . print_r($this->db->errorInfo(), true));
                return false;
            }
            
            $params = ['id' => $id, 'estado_id' => $estadoId];
            error_log("SQL Parameters: " . print_r($params, true));
            
            $result = $stmt->execute($params);
            
            if (!$result) {
                error_log("Error executing statement: " . print_r($stmt->errorInfo(), true));
            }
            
            error_log("Update result: " . ($result ? 'SUCCESS' : 'FAILED'));
            error_log("Rows affected: " . $stmt->rowCount());
            
            // Verificar que solo se actualizó un producto
            if ($stmt->rowCount() > 1) {
                error_log("WARNING: Updated more than 1 product! Rows affected: " . $stmt->rowCount());
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Exception in updateProductStatus: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
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
        error_log("=== GETPARTICIPANTPRODUCTS START ===");
        error_log("User ID: " . $userId);
        
        $query = "SELECT DISTINCT p.*, ep.descripcion as estado_descripcion 
                  FROM productos p
                  LEFT JOIN estados_producto ep ON p.estado_id = ep.id
                  JOIN cpc c ON p.cpc_id = c.id
                  JOIN usuarios_cpc uc ON c.id = uc.cpc_id
                  WHERE uc.usuario_id = :userId
                  ORDER BY p.fecha_creacion DESC";
        
        error_log("Query: " . $query);
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute(['userId' => $userId]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Query result: " . print_r($result, true));
            error_log("=== GETPARTICIPANTPRODUCTS COMPLETED SUCCESSFULLY ===");
            return $result;
        } catch (Exception $e) {
            error_log("Error in getParticipantProducts: " . $e->getMessage());
            throw $e;
        }
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

    public function updatePhase($productId, $estadoId) {
        $query = "UPDATE productos SET estado_id = :estado_id WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'estado_id' => $estadoId,
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