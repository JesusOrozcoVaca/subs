<?php
require_once __DIR__ . '/../config/database.php';

class ConvalidationDocument {
    private $db;

    public function __construct($db = null) {
        $this->db = $db ?: Database::getInstance()->getConnection();
    }

    public function uploadDocument($convalidationId, $file, $createdAt) {
        $uploadDir = BASE_PATH . '/uploads/Convalidacion_files/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('conv_', true) . '_' . time() . '.' . $extension;
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            $query = "INSERT INTO convalidacion_archivos (convalidacion_id, nombre_archivo, ruta_archivo, fecha_carga)
                      VALUES (:convalidacion_id, :nombre_archivo, :ruta_archivo, :fecha_carga)";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                'convalidacion_id' => $convalidationId,
                'nombre_archivo' => $file['name'],
                'ruta_archivo' => 'uploads/Convalidacion_files/' . $fileName,
                'fecha_carga' => $createdAt
            ]);

            if ($result) {
                return [
                    'success' => true,
                    'file_id' => (int)$this->db->lastInsertId(),
                    'file_path' => 'uploads/Convalidacion_files/' . $fileName,
                    'full_path' => $filePath
                ];
            }

            unlink($filePath);
            return ['success' => false, 'message' => 'Error al guardar el archivo en base de datos'];
        }

        return ['success' => false, 'message' => 'Error al subir el archivo'];
    }

    public function getFilesByConvalidation($convalidationId) {
        $query = "SELECT id, convalidacion_id, nombre_archivo, ruta_archivo, fecha_carga
                  FROM convalidacion_archivos
                  WHERE convalidacion_id = :convalidacion_id
                  ORDER BY fecha_carga DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['convalidacion_id' => $convalidationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

