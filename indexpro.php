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
error_log("REQUEST_URI: " . $_SERVER['REQUEST_URI']);
error_log("QUERY_STRING: " . $_SERVER['QUERY_STRING']);
error_log("GET params: " . print_r($_GET, true));
error_log("Is AJAX: " . (isAjaxRequest() ? 'YES' : 'NO'));

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

        case 'view_file':
            // Servir archivos estáticos (uploads)
            error_log("VIEW_FILE START - Processing file request");
            $filePath = $_GET['path'] ?? '';
            error_log("VIEW_FILE - File path from GET: " . $filePath);
            
            if (empty($filePath)) {
                error_log("VIEW_FILE ERROR - No file path specified");
                http_response_code(400);
                echo "Archivo no especificado";
                exit;
            }
            
            // Validar que el archivo esté dentro del directorio uploads
            $fullPath = __DIR__ . '/' . $filePath;
            $uploadsDir = __DIR__ . '/uploads/';
            error_log("VIEW_FILE - Full path: " . $fullPath);
            error_log("VIEW_FILE - Uploads dir: " . $uploadsDir);
            
            // Verificar que el archivo esté dentro del directorio uploads
            $realFullPath = realpath($fullPath);
            $realUploadsDir = realpath($uploadsDir);
            error_log("VIEW_FILE - Real full path: " . ($realFullPath ?: 'FALSE'));
            error_log("VIEW_FILE - Real uploads dir: " . ($realUploadsDir ?: 'FALSE'));
            
            if (!$realFullPath || !$realUploadsDir || strpos($realFullPath, $realUploadsDir) !== 0) {
                error_log("VIEW_FILE ERROR - Access denied. RealFullPath: " . ($realFullPath ?: 'FALSE') . ", RealUploadsDir: " . ($realUploadsDir ?: 'FALSE'));
                http_response_code(403);
                echo "Acceso denegado";
                exit;
            }
            
            // Verificar que el archivo existe
            if (!file_exists($fullPath)) {
                error_log("VIEW_FILE ERROR - File does not exist: " . $fullPath);
                http_response_code(404);
                echo "Archivo no encontrado";
                exit;
            }
            
            error_log("VIEW_FILE - File exists, proceeding to serve");
            
            // Determinar el tipo MIME
            $mimeType = mime_content_type($fullPath);
            if (!$mimeType) {
                $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
                switch ($extension) {
                    case 'pdf':
                        $mimeType = 'application/pdf';
                        break;
                    case 'jpg':
                    case 'jpeg':
                        $mimeType = 'image/jpeg';
                        break;
                    case 'png':
                        $mimeType = 'image/png';
                        break;
                    default:
                        $mimeType = 'application/octet-stream';
                }
            }
            
            // Establecer headers para servir el archivo
            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . filesize($fullPath));
            header('Content-Disposition: inline; filename="' . basename($fullPath) . '"');
            header('Cache-Control: private, max-age=3600');
            
            // Leer y enviar el archivo
            readfile($fullPath);
            exit;

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

        case 'admin_get_unanswered_questions':
            checkAccess(1);
            $controller = loadController('AdminController');
            $controller->getUnansweredQuestions();
            break;

        case 'admin_answer_questions':
            checkAccess(1);
            $controller = loadController('AdminController');
            $controller->answerQuestions();
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

        case 'participant_submit_question':
            checkAccess(3);
            $controller = loadController('ParticipantController');
            $controller->submitQuestion();
            break;

        case 'participant_get_questions':
            checkAccess(3);
            $controller = loadController('ParticipantController');
            $controller->getQuestions();
            break;

        case 'participant_upload_offer':
            checkAccess(3);
            $controller = loadController('ParticipantController');
            $controller->uploadOffer();
            break;

        case 'participant_get_offers':
            checkAccess(3);
            $controller = loadController('ParticipantController');
            $controller->getOffers();
            break;

        case 'participant_delete_offer':
            checkAccess(3);
            $controller = loadController('ParticipantController');
            $controller->deleteOffer();
            break;

        case 'participant_process_offer':
            checkAccess(3);
            $controller = loadController('ParticipantController');
            $controller->processOffer();
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
            
        case 'moderator_edit_product':
            checkAccess(2);
            $id = $_GET['id'] ?? null;
            if ($id) {
                $controller = loadController('ModeratorController');
                $controller->editProduct($id);
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
            $controller->deleteCPC();
            break;

        case 'moderator_get_unanswered_questions':
            checkAccess(2);
            $controller = loadController('ModeratorController');
            $controller->getUnansweredQuestions();
            break;

        case 'moderator_answer_questions':
            checkAccess(2);
            $controller = loadController('ModeratorController');
            $controller->answerQuestions();
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
