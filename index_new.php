<?php 
// Nueva arquitectura de routing - Sistema autónomo
// Compatible con query parameters y sin dependencia de .htaccess

// Verificación segura de output buffer
if (ob_get_level()) {
    ob_clean();
}

// Manejo de errores más agresivo para evitar interferencia externa
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 0);

session_start();

define('BASE_PATH', dirname(__FILE__));

// Cargar configuración de la aplicación
require_once BASE_PATH . '/config/app.php';

function loadController($controllerName) {
    $controllerFile = BASE_PATH . "/controllers/{$controllerName}.php";
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        return new $controllerName();
    } else {
        throw new Exception("Controller not found: {$controllerName}");
    }
}

function checkAccess($requiredLevel) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['nivel_acceso'])) {
        header('Location: ' . getAppUrl('login'));
        exit();
    }
    if ($_SESSION['nivel_acceso'] < $requiredLevel) {
        header('Location: ' . getAppUrl('unauthorized'));
        exit();
    }
}

function isAjaxRequest() {
    return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ||
           (!empty($_SERVER['HTTP_ACCEPT']) && 
            strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
}

// Nueva función para generar URLs
function getAppUrl($action, $params = []) {
    // En producción, el archivo se renombra a index.php
    $indexFile = (defined('ENVIRONMENT') && ENVIRONMENT === 'production') ? 'index.php' : 'index_new.php';
    $url = BASE_URL . $indexFile . '?action=' . $action;
    if (!empty($params)) {
        $url .= '&' . http_build_query($params);
    }
    return $url;
}

// Obtener la acción desde query parameters
$action = $_GET['action'] ?? 'login';

// Log de routing para debugging
error_log("NEW ROUTING - Action: $action, Session ID: " . session_id());

try {
    switch ($action) {
        case 'login':
            $controller = loadController('AuthController');
            $controller->login();
            break;

        case 'logout':
            $controller = loadController('AuthController');
            $controller->logout();
            break;

        // Rutas del Administrador
        case 'admin_dashboard':
            checkAccess(1);
            $controller = loadController('AdminController');
            $controller->dashboard();
            break;

        case 'admin_create_product':
            checkAccess(1);
            $controller = loadController('AdminController');
            $controller->createProduct();
            break;

        case 'admin_create_user':
            checkAccess(1);
            $controller = loadController('AdminController');
            $controller->createUser();
            break;

        case 'admin_create_cpc':
            checkAccess(1);
            $controller = loadController('AdminController');
            $controller->createCPC();
            break;

        case 'admin_edit_user':
            checkAccess(1);
            $id = $_GET['id'] ?? null;
            if ($id) {
                $controller = loadController('AdminController');
                $controller->editUser($id);
            } else {
                throw new Exception("ID requerido");
            }
            break;

        case 'admin_edit_product':
            checkAccess(1);
            $id = $_GET['id'] ?? null;
            if ($id) {
                $controller = loadController('AdminController');
                $controller->editProduct($id);
            } else {
                throw new Exception("ID requerido");
            }
            break;

        case 'admin_edit_cpc':
            checkAccess(1);
            $id = $_GET['id'] ?? null;
            if ($id) {
                $controller = loadController('AdminController');
                $controller->editCPC($id);
            } else {
                throw new Exception("ID requerido");
            }
            break;

        case 'admin_toggle_user_status':
            checkAccess(1);
            $controller = loadController('AdminController');
            $controller->toggleUserStatus();
            break;

        case 'admin_manage_product':
            checkAccess(1);
            $id = $_GET['id'] ?? null;
            if ($id) {
                $controller = loadController('AdminController');
                $controller->manageProduct($id);
            } else {
                throw new Exception("ID requerido");
            }
            break;

        case 'admin_delete_user':
            checkAccess(1);
            $controller = loadController('AdminController');
            $controller->deleteUser();
            break;

        case 'admin_delete_product':
            checkAccess(1);
            $controller = loadController('AdminController');
            $controller->deleteProduct();
            break;

        case 'admin_delete_cpc':
            checkAccess(1);
            $controller = loadController('AdminController');
            $controller->deleteCPC();
            break;

        // Rutas del Participante
        case 'participant_dashboard':
            checkAccess(3);
            $controller = loadController('ParticipantController');
            $controller->dashboard();
            break;

        case 'participant_view_product':
            checkAccess(3);
            $id = $_GET['id'] ?? null;
            if ($id) {
                $controller = loadController('ParticipantController');
                $controller->viewProduct($id);
            } else {
                throw new Exception("ID requerido");
            }
            break;

        case 'participant_profile':
            checkAccess(3);
            $controller = loadController('ParticipantController');
            $controller->profile();
            break;

        case 'participant_search_process':
            checkAccess(3);
            $controller = loadController('ParticipantController');
            $controller->searchProcess();
            break;

        case 'participant_add_cpc':
            checkAccess(3);
            $controller = loadController('ParticipantController');
            $controller->addCpc();
            break;

        case 'participant_remove_cpc':
            checkAccess(3);
            $controller = loadController('ParticipantController');
            $controller->removeCpc();
            break;

        case 'participant_phase':
            checkAccess(3);
            $phase = $_GET['phase'] ?? null;
            if ($phase) {
                $controller = loadController('ParticipantController');
                $controller->loadPhaseContent($phase);
            } else {
                throw new Exception("Fase requerida");
            }
            break;

        // Rutas del Moderador
        case 'moderator_dashboard':
            checkAccess(2);
            $controller = loadController('ModeratorController');
            $controller->dashboard();
            break;

        case 'moderator_manage_product':
            checkAccess(2);
            $id = $_GET['id'] ?? null;
            if ($id) {
                $controller = loadController('ModeratorController');
                $controller->manageProduct($id);
            } else {
                throw new Exception("ID requerido");
            }
            break;

        case 'moderator_manage_cpcs':
            checkAccess(2);
            $controller = loadController('ModeratorController');
            $controller->manageCPCs();
            break;

        case 'moderator_edit_cpc':
            checkAccess(2);
            $id = $_GET['id'] ?? null;
            if ($id) {
                $controller = loadController('ModeratorController');
                $controller->editCPC($id);
            } else {
                throw new Exception("ID requerido");
            }
            break;

        case 'moderator_manage_questions':
            checkAccess(2);
            $id = $_GET['id'] ?? null;
            if ($id) {
                $controller = loadController('ModeratorController');
                $controller->manageQuestions($id);
            } else {
                throw new Exception("ID requerido");
            }
            break;

        case 'moderator_evaluate_participants':
            checkAccess(2);
            $id = $_GET['id'] ?? null;
            if ($id) {
                $controller = loadController('ModeratorController');
                $controller->evaluateParticipants($id);
            } else {
                throw new Exception("ID requerido");
            }
            break;

        case 'moderator_delete_cpc':
            checkAccess(2);
            $controller = loadController('ModeratorController');
            $controller->manageCPCs();
            break;

        default:
            // Página 404 o redirigir a login
            if (isAjaxRequest()) {
                http_response_code(404);
                echo json_encode(['error' => 'Acción no encontrada']);
            } else {
                header('Location: ' . getAppUrl('login'));
            }
            break;
    }
} catch (Exception $e) {
    error_log("EXCEPTION: " . $e->getMessage());
    
    if (isAjaxRequest()) {
        http_response_code(500);
        echo json_encode(['error' => 'Error interno del servidor']);
    } else {
        require_once BASE_PATH . '/views/error.php';
    }
}
?>
