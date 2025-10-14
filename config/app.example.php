<?php
/**
 * Configuración de la aplicación - ARCHIVO DE EJEMPLO
 * 
 * Copia este archivo como 'app.php' y configura según tu entorno:
 * - Desarrollo local: BASE_URL = '/subs/'
 * - Producción: BASE_URL = '/'
 */

// Configuración de la URL base
// Para desarrollo local: '/subs/'
// Para producción: '/' (cambiar manualmente al desplegar)
define('BASE_URL', '/subs/'); // ← CAMBIAR SEGÚN ENTORNO

// Otras configuraciones de la aplicación
define('APP_NAME', 'Simulador de Subasta Inversa Electrónica');
define('APP_VERSION', '1.0.0');

// Configuración de entorno
define('ENVIRONMENT', 'development'); // 'development' o 'production'

// Configuración de debug
define('DEBUG', false); // Cambiar a true solo cuando necesites debug

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

