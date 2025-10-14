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
- **`config/app_pro.php`** - Configuración temporal para producción (nueva arquitectura)
- **`config/app.php`** - Configuración para desarrollo local (sistema legacy) - IGNORADO por Git
- **`config/app_local.php`** - Configuración alternativa para desarrollo local
- **`public/js/url-helper.js`** - Helper para compatibilidad entre sistemas

## 🔧 **Instrucciones de Uso**

### **Para Producción (Nueva Arquitectura)**
```bash
# 1. Hacer git pull
git pull origin master

# 2. Renombrar index_new.php a index.php (una sola vez)
mv index_new.php index.php

# 3. Copiar configuración de producción
cp config/app_pro.php config/app.php

# 4. Acceder con nuevas URLs
https://sie.hjconsulting.com.ec/index.php?action=login

# 5. Eliminar archivos temporales (opcional)
rm config/app_pro.php
```

### **Para Desarrollo Local (Sistema Legacy)**
```bash
# 1. Asegurarse de que config/app.php esté configurado para desarrollo
# (Ya está configurado automáticamente - BASE_URL = '/subs/')

# 2. Asegurarse de que .htaccess esté configurado para /subs/
# (Si no existe, copiar de .htaccess.example)

# 3. Acceder con URLs legacy
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
# El archivo config/app.php ya está configurado para desarrollo local
# Solo asegurarse de que BASE_URL = '/subs/'
```

### **Ventajas de esta estrategia:**
- ✅ **Separación total** entre entornos
- ✅ **Sin conflictos** de archivos entre local y producción
- ✅ **Archivos ignorados** por Git (config/app.php, index.php)
- ✅ **Configuración temporal** (app_pro.php se elimina después del deploy)
- ✅ **Más técnico y seguro** que editar manualmente
