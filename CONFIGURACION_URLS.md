# Configuración de URLs para Entornos

Este documento explica cómo configurar las URLs del proyecto para diferentes entornos (desarrollo y producción).

## Problema Resuelto

El proyecto tenía URLs hardcodeadas con `/subs/` que funcionaban en desarrollo local pero no en producción. Se ha implementado una solución centralizada.

## Archivos Creados

### 1. `config/app.php` (Desarrollo)
- Contiene `BASE_URL = '/subs/'` para desarrollo local
- Este archivo se usa por defecto

### 2. `config/app.production.php` (Producción)
- Contiene `BASE_URL = '/'` para producción
- Debe renombrarse a `app.php` para usar en producción

### 3. `utils/url_helpers.php`
- Funciones helper para generar URLs dinámicamente
- Incluye: `url()`, `css()`, `js()`, `image()`, `login_url()`, `logout_url()`, etc.

## Cómo Cambiar Entre Entornos

### Para Desarrollo Local:
```bash
# Asegúrate de que config/app.php tenga:
define('BASE_URL', '/subs/');
```

### Para Producción:
```bash
# Renombra el archivo de configuración:
mv config/app.production.php config/app.php

# O copia el contenido de app.production.php a app.php
```

## Cambios Realizados

### Archivos Modificados:
1. `index.php` - Ahora usa constantes de configuración
2. `controllers/AuthController.php` - URLs dinámicas
3. `views/auth/login.php` - Funciones helper para URLs
4. `views/admin/dashboard.php` - Funciones helper para URLs
5. Otros archivos de vistas (parcialmente)

### Funciones Helper Disponibles:
- `url($path)` - Genera URL completa
- `css($file)` - URL para archivos CSS
- `js($file)` - URL para archivos JavaScript
- `image($file)` - URL para imágenes
- `login_url()` - URL de login
- `logout_url()` - URL de logout
- `dashboard_url($level)` - URL de dashboard según rol

## Uso en Vistas

### Antes:
```html
<link rel="stylesheet" href="/subs/public/css/styles.css">
<script src="/subs/public/js/admin-dashboard.js"></script>
<a href="/subs/admin/dashboard">Dashboard</a>
```

### Después:
```php
<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>
<link rel="stylesheet" href="<?php echo css('styles.css'); ?>">
<script src="<?php echo js('admin-dashboard.js'); ?>"></script>
<a href="<?php echo url('admin/dashboard'); ?>">Dashboard</a>
```

## Recomendaciones

1. **Agregar al .gitignore:**
   ```
   config/app.production.php
   ```

2. **Para completar la migración:**
   - Ejecutar el script `utils/fix_urls.php` en todos los archivos
   - O procesar manualmente los archivos restantes

3. **Verificar funcionamiento:**
   - Probar en desarrollo local con `/subs/`
   - Probar en producción sin `/subs/`

## Script de Migración

Si tienes PHP disponible, puedes ejecutar:
```bash
php utils/fix_urls.php
```

Este script procesará automáticamente todos los archivos PHP y JS para reemplazar URLs hardcodeadas.

## Estructura Final

```
config/
├── app.php                 # Configuración actual (desarrollo o producción)
├── app.production.php      # Plantilla para producción
└── database.php           # Configuración de base de datos

utils/
├── url_helpers.php        # Funciones helper para URLs
├── fix_urls.php          # Script de migración
└── replace_urls.php      # Script alternativo
```

## Beneficios

1. **Un solo archivo de configuración** para cambiar entre entornos
2. **URLs dinámicas** que se adaptan automáticamente
3. **Mantenimiento fácil** - cambios centralizados
4. **Sin conflictos** en el control de versiones
5. **Flexibilidad** para diferentes configuraciones de servidor
