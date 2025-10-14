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
 * Compatible con ambos sistemas (legacy y nuevo)
 * @param string $path Ruta relativa
 * @return string URL completa
 */
function url($path = '') {
    $path = ltrim($path, '/');
    
    // Detectar si estamos en el nuevo sistema (producción con query parameters)
    // Si ENVIRONMENT está definido como 'production' y no estamos en localhost
    $isProduction = (defined('ENVIRONMENT') && ENVIRONMENT === 'production');
    $isLocalhost = (isset($_SERVER['HTTP_HOST']) && 
                   (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
                    strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false));
    
    if ($isProduction && !$isLocalhost) {
        // Sistema nuevo - convertir rutas legacy a query parameters
        return convertToQueryParams($path);
    } else {
        // Sistema legacy - URLs amigables
        return BASE_URL . $path;
    }
}

/**
 * Convierte rutas legacy a formato de query parameters
 * @param string $path Ruta legacy
 * @return string URL con query parameters
 */
function convertToQueryParams($path) {
    if (empty($path)) {
        return BASE_URL;
    }
    
    // Mapeo de rutas legacy a acciones
    $pathParts = explode('/', $path);
    $role = $pathParts[0] ?? '';
    $action = $pathParts[1] ?? '';
    $id = $pathParts[2] ?? '';
    
    // Construir la acción para query parameter
    $queryAction = '';
    
    if ($role && $action) {
        $queryAction = $role . '_' . str_replace('-', '_', $action);
    } elseif ($role) {
        $queryAction = $role;
    }
    
    // Construir URL con query parameters
    $url = BASE_URL . 'index.php?action=' . $queryAction;
    
    if ($id) {
        $url .= '&id=' . urlencode($id);
    }
    
    return $url;
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

/**
 * Función para redirigir a una URL
 * @param string $url URL a la que redirigir
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}
