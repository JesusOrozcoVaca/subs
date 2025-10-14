<?php
require_once BASE_PATH . '/config/database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllUsers() {
        $query = "SELECT * FROM usuarios";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserById($id) {
        $query = "SELECT * FROM usuarios WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createUser($data) {
        $query = "INSERT INTO usuarios (cedula, nombre_completo, correo_electronico, telefono, contrasena, nivel_acceso) 
                  VALUES (:cedula, :nombre_completo, :correo_electronico, :telefono, :contrasena, :nivel_acceso)";
        
        $stmt = $this->db->prepare($query);
        $data['contrasena'] = password_hash($data['contrasena'], PASSWORD_DEFAULT);
        return $stmt->execute($data);
    }

    public function updateUser($id, $data) {
        $query = "UPDATE usuarios SET 
                  nombre_completo = :nombre_completo, 
                  correo_electronico = :correo_electronico, 
                  telefono = :telefono 
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $params = [
            'id' => $id,
            'nombre_completo' => $data['nombre_completo'],
            'correo_electronico' => $data['correo_electronico'],
            'telefono' => $data['telefono']
        ];
        return $stmt->execute($params);
    }

    public function getUserCPCs($userId) {
        $query = "SELECT c.* FROM cpc c
                  JOIN usuarios_cpc uc ON c.id = uc.cpc_id
                  WHERE uc.usuario_id = :userId";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['userId' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addCPC($userId, $cpcId) {
        $query = "INSERT INTO usuarios_cpc (usuario_id, cpc_id) VALUES (:userId, :cpcId)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['userId' => $userId, 'cpcId' => $cpcId]);
    }

    public function removeCPC($userId, $cpcId) {
        $query = "DELETE FROM usuarios_cpc WHERE usuario_id = :userId AND cpc_id = :cpcId";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['userId' => $userId, 'cpcId' => $cpcId]);
    }

    public function deactivateUser($id) {
        $query = "UPDATE usuarios SET estado = 'inactivo' WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $id]);
    }

    public function activateUser($id) {
        $query = "UPDATE usuarios SET estado = 'activo' WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $id]);
    }

    public function authenticate($username, $password) {
        $query = "SELECT * FROM usuarios WHERE correo_electronico = :username OR cedula = :username";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['contrasena'])) {
            return $user;
        }

        return false;
    }

    public function deleteUser($id) {
        $query = "DELETE FROM usuarios WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $id]);
    }
}