# Documentación del Proyecto - Sistema de Subastas Inversas

## 📋 Índice
1. [Información General](#información-general)
2. [Arquitectura del Sistema](#arquitectura-del-sistema)
3. [Estructura de Archivos](#estructura-de-archivos)
4. [Configuración y Entornos](#configuración-y-entornos)
5. [Sistema de Enrutamiento](#sistema-de-enrutamiento)
6. [Base de Datos](#base-de-datos)
7. [Controladores y Modelos](#controladores-y-modelos)
8. [Vistas y Frontend](#vistas-y-frontend)
9. [Consideraciones de Desarrollo](#consideraciones-de-desarrollo)
10. [Despliegue y Producción](#despliegue-y-producción)
11. [Problemas Conocidos y Soluciones](#problemas-conocidos-y-soluciones)
12. [Reglas de Desarrollo](#reglas-de-desarrollo)

---

## 📊 Información General

### Descripción del Proyecto
Sistema web para simulación de contratación pública mediante subastas inversas electrónicas. Permite a administradores, moderadores y participantes gestionar procesos de compra pública.

### Tecnologías Utilizadas
- **Backend:** PHP 7.4+ / 8.2+
- **Base de Datos:** MySQL
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Servidor Web:** Apache (XAMPP local) / Nginx (producción)
- **Control de Versiones:** Git

### Roles del Sistema
1. **Administrador (nivel_acceso = 1):** Gestión completa del sistema
2. **Moderador (nivel_acceso = 2):** Gestión de procesos y evaluación
3. **Participante (nivel_acceso = 3):** Participación en procesos

---

## 🏗️ Arquitectura del Sistema

### Patrón MVC
El sistema sigue el patrón Modelo-Vista-Controlador:

```
├── controllers/     # Lógica de negocio
├── models/         # Acceso a datos
├── views/          # Presentación
├── config/         # Configuración
└── public/         # Assets estáticos
```

### Flujo de Solicitudes
1. **index.php** → Enrutamiento
2. **Controlador** → Lógica de negocio
3. **Modelo** → Acceso a base de datos
4. **Vista** → Renderizado HTML

---

## 📁 Estructura de Archivos

```
subs/
├── config/
│   ├── app.php              # Configuración principal
│   ├── app_local.php        # Configuración local (ignorado por Git)
│   └── database.php         # Configuración de BD
├── controllers/
│   ├── AdminController.php
│   ├── AuthController.php
│   ├── ModeratorController.php
│   └── ParticipantController.php
├── models/
│   ├── Bid.php
│   ├── CPC.php
│   ├── Product.php
│   ├── Question.php
│   └── User.php
├── views/
│   ├── admin/
│   ├── auth/
│   ├── moderator/
│   ├── participant/
│   └── layouts/
├── public/
│   ├── css/
│   ├── js/
│   └── images/
├── utils/
│   └── url_helpers.php
├── index.php                # Punto de entrada principal
├── .htaccess               # Configuración Apache
└── .gitignore              # Archivos ignorados por Git
```

---

## ⚙️ Configuración y Entornos

### Archivos de Configuración

#### `config/app.php` (Desarrollo Local)
```php
define('BASE_URL', '/subs/');
define('ENVIRONMENT', 'development');
define('DEBUG', false);
```

#### `config/app_local.php` (Ignorado por Git)
- Configuración específica del entorno local
- No se versiona para evitar conflictos

#### `config/database.php`
```php
// Configuración de base de datos local
$host = 'localhost';
$db   = 'sistema_subastas_inversas';
$user = 'root';
$pass = '';
```

### Variables de Entorno
- **BASE_URL:** URL base del proyecto
- **ENVIRONMENT:** 'development' o 'production'
- **DEBUG:** Habilitar/deshabilitar debug

---

## 🛣️ Sistema de Enrutamiento

### Doble Sistema de Enrutamiento

El sistema soporta **DOS** tipos de enrutamiento:

#### 1. Sistema Legacy (URLs Amigables)
```
/subs/participant/dashboard
/subs/admin/create-user
/subs/moderator/manage-cpcs
```

#### 2. Sistema Nuevo (Query Parameters)
```
/subs/index.php?action=participant_dashboard
/subs/index.php?action=admin_create_user
/subs/index.php?action=moderator_manage_cpcs
```

### Lógica de Enrutamiento en `index.php`

```php
// Detección de query parameters
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';

if (!empty($action)) {
    // Sistema nuevo - query parameters
    switch ($action) {
        case 'participant_dashboard':
            // Lógica del controlador
            break;
    }
} else {
    // Sistema legacy - URLs amigables
    switch ($route) {
        case 'participant/dashboard':
            // Lógica del controlador
            break;
    }
}
```

### Rutas Principales

#### Administrador
- `admin/dashboard` → Dashboard principal
- `admin/create-user` → Crear usuario
- `admin/create-product` → Crear producto
- `admin/create-cpc` → Crear CPC
- `admin/edit-user/{id}` → Editar usuario
- `admin/edit-product/{id}` → Editar producto
- `admin/edit-cpc/{id}` → Editar CPC

#### Moderador
- `moderator/dashboard` → Dashboard moderador
- `moderator/manage-cpcs` → Gestionar CPCs
- `moderator/edit-cpc/{id}` → Editar CPC
- `moderator/manage-questions/{id}` → Gestionar preguntas

#### Participante
- `participant/dashboard` → Dashboard participante
- `participant/profile` → Perfil del usuario
- `participant/search-process` → Buscar proceso
- `participant/view-product/{id}` → Ver producto
- `participant/phase/{phase}` → Cargar fase del proceso

---

## 🗄️ Base de Datos

### Tablas Principales

#### `usuarios`
```sql
id, cedula, nombre_completo, correo_electronico, 
telefono, nivel_acceso, activo, fecha_creacion
```

#### `productos`
```sql
id, entidad, objeto_proceso, cpc_id, codigo, 
tipo_compra, presupuesto_referencial, tipo_contratacion, 
forma_pago, plazo_entrega, vigencia_oferta, 
funcionario_encargado, descripcion, variacion_minima, 
estado_proceso, fecha_creacion
```

#### `cpc`
```sql
id, codigo, descripcion
```

#### `usuarios_cpc`
```sql
usuario_id, cpc_id
```

### Relaciones
- `productos.cpc_id` → `cpc.id`
- `usuarios_cpc.usuario_id` → `usuarios.id`
- `usuarios_cpc.cpc_id` → `cpc.id`

---

## 🎮 Controladores y Modelos

### Controladores

#### `AuthController.php`
- `login()` → Proceso de autenticación
- `logout()` → Cerrar sesión

#### `AdminController.php`
- `dashboard()` → Dashboard administrador
- `createUser()` → Crear usuario
- `createProduct()` → Crear producto
- `createCPC()` → Crear CPC
- `editUser($id)` → Editar usuario
- `editProduct($id)` → Editar producto
- `editCPC($id)` → Editar CPC

#### `ModeratorController.php`
- `dashboard()` → Dashboard moderador
- `manageCPCs()` → Gestionar CPCs
- `editCPC($id)` → Editar CPC
- `manageQuestions($id)` → Gestionar preguntas

#### `ParticipantController.php`
- `dashboard()` → Dashboard participante
- `profile()` → Perfil del usuario
- `searchProcess()` → Buscar proceso
- `viewProduct($id)` → Ver producto
- `loadPhaseContent($phase)` → Cargar fase

### Modelos

#### `User.php`
- `getUserById($id)` → Obtener usuario
- `createUser($data)` → Crear usuario
- `updateUser($id, $data)` → Actualizar usuario
- `getUserCPCs($userId)` → Obtener CPCs del usuario

#### `Product.php`
- `getAllProducts()` → Obtener todos los productos
- `getProductById($id)` → Obtener producto
- `createProduct($data)` → Crear producto
- `getParticipantProducts($userId)` → Obtener productos del participante

#### `CPC.php`
- `getAllCPCs()` → Obtener todos los CPCs
- `getCPCById($id)` → Obtener CPC
- `createCPC($data)` → Crear CPC
- `getUnassignedCPCs($userId)` → Obtener CPCs no asignados

---

## 🎨 Vistas y Frontend

### Estructura de Vistas

#### Layouts
- `layouts/header2.php` → Header principal
- `layouts/footer2.php` → Footer principal
- `participant/participant_layout.php` → Layout participante

#### Vistas del Participante
- `participant/part_dashboard.php` → Dashboard
- `participant/part_profile.php` → Perfil
- `participant/part_search_process.php` → Buscar proceso
- `participant/part_view_product.php` → Ver producto
- `participant/tabs/` → Tabs de detalles del producto
- `participant/phases/` → Contenido de fases del proceso

### JavaScript

#### `public/js/participant-dashboard.js`
- Manejo de navegación AJAX
- Gestión de formularios
- Carga de fases del proceso
- Manejo de tabs

#### `public/js/url-helper.js`
- Generación de URLs
- Compatibilidad entre sistemas
- Detección de entorno

### CSS
- `public/css/styles.css` → Estilos principales

---

## 🔧 Consideraciones de Desarrollo

### Archivos Ignorados por Git

#### `.gitignore`
```
config/database.php
config/app.php
.htaccess
index.php
```

**⚠️ IMPORTANTE:** Estos archivos NO se versionan y deben configurarse manualmente en producción.

### Dependencias Críticas

#### Para Vistas del Participante
```php
<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>
```
**Todas las vistas que usen `url()` deben incluir este archivo.**

#### Para JavaScript
```html
<script src="<?php echo js('url-helper.js'); ?>"></script>
<script src="<?php echo js('participant-dashboard.js'); ?>"></script>
```
**El orden es importante: url-helper.js debe cargarse antes.**

### Logging y Debugging

#### Habilitar Logs en XAMPP
```ini
# php.ini
log_errors = On
error_log = "C:\xampp\apache\logs\php_error.log"
display_errors = On
```

#### Comandos para Ver Logs
```powershell
# PowerShell
Get-Content "C:\xampp\apache\logs\php_error.log" -Wait -Tail 20

# CMD
type "C:\xampp\apache\logs\php_error.log" | findstr /C:"[fecha]"
```

---

## 🚀 Despliegue y Producción

### Configuración de Producción

#### Archivos que DEBEN configurarse manualmente:
1. **`config/database.php`** → Credenciales de producción
2. **`config/app.php`** → URLs y configuración de producción
3. **`.htaccess`** → Configuración de Apache
4. **`index.php`** → Punto de entrada (si está ignorado)

#### Configuración de Base de Datos (Producción)
```php
$host = 'localhost'; // o IP del servidor
$db   = 'nombre_bd_produccion';
$user = 'usuario_produccion';
$pass = 'password_produccion';
```

#### Configuración de URLs (Producción)
```php
define('BASE_URL', '/');
define('ENVIRONMENT', 'production');
define('DEBUG', false);
```

### Proceso de Despliegue

1. **Desarrollo Local:**
   ```bash
   git add .
   git commit -m "Descripción del cambio"
   git push origin master
   ```

2. **En Producción:**
   ```bash
   git pull origin master
   # Configurar archivos ignorados manualmente
   ```

### Verificación Post-Despliegue

1. **Dashboard del participante** → Debe cargar productos
2. **Fases del proceso** → Deben cargar contenido
3. **Menú lateral** → Mi Perfil y Buscar Proceso
4. **CPC** → Debe mostrar descripción, no ID

---

## ⚠️ Problemas Conocidos y Soluciones

### Error: "URLS is not defined"
**Causa:** `url-helper.js` no se carga antes de `participant-dashboard.js`
**Solución:** Verificar orden de carga de scripts

### Error: "Unexpected token '<', "<!DOCTYPE "... is not valid JSON"
**Causa:** Servidor devuelve HTML en lugar de JSON
**Solución:** Verificar que las rutas AJAX devuelvan JSON correctamente

### Error 500 en Dashboard
**Causa:** Vistas no incluyen `url_helpers.php`
**Solución:** Agregar `<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>`

### CPC muestra ID en lugar de descripción
**Causa:** Vistas usan `$product['cpc_id']` en lugar de `$product['cpc_descripcion']`
**Solución:** Controlador debe obtener descripción del CPC

---

## 📋 Reglas de Desarrollo

### ⚠️ REGLAS CRÍTICAS - NUNCA IGNORAR

#### 1. Archivos Ignorados por Git
**ANTES de cualquier cambio, verificar:**
- `config/database.php` → Configurar manualmente en producción
- `config/app.php` → Configurar manualmente en producción
- `.htaccess` → Configurar manualmente en producción
- `index.php` → Si está ignorado, configurar manualmente

#### 2. Dependencias de Vistas
**TODA vista que use `url()` DEBE incluir:**
```php
<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>
```

#### 3. Dependencias de JavaScript
**TODA página que use `participant-dashboard.js` DEBE incluir:**
```html
<script src="<?php echo js('url-helper.js'); ?>"></script>
<script src="<?php echo js('participant-dashboard.js'); ?>"></script>
```

#### 4. Sistema de Enrutamiento
**NUNCA modificar `index.php` sin considerar ambos sistemas:**
- Sistema legacy (URLs amigables)
- Sistema nuevo (query parameters)

#### 5. Base de Datos
**SIEMPRE verificar relaciones:**
- `productos.cpc_id` → `cpc.id`
- `usuarios_cpc.usuario_id` → `usuarios.id`
- `usuarios_cpc.cpc_id` → `cpc.id`

### 🔄 Proceso de Cambios

#### Antes de Modificar Cualquier Archivo:
1. **Verificar si está en `.gitignore`**
2. **Identificar dependencias**
3. **Probar en local primero**
4. **Documentar cambios**

#### Para Nuevas Funcionalidades:
1. **Identificar controlador afectado**
2. **Verificar modelo necesario**
3. **Crear/actualizar vista**
4. **Agregar ruta en `index.php`**
5. **Probar ambos sistemas de enrutamiento**

#### Para Correcciones:
1. **Identificar causa raíz**
2. **Verificar dependencias**
3. **Probar en local**
4. **Commit y push**
5. **Configurar manualmente en producción**

### 🚨 Señales de Alerta

#### Si aparece error 500:
1. Verificar logs de PHP
2. Verificar que las vistas incluyan `url_helpers.php`
3. Verificar que los controladores estén correctos

#### Si no funciona JavaScript:
1. Verificar que `url-helper.js` se carga primero
2. Verificar consola del navegador
3. Verificar que las rutas devuelvan JSON

#### Si no se muestran datos:
1. Verificar conexión a base de datos
2. Verificar que los modelos funcionen
3. Verificar que las vistas reciban datos

---

## 📞 Contacto y Soporte

### Para Problemas Técnicos:
1. Revisar logs de PHP
2. Verificar consola del navegador
3. Consultar esta documentación
4. Verificar archivos ignorados por Git

### Para Nuevas Funcionalidades:
1. Seguir las reglas de desarrollo
2. Probar en local primero
3. Documentar cambios
4. Verificar compatibilidad con ambos sistemas

---

**Última actualización:** Octubre 2025  
**Versión del documento:** 1.0  
**Estado del proyecto:** Funcional en local y producción
