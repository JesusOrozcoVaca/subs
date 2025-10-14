<?php
/**
 * Configuración de la aplicación - PRODUCCIÓN
 * 
 * Este es el archivo de configuración real para producción
 */

// Configuración de la URL base para PRODUCCIÓN
define('BASE_URL', '/'); // ← PRODUCCIÓN: sin subdirectorio

// Otras configuraciones de la aplicación
define('APP_NAME', 'Simulador de Subasta Inversa Electrónica');
define('APP_VERSION', '1.0.0');

// Configuración de entorno
define('ENVIRONMENT', 'production');

// Configuración de debug - DESHABILITADO en producción
define('DEBUG', false);

// Configuración de rutas
define('LOGIN_URL', BASE_URL . 'login');
define('UNAUTHORIZED_URL', BASE_URL . 'unauthorized');

// URLs de redirección por rol
define('ADMIN_DASHBOARD_URL', BASE_URL . 'admin/dashboard');
define('MODERATOR_DASHBOARD_URL', BASE_URL . 'moderator/dashboard');
define('PARTICIPANT_DASHBOARD_URL', BASE_URL . 'participant/dashboard');

// URL para assets estáticos
define('ASSETS_URL', BASE_URL . 'public/');
define('CSS_URL', ASSETS_URL . 'css/');
define('JS_URL', ASSETS_URL . 'js/');
define('IMAGES_URL', ASSETS_URL . 'images/');
