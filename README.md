# Sistema de Simulación de Contratación Pública

## 🚀 **Nueva Arquitectura Implementada**

Este proyecto utiliza una **nueva arquitectura de routing** que resuelve problemas de interferencia con WordPress y servidores compartidos.

### **📋 Características Principales**

- ✅ **Sin interferencia de WordPress**
- ✅ **Sin dependencia de `.htaccess` en producción**
- ✅ **Routing robusto con query parameters**
- ✅ **Compatible con desarrollo local**

### **🔧 Instalación Rápida**

#### **Para Producción:**
```bash
git clone [tu-repo]
cd subs
mv index_new.php index.php
# Configurar config/app.php y config/database.php
```

#### **Para Desarrollo Local:**
```bash
git clone [tu-repo]
cd subs
cp config/app_local.php config/app.php
cp config/database.example.php config/database.php
# Configurar .htaccess para desarrollo local
```

### **📖 URLs del Sistema**

| Función | URL |
|---|---|
| **Login** | `/index.php?action=login` |
| **Admin Dashboard** | `/index.php?action=admin_dashboard` |
| **Moderator Dashboard** | `/index.php?action=moderator_dashboard` |
| **Participant Dashboard** | `/index.php?action=participant_dashboard` |

### **📚 Documentación Completa**

Ver `NUEVA_ARQUITECTURA.md` para documentación detallada sobre:
- Configuración por entorno
- Mapeo completo de URLs
- Mantenimiento y troubleshooting

### **🧪 Verificación rápida de fases**

- Las fases con scripts embebidos (ej. `conv`, `eof`) se cargan vía AJAX en `participant/view-product`.
- El loader `public/js/unified-tabs.js` debe ejecutar los `<script>` inline del contenido inyectado.
- Verificación por código: actualmente solo `conv.php` y `eof.php` incluyen `<script>` y ambos se cargan desde `unified-tabs.js`.

### **🛠️ Troubleshooting**

**Síntoma:** al cargar una fase, el formulario no intercepta el submit, los inputs no actualizan y el envío redirige al detalle del proceso.  
**Causa:** el HTML de la fase se inyecta con `innerHTML` y los `<script>` inline no se ejecutan.  
**Solución:** ejecutar scripts inline después de inyectar el contenido en `public/js/unified-tabs.js` (función `executeInlineScripts`).  

**Nota:** si el navegador cachea `unified-tabs.js`, limpiar caché o usar un query param de versión en `views/participant/part_view_product.php`.

### **⚙️ Configuración**

Los archivos de configuración se manejan por entorno:
- `config/app.php` - Configuración principal (ignorado por Git)
- `config/database.php` - Base de datos (ignorado por Git)
- `.htaccess` - Servidor web (ignorado por Git)

**Ver archivos `.example` para plantillas de configuración.**

---

**Versión:** 2.0 - Nueva Arquitectura  
**Última actualización:** Enero 2025
