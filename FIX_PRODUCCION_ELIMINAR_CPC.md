# FIX CRÍTICO PARA PRODUCCIÓN - Eliminación de CPC

## 🔍 PROBLEMA IDENTIFICADO:

El botón "Eliminar" en el dashboard del moderador muestra "Acción no reconocida" porque el router está llamando al método incorrecto.

## ✅ SOLUCIÓN:

### **Archivo a modificar:** `index.php` (en producción)

### **Línea a cambiar:** Aproximadamente línea 275

**❌ Código actual (incorrecto):**
```php
case 'moderator/delete-cpc':
    checkAccess(2);
    $controller = loadController('ModeratorController');
    $controller->manageCPCs();  // ❌ INCORRECTO
```

**✅ Código corregido:**
```php
case 'moderator/delete-cpc':
    checkAccess(2);
    $controller = loadController('ModeratorController');
    $controller->deleteCPC();  // ✅ CORRECTO
```

## 📋 INSTRUCCIONES PARA APLICAR EN PRODUCCIÓN:

1. **Acceder al servidor de producción**
2. **Abrir el archivo `index.php`**
3. **Buscar la línea que contiene:** `case 'moderator/delete-cpc':`
4. **Cambiar:** `$controller->manageCPCs();` por `$controller->deleteCPC();`
5. **Guardar el archivo**
6. **Probar la funcionalidad de eliminación**

## 🎯 RESULTADO ESPERADO:

Después de aplicar este cambio:
- ✅ El botón "Eliminar" funcionará correctamente
- ✅ Se mostrará el alert: "CPC eliminado exitosamente"
- ✅ El CPC desaparecerá de la lista
- ✅ No más mensaje "Acción no reconocida"

## ⚠️ IMPORTANTE:

Este archivo (`index.php`) está ignorado por Git, por lo que el cambio debe aplicarse manualmente en producción.
