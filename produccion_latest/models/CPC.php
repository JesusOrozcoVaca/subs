<?php
require_once BASE_PATH . '/config/database.php';

class CPC {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllCPCs() {
        $query = "SELECT * FROM cpc";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCPCById($id) {
        $query = "SELECT * FROM cpc WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createCPC($data) {
        $query = "INSERT INTO cpc (codigo, descripcion) VALUES (:codigo, :descripcion)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'codigo' => $data['codigo'],
            'descripcion' => $data['descripcion']
        ]);
    }

    public function updateCPC($id, $data) {
        $query = "UPDATE cpc SET codigo = :codigo, descripcion = :descripcion WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    public function deleteCPC($id) {
        $query = "DELETE FROM cpc WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $id]);
    }

    public function getCPCByCode($code) {
        $query = "SELECT * FROM cpc WHERE codigo = :codigo";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['codigo' => $code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUnassignedCPCs($userId) {
        $query = "SELECT c.* FROM cpc c
                  WHERE c.id NOT IN (
                      SELECT cpc_id FROM usuarios_cpc WHERE usuario_id = :userId
                  )";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['userId' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function assignCPCToUser($userId, $cpcId) {
        $query = "INSERT INTO usuarios_cpc (usuario_id, cpc_id) VALUES (:userId, :cpcId)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['userId' => $userId, 'cpcId' => $cpcId]);
    }

    public function unassignCPCFromUser($userId, $cpcId) {
        $query = "DELETE FROM usuarios_cpc WHERE usuario_id = :userId AND cpc_id = :cpcId";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['userId' => $userId, 'cpcId' => $cpcId]);
    }
}