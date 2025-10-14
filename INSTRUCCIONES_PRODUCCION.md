# Instrucciones para Despliegue en Producción

## Problema Actual
- En producción: Respuesta 200 pero pantalla en blanco (archivos JS se sirven como HTML)
- En localhost: Error 404 Not Found

## Solución

### Paso 1: Cambiar BASE_URL en Producción
En el archivo `config/app.php`, cambiar **MANUALMENTE**:
```php
// De:
define('BASE_URL', '/subs/');

// A:
define('BASE_URL', '/');
```

**Importante**: Este archivo NO está en .gitignore, por lo que debes editarlo directamente en el servidor.

### Paso 2: Configurar .htaccess en Producción
✅ **Ya configurado**: Tienes el `.htaccess` correcto en producción.

### Paso 3: Configurar Database en Producción
Crear el archivo `config/database.php` con las credenciales de producción:
```php
<?php
class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $host = 'TU_HOST_DE_PRODUCCION';
        $db   = 'TU_BASE_DE_DATOS';
        $user = 'TU_USUARIO';
        $pass = 'TU_PASSWORD';

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
```

### Paso 4: Deshabilitar DEBUG en Producción
En `config/app.php`:
```php
define('DEBUG', false);
```

## Verificación
1. Los archivos CSS/JS deben servirse con el tipo MIME correcto
2. Las rutas deben funcionar sin el prefijo `/subs/`
3. No debe haber errores 500 en archivos estáticos

## Rollback
Si algo sale mal, revertir los cambios:
1. Cambiar `BASE_URL` de vuelta a `/subs/`
2. Restaurar el `.htaccess` anterior
3. Verificar que localhost funcione
