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
            
            // Verificar que el directorio uploads existe
            if (!is_dir($uploadsDir)) {
                error_log("VIEW_FILE ERROR - Uploads directory does not exist: " . $uploadsDir);
                http_response_code(500);
                echo "Directorio uploads no existe";
                exit;
            }
            
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
            
            // Verificar que es un archivo (no directorio)
            if (!is_file($fullPath)) {
                error_log("VIEW_FILE ERROR - Path is not a file: " . $fullPath);
                http_response_code(400);
                echo "Ruta no es un archivo válido";
                exit;
            }
            
            error_log("VIEW_FILE - File exists and is valid, proceeding to serve");
            
            // Determinar el tipo MIME
            $mimeType = mime_content_type($fullPath);
            if (!$mimeType) {
                $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
                error_log("VIEW_FILE - Extension detected: " . $extension);
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
            
            error_log("VIEW_FILE - MIME type: " . $mimeType);
            error_log("VIEW_FILE - File size: " . filesize($fullPath) . " bytes");
            
            // Limpiar cualquier output buffer
            if (ob_get_level()) {
                ob_clean();
            }
            
            // Establecer headers para servir el archivo
            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . filesize($fullPath));
            header('Content-Disposition: inline; filename="' . basename($fullPath) . '"');
            header('Cache-Control: private, max-age=3600');
            
            error_log("VIEW_FILE - Headers set, reading file");
            
            // Leer y enviar el archivo
            $result = readfile($fullPath);
            if ($result === false) {
                error_log("VIEW_FILE ERROR - Failed to read file");
                http_response_code(500);
                echo "Error al leer el archivo";
                exit;
            }
            
            error_log("VIEW_FILE - File served successfully");
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

        // Rutas Admin - Prácticas de Puja
        case 'admin_training_dashboard':
            checkAccess(1);
            $controller = loadController('AdminTrainingController');
            $controller->dashboard();
            break;

        case 'admin_training_create_sala':
            checkAccess(1);
            $controller = loadController('AdminTrainingController');
            $controller->createSala();
            break;

        case 'admin_training_edit_sala':
            checkAccess(1);
            $controller = loadController('AdminTrainingController');
            $controller->editSala($_GET['id'] ?? null);
            break;

        case 'admin_training_view_sala':
            checkAccess(1);
            $controller = loadController('AdminTrainingController');
            $controller->viewSala($_GET['id'] ?? null);
            break;

        case 'admin_training_create_ronda':
            checkAccess(1);
            $controller = loadController('AdminTrainingController');
            $controller->createRonda($_POST['sala_id'] ?? $_GET['sala_id'] ?? null);
            break;

        case 'admin_training_cancel_ronda':
            checkAccess(1);
            $controller = loadController('AdminTrainingController');
            $controller->cancelRonda($_POST['id'] ?? $_GET['id'] ?? null);
            break;

        case 'admin_training_close_ronda':
            checkAccess(1);
            $controller = loadController('AdminTrainingController');
            $controller->closeRonda($_POST['id'] ?? $_GET['id'] ?? null);
            break;

        case 'admin_training_ronda_detail':
            checkAccess(1);
            $controller = loadController('AdminTrainingController');
            $controller->rondaDetail($_GET['id'] ?? null);
            break;

        case 'admin_training_toggle_inscripcion':
            checkAccess(1);
            $controller = loadController('AdminTrainingController');
            $controller->toggleInscripcion();
            break;

        case 'admin_training_ronda_status':
            checkAccess(1);
            $controller = loadController('AdminTrainingController');
            $controller->rondaStatus($_GET['id'] ?? null);
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

        case 'participant_puja':
            checkAccess(3);
            $id = $_GET['id'] ?? null;
            if ($id) {
                $controller = loadController('ParticipantController');
                $controller->pujaWindow($id);
            } else {
                throw new Exception("ID requerido");
            }
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

        case 'participant_get_offer_rating':
            checkAccess(3);
            $controller = loadController('ParticipantController');
            $controller->getOfferRating();
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

        case 'participant_submit_bid':
            checkAccess(3);
            $controller = loadController('ParticipantController');
            $controller->submitBid();
            break;

        case 'participant_puja_status':
            checkAccess(3);
            $controller = loadController('ParticipantController');
            $controller->getPujaStatus();
            break;

        case 'participant_submit_initial_offer':
            checkAccess(3);
            $controller = loadController('ParticipantController');
            $controller->submitInitialOffer();
            break;

        case 'participant_submit_convalidation':
            checkAccess(3);
            $controller = loadController('ParticipantController');
            $controller->submitConvalidation();
            break;

        case 'participant_get_convalidation':
            checkAccess(3);
            $controller = loadController('ParticipantController');
            $controller->getConvalidation();
            break;

        case 'participant_download_convalidation_pdf':
            checkAccess(3);
            $controller = loadController('ParticipantController');
            $controller->downloadConvalidationPdf();
            break;

        case 'participant_download_offer_pdf':
            checkAccess(3);
            $controller = loadController('ParticipantController');
            $controller->downloadOfferPdf();
            break;

        // Rutas Participante - Prácticas de Puja
        case 'participant_training_list':
            checkAccess(3);
            $controller = loadController('ParticipantTrainingController');
            $controller->listPractices();
            break;

        case 'participant_training_join':
            checkAccess(3);
            $controller = loadController('ParticipantTrainingController');
            $controller->join($_GET['id'] ?? $_POST['ronda_id'] ?? null);
            break;

        case 'participant_training_puja':
            checkAccess(3);
            $controller = loadController('ParticipantTrainingController');
            $controller->pujaWindow($_GET['id'] ?? null);
            break;

        case 'participant_training_submit_bid':
            checkAccess(3);
            $controller = loadController('ParticipantTrainingController');
            $controller->submitBid();
            break;

        case 'participant_training_puja_status':
            checkAccess(3);
            $controller = loadController('ParticipantTrainingController');
            $controller->pujaStatus($_GET['id'] ?? $_GET['ronda_id'] ?? null);
            break;

        case 'participant_training_summary':
            checkAccess(3);
            $controller = loadController('ParticipantTrainingController');
            $controller->summary($_GET['id'] ?? null);
            break;

        case 'participant_training_history':
            checkAccess(3);
            $controller = loadController('ParticipantTrainingController');
            $controller->history();
            break;

        case 'participant_training_history_detail':
            checkAccess(3);
            $controller = loadController('ParticipantTrainingController');
            $controller->historyDetail($_GET['id'] ?? null);
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

        case 'moderator_get_offer_ratings':
            checkAccess(2);
            $controller = loadController('ModeratorController');
            $controller->getOfferRatings();
            break;

        case 'moderator_save_offer_rating':
            checkAccess(2);
            $controller = loadController('ModeratorController');
            $controller->saveOfferRating();
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
