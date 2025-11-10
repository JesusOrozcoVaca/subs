# Documentación del Proyecto - Sistema de Subastas Inversas


**Considerar este archivo como regla general

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

#### ⚠️ CONSIDERACIÓN CRÍTICA - Proceso de Despliegue Específico

**Archivos versionados vs no versionados:**

- **Archivos versionados (se actualizan automáticamente con `git pull`):**
  - Todos los archivos del proyecto EXCEPTO los listados en `.gitignore`
  - Incluye: controladores, modelos, vistas, CSS, JavaScript, etc.

- **Archivos NO versionados (requieren configuración manual):**
  - `config/database.php` → Credenciales de producción
  - `config/app.php` → URLs y configuración de producción  
  - `.htaccess` → Configuración de Apache
  - `index.php` → **Se copia manualmente desde `indexpro.php`**
  - `uploads/` → Archivos subidos por usuarios (se crean dinámicamente)

**Proceso específico para `index.php` en producción:**
1. En local: `indexpro.php` es la plantilla
2. En producción: Copiar contenido de `indexpro.php` y pegarlo en `index.php`
3. **NUNCA** hacer commit de `index.php` (está en `.gitignore`)

#### 🔄 GESTIÓN DE `indexpro.php` → `index.php`

**PROCESO CRÍTICO DE DESPLIEGUE:**

1. **Desarrollo Local:**
   - Modificar `indexpro.php` (archivo versionado)
   - Probar funcionalidad en local
   - Hacer commit y push de `indexpro.php`

2. **Despliegue en Producción:**
   ```bash
   # En servidor de producción
   git pull origin master
   
   # Copiar contenido de indexpro.php a index.php
   cp indexpro.php index.php
   ```

3. **Verificación Post-Despliegue:**
   - ✅ Verificar que `index.php` existe en producción
   - ✅ Verificar que las rutas funcionan correctamente
   - ✅ Probar funcionalidades AJAX (Preguntas y Respuestas, etc.)

**⚠️ PUNTOS CRÍTICOS:**

- **`indexpro.php`** = Plantilla versionada (se actualiza con `git pull`)
- **`index.php`** = Archivo de producción (se copia manualmente)
- **JavaScript** debe detectar automáticamente el entorno
- **URLs** se generan dinámicamente según el entorno detectado

**ERRORES COMUNES A EVITAR:**
- ❌ Modificar `index.php` directamente en producción
- ❌ Hacer commit de `index.php` (está en `.gitignore`)
- ❌ Hardcodear URLs en JavaScript
- ❌ Olvidar copiar `indexpro.php` a `index.php` después de `git pull`

**Ventajas de este enfoque:**
- ✅ Mantiene configuraciones específicas de cada entorno
- ✅ Evita conflictos entre desarrollo y producción
- ✅ Permite personalizaciones sin afectar el repositorio

#### 🚨 CONSIDERACIÓN CRÍTICA - Detección de Entorno en JavaScript

**PROBLEMA RESUELTO:** El JavaScript debe detectar automáticamente si está ejecutándose en desarrollo local o en producción para generar las URLs correctas.

**CAUSA RAÍZ:** 
- **Desarrollo local:** Usa URLs amigables (`/subs/participant/phase/pyr`)
- **Producción:** Usa query parameters (`/subs/index.php?action=participant_phase&phase=pyr`)
- **El mismo código JavaScript debe funcionar en ambos entornos**

**SOLUCIÓN IMPLEMENTADA:**

```javascript
// Detección automática de entorno
const isProduction = window.location.pathname.includes('index.php') || 
                    window.location.hostname.includes('hjconsulting.com.ec');

// Generación de URLs según entorno
let url;
if (isProduction) {
    // Producción: query parameters
    url = `/subs/index.php?action=participant_phase&phase=${phase}&producto_id=${productId}`;
} else {
    // Local: URLs amigables
    url = `/subs/participant/phase/${phase}?producto_id=${productId}`;
}
```

**EXTRACCIÓN DE PARÁMETROS:**

