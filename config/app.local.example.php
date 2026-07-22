<?php
/**
 * Plantilla de configuración LOCAL (Windows/XAMPP u otro).
 *
 * Para desarrollo local:
 *   cp config/app.local.example.php config/app.php
 *
 * En producción se usa config/app.php versionado con BASE_URL = '/'.
 */

define('BASE_URL', '/subs/');

define('APP_NAME', 'Simulador de Subasta Inversa Electrónica');
define('APP_VERSION', '1.0.0');

define('ENVIRONMENT', 'development');
define('DEBUG', false);

define('LOGIN_URL', BASE_URL . 'login');
define('UNAUTHORIZED_URL', BASE_URL . 'unauthorized');
define('LOGOUT_URL', BASE_URL . 'logout');

define('ADMIN_DASHBOARD_URL', BASE_URL . 'admin/dashboard');
define('MODERATOR_DASHBOARD_URL', BASE_URL . 'moderator/dashboard');
define('PARTICIPANT_DASHBOARD_URL', BASE_URL . 'participant/dashboard');

define('ASSETS_URL', BASE_URL . 'public/');
define('CSS_URL', ASSETS_URL . 'css/');
define('JS_URL', ASSETS_URL . 'js/');
define('IMAGES_URL', ASSETS_URL . 'images/');
