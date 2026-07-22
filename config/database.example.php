<?php
/**
 * Plantilla de configuración de base de datos.
 *
 * Copiar como config/database.php y ajustar credenciales:
 *   cp config/database.example.php config/database.php
 *
 * IMPORTANTE: config/database.php NO se versiona (contiene secretos).
 */
class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $host = 'localhost';
        $db   = 'sistema_subastas_inversas';
        $user = 'USUARIO_MYSQL';
        $pass = 'PASSWORD_MYSQL';

        try {
            $this->conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }
}