```javascript
function getProductIdFromURL() {
    // Primero intentar extraer de parámetros de URL (para producción)
    const urlParams = new URLSearchParams(window.location.search);
    const productIdFromParams = urlParams.get('id');
    
    if (productIdFromParams) {
        return productIdFromParams; // ✅ Devuelve '15' de ?id=15
    }
    
    // Si no hay parámetros, intentar extraer del pathname (para local)
    const pathParts = window.location.pathname.split('/');
    const productIdFromPath = pathParts[pathParts.length - 1];
    
    // Verificar que sea un número (ID válido)
    if (productIdFromPath && !isNaN(productIdFromPath)) {
        return productIdFromPath;
    }
    
    return '1'; // Fallback
}
```

**DETECCIÓN DE PÁGINA DE PRODUCTO:**

```javascript
// Detectar si estamos en página de producto (ambos entornos)
const isProductPage = window.location.pathname.includes('/view-product/') || 
                     window.location.href.includes('participant_view_product');
```

**⚠️ REGLAS CRÍTICAS:**

1. **NUNCA hardcodear URLs** - Siempre usar detección de entorno
2. **SIEMPRE validar parámetros** - Verificar que los IDs sean numéricos
3. **SIEMPRE probar en ambos entornos** - Local y producción
4. **MANTENER compatibilidad** - El mismo código debe funcionar en ambos sistemas
5. **DOCUMENTAR cambios** - Cualquier modificación debe incluir esta consideración

**CASOS DE USO:**
- ✅ Carga de contenido de fases (`loadPhaseContent`)
- ✅ Envío de preguntas (`submitPregunta`)
- ✅ Carga de preguntas (`loadPreguntas`)
- ✅ Cualquier funcionalidad AJAX que dependa de URLs

**ESTA CONSIDERACIÓN ES FUNDAMENTAL** - Sin ella, el sistema falla silenciosamente en producción mientras funciona perfectamente en local.

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

## 🆕 Consideraciones Principales - Desarrollo Avanzado

### 📊 Sistema de Gestión de Estados de Productos

#### Nueva Tabla: `estados_producto`
```sql
CREATE TABLE estados_producto (
    id INT PRIMARY KEY AUTO_INCREMENT,
    descripcion VARCHAR(100) NOT NULL,
    activo BOOLEAN DEFAULT TRUE
);

-- Estados por defecto
INSERT INTO estados_producto (descripcion) VALUES 
('Preguntas y Respuestas'),
('Entrega de Ofertas'),
('Por adjudicar'),
('Adjudicado'),
('Cancelado');
```

#### Migración de Datos
```sql
-- Agregar columna estado_id a productos
ALTER TABLE productos ADD COLUMN estado_id INT;
ALTER TABLE productos ADD FOREIGN KEY (estado_id) REFERENCES estados_producto(id);

-- Migrar datos existentes (estado_proceso → estado_id)
UPDATE productos SET estado_id = 1 WHERE estado_proceso = 'Preguntas y Respuestas';
UPDATE productos SET estado_id = 2 WHERE estado_proceso = 'Entrega de Ofertas';
-- ... etc

-- Eliminar columna antigua
ALTER TABLE productos DROP COLUMN estado_proceso;
```

### 🔧 Sistema de Preguntas y Respuestas

#### Tabla: `preguntas_respuestas`
```sql
CREATE TABLE preguntas_respuestas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    producto_id INT NOT NULL,
    usuario_id INT NOT NULL,
    pregunta TEXT NOT NULL,
    respuesta TEXT NULL,
    fecha_pregunta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_respuesta TIMESTAMP NULL,
    FOREIGN KEY (producto_id) REFERENCES productos(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);
```

#### Funcionalidades Implementadas
- **Participantes:** Pueden hacer preguntas ilimitadas (máx 500 caracteres)
- **Admin/Moderador:** Pueden responder preguntas desde popup
- **Visibilidad:** Preguntas visibles para todos los participantes del CPC
- **Control de estado:** Preguntas se deshabilitan cuando cambia el estado del producto

### 🎯 Sistema de Popups Dinámicos

#### Event Delegation Pattern
```javascript
// Interceptar clics en elementos dinámicos
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('btn-edit')) {
        e.preventDefault();
        createSimplePopup(e.target.getAttribute('href'));
    }
});
```

#### Popup Dinámico
```javascript
function createSimplePopup(url) {
    // Crear overlay
    const overlay = document.createElement('div');
    overlay.style.cssText = `
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background-color: rgba(0, 0, 0, 0.8); z-index: 99999;
        display: flex; justify-content: center; align-items: center;
    `;
    
    // Crear popup
    const popup = document.createElement('div');
    popup.style.cssText = `
        background-color: white; border-radius: 8px; padding: 20px;
        max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto;
    `;
    
    // Cargar contenido via AJAX
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(response => response.text())
        .then(html => popup.innerHTML = html);
}
```

