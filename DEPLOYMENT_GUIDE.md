# Guía de Despliegue en Producción

## Configuración para Producción

### 1. Cambiar Configuración de Entorno

Para cambiar de desarrollo a producción, ejecuta el script:

```bash
switch_environment.bat
```

Y selecciona la opción `2` (Producción).

### 2. Configuración Automática

El script automáticamente:
- Cambia `BASE_URL` de `/subs/` a `/`
- Configura `DEBUG` a `false`
- Configura `ENVIRONMENT` a `production`

### 3. Archivos de Configuración

- **Desarrollo**: `config/app.php` → `BASE_URL = '/subs/'`
- **Producción**: `config/app.php` → `BASE_URL = '/'`

### 4. JavaScript Adaptativo

El JavaScript ahora detecta automáticamente el entorno:

- **Desarrollo Local** (`localhost`): Usa `/subs/` como base
- **Producción**: Usa `/` como base

### 5. URLs en Producción

- **Desarrollo**: `http://localhost/subs/admin/dashboard`
- **Producción**: `https://sie.hjconsulting.com.ec/admin/dashboard`

## Verificación Pre-Despliegue

### ✅ Checklist de Producción

1. **Configuración de Base de Datos**
   - [ ] Verificar `config/database.php` para producción
   - [ ] Credenciales de producción configuradas

2. **Archivos de Configuración**
   - [ ] `DEBUG = false` en producción
   - [ ] `BASE_URL = '/'` para producción
   - [ ] `ENVIRONMENT = 'production'`

3. **Archivos de Assets**
   - [ ] CSS y JS funcionando correctamente
   - [ ] Imágenes y favicon accesibles

4. **Funcionalidades**
   - [ ] Login funcionando
   - [ ] Dashboards cargando
   - [ ] Botones de edición funcionando
   - [ ] Formularios enviando correctamente

### 🔧 Comandos de Verificación

```bash
# Verificar configuración actual
php -r "require_once 'config/app.php'; echo 'BASE_URL: ' . BASE_URL . PHP_EOL; echo 'DEBUG: ' . (DEBUG ? 'true' : 'false') . PHP_EOL;"

# Verificar archivos críticos
ls -la config/app.php
ls -la public/js/admin-dashboard.js
```

## Solución de Problemas

### Problema: URLs incorrectas en producción
**Solución**: Verificar que `BASE_URL = '/'` en `config/app.php`

### Problema: JavaScript no carga
**Solución**: Verificar que los assets estén en `/public/` y accesibles

### Problema: Formularios no envían
**Solución**: Verificar que las rutas AJAX estén correctas y el JavaScript detecte el entorno

## Notas Importantes

1. **Siempre hacer backup** antes de cambiar a producción
2. **Probar en staging** antes de producción
3. **Verificar logs** del servidor web para errores
4. **Monitorear** el rendimiento después del despliegue

## Rollback

Para volver a desarrollo:

```bash
switch_environment.bat
```

Y selecciona la opción `1` (Desarrollo).
