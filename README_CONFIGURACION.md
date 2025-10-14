# 📁 Configuración del Proyecto

## 🔧 Archivos de Configuración por Entorno

Este proyecto utiliza archivos de configuración **específicos por entorno** que **NO se suben a Git**.

### Archivos que NO están en Git:
- `config/app.php` - Configuración de la aplicación (BASE_URL, DEBUG, etc.)
- `config/database.php` - Credenciales de base de datos
- `.htaccess` - Configuración de Apache/servidor web

### Archivos de Ejemplo (SÍ están en Git):
- `config/app.example.php` - Plantilla para `app.php`
- `config/database.example.php` - Plantilla para `database.php`
- `.htaccess.example` - Plantilla para `.htaccess`

## 🚀 Configuración Inicial

### Para Desarrollo Local:

1. **Copia los archivos de ejemplo:**
   ```bash
   cp config/app.example.php config/app.php
   cp config/database.example.php config/database.php
   cp .htaccess.example .htaccess
   ```

2. **Edita `config/app.php`:**
   ```php
   define('BASE_URL', '/subs/'); // ← Para desarrollo local
   define('DEBUG', false);
   define('ENVIRONMENT', 'development');
   ```

3. **Edita `config/database.php`:**
   ```php
   $host = 'localhost';
   $db   = 'sistema_subastas_inversas';
   $user = 'root';
   $pass = '';
   ```

4. **Edita `.htaccess`:**
   ```apache
   RewriteBase /subs/
   ErrorDocument 404 /subs/index.php
   ```

### Para Producción:

1. **Copia los archivos de ejemplo:**
   ```bash
   cp config/app.example.php config/app.php
   cp config/database.example.php config/database.php
   cp .htaccess.example .htaccess
   ```

2. **Edita `config/app.php`:**
   ```php
   define('BASE_URL', '/'); // ← Para producción
   define('DEBUG', false);
   define('ENVIRONMENT', 'production');
   ```

3. **Edita `config/database.php`:**
   ```php
   $host = 'localhost';
   $db   = 'tu_base_de_datos_produccion';
   $user = 'tu_usuario_produccion';
   $pass = 'tu_contraseña_produccion';
   ```

4. **Edita `.htaccess`:**
   ```apache
   RewriteBase /
   ErrorDocument 404 /index.php
   ```

## 🔄 Despliegues con Git

### ✅ Ventajas de este Enfoque:

1. **No se sobrescriben configuraciones:** Después de `git pull`, tus archivos de configuración en producción permanecen intactos.
2. **Seguridad:** Las credenciales de base de datos no se suben a Git.
3. **Flexibilidad:** Cada entorno tiene su propia configuración sin conflictos.

### 📋 Flujo de Trabajo:

1. **Desarrollo Local:**
   ```bash
   git add <archivos_modificados>
   git commit -m "Descripción de cambios"
   git push origin master
   ```

2. **Producción:**
   ```bash
   git pull origin master
   # ✅ Los archivos config/app.php, config/database.php y .htaccess NO se modifican
   # ✅ Solo se actualizan controllers, views, js, css, etc.
   ```

## ⚠️ Importante

- **NUNCA** hagas commit de `config/app.php`, `config/database.php` o `.htaccess`
- **SIEMPRE** usa los archivos `.example` como referencia
- Si agregas nuevas configuraciones, actualiza los archivos `.example` correspondientes

## 📚 Documentación Adicional

- Ver `INSTRUCCIONES_PRODUCCION.md` para instrucciones detalladas de despliegue

