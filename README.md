# Sistema de Simulación de Contratación Pública

Simulador Educativo de Subastas Inversas Electrónicas.

Dominio de producción: `https://sie.hjconsulting.com.ec/`

## Arquitectura

- Routing por query parameters en producción: `index.php?action=...`
- Compatible con desarrollo local bajo `/subs/` (URLs amigables + `.htaccess`)
- Las URLs de JavaScript se generan con `public/js/url-helper.js` (`generateUrl` / `getAppBasePath`)
- **No hardcodear** `/subs/` en JS ni vistas

## Instalación

### Producción (`sie.hjconsulting.com.ec`)

```bash
git pull origin master

# index.php y config/app.php ya vienen versionados para producción (BASE_URL = '/')

# Credenciales DB (NO versionadas): crear solo si no existe
cp config/database.example.php config/database.php
# Editar config/database.php con usuario/password del hosting
```

Checklist post-deploy:

1. `config/app.php` → `BASE_URL = '/'`, `ENVIRONMENT = 'production'`
2. `config/database.php` → credenciales del hosting (archivo local en servidor)
3. `index.php` presente (espejo de `indexpro.php`)
4. Hard refresh del navegador (JS versionado con `?v=20260722`)

### Desarrollo local

```bash
cp config/app.local.example.php config/app.php
cp config/database.example.php config/database.php
# Editar database.php (ej. root sin password en XAMPP)
# Configurar .htaccess con RewriteBase /subs/
```

## Archivos versionados vs ignorados

| Archivo | ¿En Git? | Notas |
|---|---|---|
| `index.php` / `indexpro.php` | Sí | Producción usa `index.php` |
| `config/app.php` | Sí | Config de producción (`BASE_URL=/`) |
| `config/app.local.example.php` | Sí | Plantilla local |
| `config/database.example.php` | Sí | Plantilla sin secretos |
| `config/database.php` | **No** | Secretos; crear en cada entorno |
| `uploads/` | **No** | Archivos de usuarios |

## Módulo Prácticas de Puja

Módulo **separado** del proceso completo para entrenamiento multiusuario en vivo.

### Deploy de tablas (obligatorio una vez)

```bash
# En MySQL / phpMyAdmin ejecutar:
migrations/create_practicas_puja_tables.sql
```

### Rutas

| Rol | Acción | URL |
|---|---|---|
| Admin | Banco de salas | `/index.php?action=admin_training_dashboard` |
| Admin | Crear sala | `/index.php?action=admin_training_create_sala` |
| Participante | Listado prácticas | `/index.php?action=participant_training_list` |
| Participante | Join + oferta inicial | `/index.php?action=participant_training_join&id=RONDA_ID` |
| Participante | Ventana puja | `/index.php?action=participant_training_puja&id=RONDA_ID` |

### Checklist smoke test

1. Admin crea sala activa (presupuesto + variación + duración)
2. Admin abre ronda con fecha/hora de inicio
3. Dos participantes ingresan con oferta inicial ≤ presupuesto
4. Compiten en vivo; rechazo si no mejora el mejor valor
5. Al cerrar: resumen + badge ganador; nueva ronda no mezcla historial

## URLs principales (producción)

| Función | URL |
|---|---|
| Login | `/index.php?action=login` |
| Admin | `/index.php?action=admin_dashboard` |
| Moderador | `/index.php?action=moderator_dashboard` |
| Participante | `/index.php?action=participant_dashboard` |
| Fase PyR | `/index.php?action=participant_phase&phase=pyr&producto_id=ID` |
| Prácticas (admin) | `/index.php?action=admin_training_dashboard` |
| Prácticas (participante) | `/index.php?action=participant_training_list` |

## Troubleshooting

**Fases / AJAX con HTTP 404 a `/subs/...`**  
Causa: JS antiguo con `/subs/` quemado o caché del navegador.  
Solución: desplegar `url-helper.js` + `unified-tabs.js` y hard refresh.

**Sitio sin CSS**  
Causa: `BASE_URL` en `config/app.php` apunta a `/subs/` en un dominio raíz.  
Solución: `BASE_URL = '/'` en producción.

**Scripts de fase no corren**  
Causa: HTML inyectado con `innerHTML` sin ejecutar `<script>`.  
Solución: `executeInlineScripts` en `unified-tabs.js`.

## Documentación

Ver `DOCUMENTACION_PROYECTO.md` para detalle funcional y de base de datos.

**Versión:** 2.1 - URLs dinámicas multi-entorno  
**Última actualización:** Julio 2026
