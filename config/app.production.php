<?php
/**
 * Configuración de la aplicación para PRODUCCIÓN
 * 
 * Este archivo contiene las configuraciones para el entorno de producción.
 * Para cambiar a producción, simplemente renombra este archivo a app.php
 */

// Configuración de la URL base para PRODUCCIÓN
define('BASE_URL', '/');

// Otras configuraciones de la aplicación
define('APP_NAME', 'Simulador de Subasta Inversa Electrónica');
define('APP_VERSION', '1.0.0');

// Configuración de entorno
define('ENVIRONMENT', 'production'); // 'development' o 'production'

// Configuración de debug - SIEMPRE false en producción
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