### 🔄 Interceptación de Formularios

#### Manejo AJAX de Formularios
```javascript
// Interceptar envío de formularios
form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const formAction = this.getAttribute('action');
    
    fetch(formAction, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            closePopup();
            location.reload();
        }
    });
});
```

### 🎨 Gestión de Estados en Controladores

#### Detección de Peticiones AJAX
```php
private function isAjaxRequest() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
}

public function editProduct($id) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Procesar actualización
        $result = $this->productModel->updateProduct($id, $_POST);
        $this->sendJsonResponse($result, "Producto actualizado exitosamente.");
    } else {
        // Si es AJAX, devolver solo formulario
        if ($this->isAjaxRequest()) {
            require 'views/moderator/mod_edit_product_form.php';
        } else {
            // Si no es AJAX, devolver página completa
            require 'views/moderator/mod_edit_product.php';
        }
    }
}
```

### 📱 Sistema de Tabs Dinámicos

#### Gestión de Contenido Dinámico
```javascript
// Sistema unificado de tabs
function loadTabContent(tabId, url) {
    const contentArea = document.getElementById('tab-content');
    contentArea.innerHTML = '<div class="loading">Cargando...</div>';
    
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(response => response.text())
        .then(html => {
            contentArea.innerHTML = html;
            // Re-inicializar listeners para contenido dinámico
            initDynamicListeners();
        });
}
```

### 🎯 Patrones de Desarrollo Aprendidos

#### 1. Análisis Exhaustivo Antes de Implementar
- **SIEMPRE** revisar cómo está implementado en el administrador
- **NUNCA** asumir funcionalidad sin verificar el código existente
- **SIEMPRE** replicar patrones probados y funcionales

#### 2. Event Delegation para Elementos Dinámicos
- **Problema:** Event listeners no se adjuntan a elementos cargados dinámicamente
- **Solución:** Usar event delegation en el documento padre
- **Patrón:** `document.addEventListener('click', function(e) { if (e.target.matches('.selector')) { ... } })`

#### 3. Interceptación de Formularios
- **Problema:** Formularios se envían como navegación normal
- **Solución:** Interceptar con `preventDefault()` y manejar como AJAX
- **Resultado:** Experiencia de usuario fluida sin recargas de página

#### 4. Detección de Contexto (AJAX vs Página Completa)
- **Problema:** Mismo endpoint para popup y página completa
- **Solución:** Header `X-Requested-With: XMLHttpRequest`
- **Resultado:** Servidor devuelve contenido apropiado según el contexto

#### 5. Gestión de Estados de Productos
- **Problema:** Estados hardcodeados como strings
- **Solución:** Tabla `estados_producto` con relaciones
- **Beneficio:** Flexibilidad y mantenibilidad

### 🚨 Errores Comunes y Soluciones

#### Error: "Modal not found"
**Causa:** JavaScript busca modal estático que no existe
**Solución:** Crear modal dinámicamente con `createSimplePopup()`

#### Error: "Form submission redirects to JSON page"
**Causa:** Formulario no interceptado, se envía como navegación normal
**Solución:** Interceptar con `addEventListener('submit', preventDefault)`

#### Error: "Popup shows full page layout"
**Causa:** Servidor devuelve página completa en lugar de solo formulario
**Solución:** Detectar AJAX y devolver vista específica para popup

#### Error: "Event listeners not working on dynamic content"
**Causa:** Listeners adjuntados antes de que exista el elemento
**Solución:** Event delegation en documento padre

### 📋 Checklist de Desarrollo

#### Antes de Implementar Nueva Funcionalidad:
1. ✅ **Analizar implementación existente** (administrador)
2. ✅ **Identificar patrones probados**
3. ✅ **Replicar estructura exacta**
4. ✅ **Verificar rutas en ambos sistemas** (legacy y query params)
5. ✅ **Probar en local primero**

#### Para Popups Dinámicos:
1. ✅ **Event delegation en documento**
2. ✅ **Creación dinámica de modal**
3. ✅ **Header AJAX en peticiones**
4. ✅ **Detección de contexto en servidor**
5. ✅ **Interceptación de formularios**

