<?php
/**
 * Configuración de la aplicación - PRODUCCIÓN
 * 
 * Este archivo contiene las configuraciones para el entorno de producción.
 * Se usa temporalmente durante el despliegue y luego se elimina.
 */

// Configuración de la URL base para PRODUCCIÓN
define('BASE_URL', '/');

// Otras configuraciones de la aplicación
define('APP_NAME', 'Simulador de Subasta Inversa Electrónica');
define('APP_VERSION', '1.0.0');

// Configuración de entorno
define('ENVIRONMENT', 'production');

// Configuración de debug - DESHABILITADO en producción
define('DEBUG', false);

// Configuración de rutas - NUEVA ARQUITECTURA (query parameters)
define('LOGIN_URL', BASE_URL . 'index.php?action=login');
define('UNAUTHORIZED_URL', BASE_URL . 'index.php?action=unauthorized');
define('LOGOUT_URL', BASE_URL . 'index.php?action=logout');

// URLs de redirección por rol - NUEVA ARQUITECTURA (query parameters)
define('ADMIN_DASHBOARD_URL', BASE_URL . 'index.php?action=admin_dashboard');
define('MODERATOR_DASHBOARD_URL', BASE_URL . 'index.php?action=moderator_dashboard');
define('PARTICIPANT_DASHBOARD_URL', BASE_URL . 'index.php?action=participant_dashboard');

// URL para assets estáticos
define('ASSETS_URL', BASE_URL . 'public/');
define('CSS_URL', ASSETS_URL . 'css/');
define('JS_URL', ASSETS_URL . 'js/');
define('IMAGES_URL', ASSETS_URL . 'images/');
