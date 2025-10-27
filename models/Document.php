<?php
require_once __DIR__ . '/../config/database.php';

class Document {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function uploadDocument($productoId, $usuarioId, $file) {
        // Verificar si ya existe un archivo con el mismo nombre para este usuario y producto
        $checkQuery = "SELECT COUNT(*) as count FROM documentos_producto 
                      WHERE producto_id = :producto_id AND usuario_id = :usuario_id 
                      AND nombre_archivo = :nombre_archivo AND procesado = 0";
        
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->execute([
            'producto_id' => $productoId,
            'usuario_id' => $usuarioId,
            'nombre_archivo' => $file['name']
        ]);
        
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if ($result['count'] > 0) {
            return ['success' => false, 'message' => 'Ya existe un archivo con el mismo nombre. Elimine el archivo existente antes de subir uno nuevo.'];
        }
        
        // Crear directorio si no existe
        $uploadDir = BASE_PATH . '/uploads/offers/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generar nombre único para el archivo
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '_' . time() . '.' . $extension;
        $filePath = $uploadDir . $fileName;

        // Mover archivo
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Guardar en base de datos
            $query = "INSERT INTO documentos_producto (producto_id, usuario_id, nombre_archivo, ruta_archivo, fecha_carga) 
                      VALUES (:producto_id, :usuario_id, :nombre_archivo, :ruta_archivo, CURRENT_TIMESTAMP)";
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                'producto_id' => $productoId,
                'usuario_id' => $usuarioId,
                'nombre_archivo' => $file['name'],
                'ruta_archivo' => 'uploads/offers/' . $fileName
            ]);

            if ($result) {
                return [
                    'success' => true,
                    'file_id' => $this->db->lastInsertId(),
                    'file_path' => 'uploads/offers/' . $fileName
                ];
            } else {
                // Eliminar archivo si falló la inserción
                unlink($filePath);
                return ['success' => false, 'message' => 'Error al guardar en base de datos'];
            }
        } else {
            return ['success' => false, 'message' => 'Error al subir el archivo'];
        }
    }

    public function getUserDocuments($productoId, $usuarioId) {
        $query = "SELECT * FROM documentos_producto 
                  WHERE producto_id = :producto_id AND usuario_id = :usuario_id
                  ORDER BY fecha_carga DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['producto_id' => $productoId, 'usuario_id' => $usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllDocuments($productoId) {
        $query = "SELECT dp.*, u.nombre_completo as nombre_usuario 
                  FROM documentos_producto dp
                  JOIN usuarios u ON dp.usuario_id = u.id
                  WHERE dp.producto_id = :producto_id
                  ORDER BY dp.fecha_carga DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['producto_id' => $productoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteDocument($fileId, $usuarioId) {
        // Obtener información del archivo
        $query = "SELECT * FROM documentos_producto WHERE id = :id AND usuario_id = :usuario_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $fileId, 'usuario_id' => $usuarioId]);
        $document = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$document) {
            return ['success' => false, 'message' => 'Archivo no encontrado'];
        }

        // Eliminar archivo físico
        $fullPath = BASE_PATH . '/' . $document['ruta_archivo'];
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        // Eliminar de base de datos
        $query = "DELETE FROM documentos_producto WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute(['id' => $fileId]);

        return ['success' => $result];
    }

    public function processOffer($productoId, $usuarioId) {
        // Marcar todos los documentos del usuario como procesados
        $query = "UPDATE documentos_producto 
                  SET procesado = 1 
                  WHERE producto_id = :producto_id AND usuario_id = :usuario_id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['producto_id' => $productoId, 'usuario_id' => $usuarioId]);
    }

    public function isOfferProcessed($productoId, $usuarioId) {
        $query = "SELECT COUNT(*) FROM documentos_producto 
                  WHERE producto_id = :producto_id AND usuario_id = :usuario_id AND procesado = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['producto_id' => $productoId, 'usuario_id' => $usuarioId]);
        return $stmt->fetchColumn() > 0;
    }
}