#### Para Gestión de Estados:
1. ✅ **Crear tabla de estados**
2. ✅ **Migrar datos existentes**
3. ✅ **Actualizar controladores**
4. ✅ **Modificar vistas para mostrar descripción**
5. ✅ **Eliminar columnas obsoletas**

---

## 🚨 CONSIDERACIÓN CRÍTICA - URLs Dinámicas y Enrutamiento

### **PROBLEMA IDENTIFICADO:**
El uso de URLs hardcodeadas causa problemas de enrutamiento entre entornos local y producción, especialmente para recursos estáticos como archivos subidos por usuarios.

### **SÍNTOMAS:**
- Botones "Ver" redirigen al dashboard en producción
- Enlaces a archivos no funcionan correctamente
- URLs absolutas fallan en diferentes configuraciones de servidor

### **SOLUCIÓN OBLIGATORIA:**
**SIEMPRE usar detección de entorno y URLs dinámicas** para cualquier enlace a recursos:

```javascript
// ✅ CORRECTO - Detección de entorno
const isProduction = window.location.pathname.includes('index.php') || 
                    window.location.hostname.includes('hjconsulting.com.ec');

const baseUrl = isProduction ? '/subs/' : '/subs/';
const fileUrl = baseUrl + rutaArchivo;

// ❌ INCORRECTO - URL hardcodeada
const fileUrl = '/subs/' + rutaArchivo;
```

### **CASOS DE USO CRÍTICOS:**
1. **Enlaces a archivos subidos** (`uploads/offers/`)
2. **Enlaces a recursos estáticos** (CSS, JS, imágenes)
3. **Enlaces a vistas** (formularios, reportes)
4. **URLs de API** (endpoints AJAX)

### **IMPLEMENTACIÓN REQUERIDA:**
```javascript
// Función helper para generar URLs dinámicas
function generateUrl(path) {
    const isProduction = window.location.pathname.includes('index.php') || 
                        window.location.hostname.includes('hjconsulting.com.ec');
    const baseUrl = isProduction ? '/subs/' : '/subs/';
    return baseUrl + path;
}

// Uso en enlaces
<a href="${generateUrl(oferta.ruta_archivo)}" target="_blank">Ver</a>
```

### **REGLAS OBLIGATORIAS:**
1. **NUNCA** hardcodear URLs absolutas
2. **SIEMPRE** detectar entorno antes de generar URLs
3. **INCLUIR** logs de debugging para URLs generadas
4. **PROBAR** en ambos entornos (local y producción)

### **ARCHIVOS AFECTADOS:**
- `public/js/unified-tabs.js`
- `views/participant/phases/eof.php`
- Cualquier archivo que genere enlaces dinámicos

**Esta consideración es CRÍTICA y debe aplicarse en TODOS los desarrollos futuros para evitar problemas de enrutamiento.**

---

## 🚨 CONSIDERACIÓN CRÍTICA - Servicio de Documentos PDF y Archivos Estáticos

### **PROBLEMA IDENTIFICADO:**
Los archivos PDF y otros documentos no se pueden servir directamente desde el servidor web en producción, causando errores 404 o 500 al intentar acceder a ellos.

### **SÍNTOMAS:**
- Botones "Ver" redirigen al dashboard en lugar de mostrar archivos
- Error 404 al acceder a archivos en `uploads/`
- Error 500 en producción al intentar servir archivos estáticos
- URLs como `/subs/uploads/offers/archivo.pdf` no funcionan

### **CAUSA RAÍZ:**
- **Servidor web no configurado** para servir archivos desde `uploads/`
- **Falta de ruta específica** en la aplicación PHP para manejar archivos
- **URLs hardcodeadas** que no pasan por el sistema de enrutamiento

### **SOLUCIÓN OBLIGATORIA:**
**SIEMPRE crear una ruta `view_file` en el sistema de enrutamiento** para servir archivos de forma controlada:

