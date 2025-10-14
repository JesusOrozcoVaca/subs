<?php
/**
 * Configuración de la aplicación - DESARROLLO LOCAL
 * 
 * Este archivo mantiene la compatibilidad con el sistema legacy para desarrollo local
 */

// Configuración de la URL base para DESARROLLO LOCAL
define('BASE_URL', '/subs/'); // ← DESARROLLO LOCAL: con subdirectorio

// Otras configuraciones de la aplicación
define('APP_NAME', 'Simulador de Subasta Inversa Electrónica');
define('APP_VERSION', '1.0.0');

// Configuración de entorno
define('ENVIRONMENT', 'development');

// Configuración de debug
define('DEBUG', false);

// Configuración de rutas - SISTEMA LEGACY para desarrollo local
define('LOGIN_URL', BASE_URL . 'login');
define('UNAUTHORIZED_URL', BASE_URL . 'unauthorized');

// URLs de redirección por rol - SISTEMA LEGACY
define('ADMIN_DASHBOARD_URL', BASE_URL . 'admin/dashboard');
define('MODERATOR_DASHBOARD_URL', BASE_URL . 'moderator/dashboard');
define('PARTICIPANT_DASHBOARD_URL', BASE_URL . 'participant/dashboard');

// URL para assets estáticos
define('ASSETS_URL', BASE_URL . 'public/');
define('CSS_URL', ASSETS_URL . 'css/');
define('JS_URL', ASSETS_URL . 'js/');
define('IMAGES_URL', ASSETS_URL . 'images/');
