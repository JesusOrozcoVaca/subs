<?php
/**
 * Funciones helper para URLs
 * 
 * Este archivo contiene funciones helper para generar URLs
 * que están disponibles en todas las vistas y controladores.
 */

// Asegurar que las constantes estén definidas
if (!defined('BASE_URL')) {
    require_once BASE_PATH . '/config/app.php';
}

/**
 * Función helper para generar URLs completas
 * @param string $path Ruta relativa
 * @return string URL completa
 */
function url($path = '') {
    return BASE_URL . ltrim($path, '/');
}

/**
 * Función helper para generar URLs de assets
 * @param string $path Ruta del asset
 * @return string URL del asset
 */
function asset($path) {
    return ASSETS_URL . ltrim($path, '/');
}

/**
 * Función helper para generar URLs de CSS
 * @param string $path Ruta del archivo CSS
 * @return string URL del CSS
 */
function css($path) {
    return CSS_URL . ltrim($path, '/');
}

/**
 * Función helper para generar URLs de JavaScript
 * @param string $path Ruta del archivo JS
 * @return string URL del JS
 */
function js($path) {
    return JS_URL . ltrim($path, '/');
}

/**
 * Función helper para generar URLs de imágenes
 * @param string $path Ruta de la imagen
 * @return string URL de la imagen
 */
function image($path) {
    return IMAGES_URL . ltrim($path, '/');
}

/**
 * Función helper para generar URLs de logout
 * @return string URL de logout
 */
function logout_url() {
    return url('logout');
}

/**
 * Función helper para generar URLs de login
 * @return string URL de login
 */
function login_url() {
    return url('login');
}

/**
 * Función helper para generar URLs de dashboard según el rol
 * @param int $level Nivel de acceso del usuario
 * @return string URL del dashboard correspondiente
 */
function dashboard_url($level = null) {
    if ($level === null && isset($_SESSION['nivel_acceso'])) {
        $level = $_SESSION['nivel_acceso'];
    }
    
    switch ($level) {
        case 1:
            return url('admin/dashboard');
        case 2:
            return url('moderator/dashboard');
        case 3:
        default:
            return url('participant/dashboard');
    }
}

/**
 * Función helper para generar URLs de perfil
 * @return string URL del perfil del usuario
 */
function profile_url() {
    if (isset($_SESSION['nivel_acceso'])) {
        switch ($_SESSION['nivel_acceso']) {
            case 1:
                return url('admin/profile');
            case 2:
                return url('moderator/profile');
            case 3:
            default:
                return url('participant/profile');
        }
    }
    return url('login');
}
