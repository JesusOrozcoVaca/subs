<?php
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/utils/logger.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllUsers($includeBots = false) {
        $query = "SELECT * FROM usuarios";
        if (!$includeBots) {
            $query .= " WHERE COALESCE(es_bot, 0) = 0";
        }
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUsersPaginated($limit, $offset, $includeBots = false) {
        $query = "SELECT * FROM usuarios";
        if (!$includeBots) {
            $query .= " WHERE COALESCE(es_bot, 0) = 0";
        }
        $query .= " ORDER BY id ASC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUsersCount($includeBots = false) {
        $query = "SELECT COUNT(*) FROM usuarios";
        if (!$includeBots) {
            $query .= " WHERE COALESCE(es_bot, 0) = 0";
        }
        $stmt = $this->db->query($query);
        return (int)$stmt->fetchColumn();
    }

    public function listBotUsers($limit = 10) {
        $stmt = $this->db->prepare(
            "SELECT * FROM usuarios
             WHERE COALESCE(es_bot, 0) = 1
             ORDER BY id ASC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function ensureBotUser($index) {
        $index = max(1, (int)$index);
        $cedula = sprintf('BOT%07d', $index);
        $email = sprintf('bot%02d.practica@local.invalid', $index);
        $existing = $this->getUserByEmail($email);
        if ($existing) {
            return $existing;
        }
        $stmt = $this->db->prepare(
            "SELECT * FROM usuarios WHERE cedula = :cedula LIMIT 1"
        );
        $stmt->execute(['cedula' => $cedula]);
        $byCedula = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($byCedula) {
            return $byCedula;
        }

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO usuarios
                 (cedula, nombre_completo, correo_electronico, telefono, contrasena, nivel_acceso, es_bot, estado)
                 VALUES
                 (:cedula, :nombre_completo, :correo_electronico, :telefono, :contrasena, 3, 1, 'activo')"
            );
            $ok = $stmt->execute([
                'cedula' => $cedula,
                'nombre_completo' => sprintf('Rival Simulado %02d', $index),
                'correo_electronico' => $email,
                'telefono' => sprintf('9%09d', $index),
                'contrasena' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT)
            ]);
            if (!$ok) {
                return null;
            }
            return $this->getUserById((int)$this->db->lastInsertId());
        } catch (PDOException $e) {
            app_log('User.ensureBotUser failed', ['message' => $e->getMessage(), 'index' => $index]);
            return null;
        }
    }

    public function getUserById($id) {
        $query = "SELECT * FROM usuarios WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserByEmail($email) {
        $query = "SELECT * FROM usuarios WHERE correo_electronico = :email";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function emailExists($email, $excludeId = null) {
        $query = "SELECT id FROM usuarios WHERE correo_electronico = :email";
        $params = ['email' => $email];

        if ($excludeId !== null) {
            $query .= " AND id != :excludeId";
            $params['excludeId'] = $excludeId;
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function phoneExists($phone, $excludeId = null) {
        $query = "SELECT id FROM usuarios WHERE telefono = :telefono";
        $params = ['telefono' => $phone];

        if ($excludeId !== null) {
            $query .= " AND id != :excludeId";
            $params['excludeId'] = $excludeId;
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createUser($data) {
        $query = "INSERT INTO usuarios (cedula, nombre_completo, correo_electronico, telefono, contrasena, nivel_acceso) 
                  VALUES (:cedula, :nombre_completo, :correo_electronico, :telefono, :contrasena, :nivel_acceso)";
        
        try {
            $stmt = $this->db->prepare($query);
            $data['contrasena'] = password_hash($data['contrasena'], PASSWORD_DEFAULT);
            $result = $stmt->execute($data);
            app_log('User.createUser executed', [
                'result' => $result
            ]);
            return $result;
        } catch (PDOException $e) {
            app_log('User.createUser PDOException', [
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            throw $e;
        }
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

    public function addCPCs($userId, array $cpcIds) {
        $uniqueIds = array_values(array_unique(array_map('intval', $cpcIds)));
        if (empty($uniqueIds)) {
            return false;
        }

        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("INSERT INTO usuarios_cpc (usuario_id, cpc_id) VALUES (:userId, :cpcId)");
            foreach ($uniqueIds as $cpcId) {
                $result = $stmt->execute(['userId' => $userId, 'cpcId' => $cpcId]);
                if (!$result) {
                    $this->db->rollBack();
                    return false;
                }
            }
            return $this->db->commit();
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            app_log('User.addCPCs PDOException', [
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            return false;
        }
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

        if ($user && !empty($user['es_bot'])) {
            return false;
        }

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