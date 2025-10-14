# Instrucciones para Despliegue en Producción

## 🔧 Archivos de Configuración

Los siguientes archivos **NO se subirán a Git** y debes configurarlos manualmente en cada entorno:
- `config/app.php` - Configuración de la aplicación (BASE_URL)
- `config/database.php` - Credenciales de base de datos
- `.htaccess` - Configuración de Apache

## 📋 Pasos para Configurar Producción

### Paso 1: Copiar Archivos de Ejemplo

Después de hacer `git pull` en producción, copia los archivos de ejemplo:

```bash
# Si no existen los archivos, créalos desde los ejemplos:
cp config/app.example.php config/app.php
cp config/database.example.php config/database.php
cp .htaccess.example .htaccess
```

### Paso 2: Configurar BASE_URL en Producción
Edita el archivo `config/app.php` en producción:
```php
// Cambiar de:
define('BASE_URL', '/subs/');

// A:
define('BASE_URL', '/');
```

### Paso 3: Configurar .htaccess en Producción
Edita el archivo `.htaccess` en producción:
```apache
# Cambiar de:
RewriteBase /subs/
ErrorDocument 404 /subs/index.php

# A:
RewriteBase /
ErrorDocument 404 /index.php
```

### Paso 4: Configurar Database en Producción
Edita el archivo `config/database.php` con las credenciales de producción:
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

### Paso 5: Deshabilitar DEBUG en Producción
En el archivo `config/app.php`, asegúrate de que:
```php
define('DEBUG', false);
define('ENVIRONMENT', 'production');
```

### Paso 6: Verificar Permisos de Archivos
Asegúrate de que los archivos tengan permisos de lectura:
```bash
chmod 644 config/app.php
chmod 644 .htaccess
chmod 644 config/database.php
chmod -R 644 public/css/
chmod -R 644 public/js/
chmod -R 644 public/images/
```

## ✅ Verificación
1. Los archivos CSS/JS deben servirse con el tipo MIME correcto
2. Las rutas deben funcionar sin el prefijo `/subs/`
3. No debe haber errores 500 en archivos estáticos
4. El login debe funcionar correctamente

## 🔄 Importante: Despliegues Futuros

**Después de cada `git pull` en producción:**
- ✅ Los archivos `config/app.php`, `config/database.php` y `.htaccess` **NO se sobrescribirán**
- ✅ Tu configuración de producción se mantendrá intacta
- ✅ Solo se actualizarán los archivos de código (controllers, views, js, etc.)

**Si necesitas actualizar la configuración:**
- Revisa los archivos `.example` para ver si hay nuevas opciones
- Actualiza manualmente tus archivos de configuración en producción
