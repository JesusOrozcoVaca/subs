<?php
require_once BASE_PATH . '/config/database.php';

class ProductState {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllStates() {
        $query = "SELECT * FROM estados_producto WHERE activo = 1 ORDER BY orden ASC";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStateById($id) {
        $query = "SELECT * FROM estados_producto WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getStateByCode($code) {
        $query = "SELECT * FROM estados_producto WHERE codigo = :code";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['code' => $code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createState($data) {
        $query = "INSERT INTO estados_producto (codigo, descripcion, orden) VALUES (:codigo, :descripcion, :orden)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute($data);
    }

    public function updateState($id, $data) {
        $query = "UPDATE estados_producto SET codigo = :codigo, descripcion = :descripcion, orden = :orden WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    public function deleteState($id) {
        $query = "UPDATE estados_producto SET activo = 0 WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute(['id' => $id]);
    }

    public function getStatesForSelect() {
        $states = $this->getAllStates();
        $options = [];
        foreach ($states as $state) {
            $options[$state['codigo']] = $state['descripcion'];
        }
        return $options;
    }

    public function getStateIdByCode($code) {
        $query = "SELECT id FROM estados_producto WHERE codigo = :code";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['code' => $code]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['id'] : null;
    }

    public function getStateCodeById($id) {
        $query = "SELECT codigo FROM estados_producto WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['codigo'] : 'pyr';
    }
}

