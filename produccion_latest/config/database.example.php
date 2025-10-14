<?php
/**
 * Configuración de Base de Datos - ARCHIVO DE EJEMPLO
 * 
 * Copia este archivo como 'database.php' y configura con tus credenciales:
 * - Desarrollo local: localhost, root, sin contraseña
 * - Producción: credenciales de tu servidor de producción
 */
class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $host = 'localhost';
        $db   = 'sistema_subastas_inversas'; // ← CAMBIAR SEGÚN ENTORNO
        $user = 'root'; // ← CAMBIAR SEGÚN ENTORNO
        $pass = ''; // ← CAMBIAR SEGÚN ENTORNO

        try {
            $this->conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if(!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }
}

