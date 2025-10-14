# Nueva Arquitectura de Routing

## 🎯 **Problema Resuelto**

La nueva arquitectura soluciona los problemas de:
- **Interferencia de WordPress** en servidores compartidos
- **Dependencia de `.htaccess`** para routing
- **Redirecciones complejas** afectadas por errores externos
- **Routing frágil** basado en `$_SERVER['REQUEST_URI']`

## 🚀 **Nueva Arquitectura**

### **Sistema de Query Parameters**
```php
// En lugar de: /admin/dashboard
// Ahora: /index.php?action=admin_dashboard

// En lugar de: /login  
// Ahora: /index.php?action=login
```

### **Ventajas**
- ✅ **Sin dependencia de `.htaccess`**
- ✅ **Sin interferencia de WordPress**
- ✅ **Routing más predecible**
- ✅ **Fácil debugging**
- ✅ **Más portable entre servidores**
- ✅ **Menos propenso a errores**

## 📋 **Archivos Principales**

- **`index_new.php`** - Nuevo sistema de routing (para producción)
- **`index.php`** - Sistema legacy (para desarrollo local)
- **`config/app.php`** - Configuración para producción (nueva arquitectura)
- **`config/app_local.php`** - Configuración para desarrollo local (sistema legacy)
- **`public/js/url-helper.js`** - Helper para compatibilidad entre sistemas

## 🔧 **Instrucciones de Uso**

### **Para Producción (Nueva Arquitectura)**
```bash
# 1. Hacer git pull
git pull origin master

# 2. Renombrar index_new.php a index.php (una sola vez)
mv index_new.php index.php

# 3. Acceder con nuevas URLs
https://sie.hjconsulting.com.ec/index.php?action=login
```

### **Para Desarrollo Local (Sistema Legacy)**
```bash
# 1. Usar config/app_local.php
cp config/app_local.php config/app.php

# 2. Acceder con URLs legacy
http://localhost/subs/login
http://localhost/subs/admin/dashboard
```

## 🔄 **Compatibilidad**

- **Producción:** Usa nueva arquitectura (query parameters)
- **Desarrollo Local:** Usa sistema legacy (URLs amigables)
- **Ambas versiones mantienen toda la funcionalidad**

## 📝 **Mapeo de URLs**

| Sistema Legacy | Nueva Arquitectura |
|---|---|
| `/login` | `/index.php?action=login` |
| `/admin/dashboard` | `/index.php?action=admin_dashboard` |
| `/admin/edit-user/123` | `/index.php?action=admin_edit_user&id=123` |
| `/participant/dashboard` | `/index.php?action=participant_dashboard` |
| `/moderator/dashboard` | `/index.php?action=moderator_dashboard` |

## 🛠️ **Mantenimiento**

### **Para volver al sistema anterior:**
```bash
# Restaurar desde backup (si existe)
mv index_legacy_backup.php index.php
mv config/app_backup.php config/app.php
```

### **Para limpiar archivos de backup:**
```bash
rm -f index_legacy_backup.php
rm -f config/app_backup.php
```

### **Para desarrollo local:**
```bash
# Usar sistema legacy
cp config/app_local.php config/app.php
```