```php
case 'view_file':
    // Servir archivos estáticos (uploads)
    $filePath = $_GET['path'] ?? '';
    
    if (empty($filePath)) {
        http_response_code(400);
        echo "Archivo no especificado";
        exit;
    }
    
    // Validar que el archivo esté dentro del directorio uploads
    $fullPath = __DIR__ . '/' . $filePath;
    $uploadsDir = __DIR__ . '/uploads/';
    
    // Verificar que el directorio uploads existe
    if (!is_dir($uploadsDir)) {
        http_response_code(500);
        echo "Directorio uploads no existe";
        exit;
    }
    
    // Verificar que el archivo esté dentro del directorio uploads
    $realFullPath = realpath($fullPath);
    $realUploadsDir = realpath($uploadsDir);
    
    if (!$realFullPath || !$realUploadsDir || strpos($realFullPath, $realUploadsDir) !== 0) {
        http_response_code(403);
        echo "Acceso denegado";
        exit;
    }
    
    // Verificar que el archivo existe y es válido
    if (!file_exists($fullPath) || !is_file($fullPath)) {
        http_response_code(404);
        echo "Archivo no encontrado";
        exit;
    }
    
    // Determinar el tipo MIME
    $mimeType = mime_content_type($fullPath);
    if (!$mimeType) {
        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        switch ($extension) {
            case 'pdf':
                $mimeType = 'application/pdf';
                break;
            case 'jpg':
            case 'jpeg':
                $mimeType = 'image/jpeg';
                break;
            case 'png':
                $mimeType = 'image/png';
                break;
            default:
                $mimeType = 'application/octet-stream';
        }
    }
    
    // Limpiar cualquier output buffer
    if (ob_get_level()) {
        ob_clean();
    }
    
    // Establecer headers para servir el archivo
    header('Content-Type: ' . $mimeType);
    header('Content-Length: ' . filesize($fullPath));
    header('Content-Disposition: inline; filename="' . basename($fullPath) . '"');
    header('Cache-Control: private, max-age=3600');
    
    // Leer y enviar el archivo
    $result = readfile($fullPath);
    if ($result === false) {
        http_response_code(500);
        echo "Error al leer el archivo";
        exit;
    }
    
    exit;
```

### **GENERACIÓN DE URLs CORRECTAS:**
**Usar función helper para generar URLs dinámicas:**

```javascript
// Función helper para generar URLs de archivos
function generateUrl(path) {
    const isProduction = window.location.pathname.includes('index.php') || 
                        window.location.hostname.includes('hjconsulting.com.ec');
    const baseUrl = isProduction ? '/subs/' : '/subs/';
    return `${baseUrl}index.php?action=view_file&path=${encodeURIComponent(path)}`;
}

// Uso en enlaces
const fileUrl = generateUrl(oferta.ruta_archivo);
// Resultado: /subs/index.php?action=view_file&path=uploads%2Foffers%2Farchivo.pdf
```

### **VENTAJAS DE ESTA SOLUCIÓN:**
1. **Seguridad:** Validación de rutas y permisos
2. **Control:** Headers personalizados y logs detallados
3. **Compatibilidad:** Funciona en local y producción
4. **Flexibilidad:** Fácil de extender para otros tipos de archivos
5. **Debugging:** Logs detallados para identificar problemas

### **CASOS DE USO CRÍTICOS:**
1. **Entrega de Ofertas (EOF)** - Archivos PDF, JPG, PNG
2. **Documentos de Contratación** - PDFs de contratos
3. **Evidencias de Pago** - Comprobantes en PDF
4. **Reportes del Sistema** - PDFs generados dinámicamente
5. **Certificados y Diplomas** - Documentos oficiales

### **REGLAS OBLIGATORIAS:**
1. **NUNCA** servir archivos directamente desde el servidor web
2. **SIEMPRE** usar la ruta `view_file` para archivos de usuarios
3. **VALIDAR** que los archivos estén dentro de `uploads/`
4. **INCLUIR** logs de debugging para troubleshooting
5. **PROBAR** en ambos entornos (local y producción)

### **ARCHIVOS AFECTADOS:**
- `index.php` / `indexpro.php` - Ruta `view_file`
- `public/js/unified-tabs.js` - Función `generateUrl()`
- `views/participant/phases/eof.php` - URLs de archivos
- Cualquier vista que muestre enlaces a archivos

### **CONFIGURACIÓN DE PRODUCCIÓN:**
```apache
# .htaccess - Asegurar que todas las solicitudes pasen por index.php
RewriteEngine On
RewriteBase /subs/

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [L,QSA]
```

**Esta solución es CRÍTICA para todas las fases futuras del proyecto que involucren manejo de documentos.**

---

**Última actualización:** Octubre 2025  
**Versión del documento:** 2.0  
**Estado del proyecto:** Funcional en local y producción con sistema avanzado de gestión de estados y popups dinámicos

