<?php 
// Iniciar output buffering al principio
ob_start();

// Deshabilitar errores de WordPress que interfieren
error_reporting(0); // Deshabilitar TODOS los errores
ini_set('display_errors', 0);
ini_set('log_errors', 0); // También deshabilitar logs de errores
ini_set('error_log', '/dev/null'); // Enviar errores a /dev/null

session_start();

// Función para manejar errores de WordPress
function handleWordPressError($errno, $errstr, $errfile, $errline) {
    // Ignorar errores de WordPress
    if (strpos($errfile, 'wp-includes') !== false || 
        strpos($errfile, 'wordpress') !== false ||
        strpos($errstr, 'dt_theme') !== false ||
        strpos($errstr, 'call_user_func_array') !== false) {
        return true; // Ignorar el error
    }
    return false; // Permitir que PHP maneje otros errores
}

set_error_handler('handleWordPressError');

define('BASE_PATH', dirname(__FILE__));

// Cargar configuración de la aplicación
require_once BASE_PATH . '/config/app.php';

// Debug comentado para producción
/*
if (DEBUG) {
    echo "BASE_PATH: " . BASE_PATH . "<br>";
    echo "Current file: " . __FILE__ . "<br>";
    echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "<br>";
    echo "QUERY_STRING: " . $_SERVER['QUERY_STRING'] . "<br>";
    echo "SESSION: "; print_r($_SESSION); echo "<br>";
}
*/

function loadController($controllerName) {
    $controllerFile = BASE_PATH . "/controllers/{$controllerName}.php";
    // Debug comentado para producción
    /*
    if (DEBUG) {
        echo "Attempting to load controller: " . $controllerFile . "<br>";
    }
    */
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        return new $controllerName();
    } else {
        throw new Exception("Controller not found: {$controllerName}");
    }
}

function checkAccess($requiredLevel) {
    error_log("CHECKACCESS START - Level: $requiredLevel");
    
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['nivel_acceso'])) {
        error_log("CHECKACCESS FAIL - Missing session, redirecting to login");
        header('Location: ' . LOGIN_URL);
        exit();
    }
    
    if ($_SESSION['nivel_acceso'] < $requiredLevel) {
        error_log("CHECKACCESS FAIL - Insufficient level, redirecting to unauthorized");
        header('Location: ' . UNAUTHORIZED_URL);
        exit();
    }
    
    error_log("CHECKACCESS SUCCESS - Access granted");
}

function isAjaxRequest() {
    return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ||
           (!empty($_SERVER['HTTP_ACCEPT']) && 
            strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
}

$uri = $_SERVER['REQUEST_URI'];
$basePath = BASE_URL;
$route = str_replace($basePath, '', $uri);
$route = strtok($route, '?');
$route = trim($route, '/');

// Verificar si hay query parameters para el nuevo sistema
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';

error_log("=== ROUTING ===");
error_log("REQUEST_URI: " . $uri);
error_log("BASE_URL: " . BASE_URL);
error_log("Processed route: " . $route);
error_log("Action parameter: " . $action);
error_log("ID parameter: " . $id);
error_log("Session ID: " . session_id());

try {
    // Manejar query parameters del nuevo sistema
    if (!empty($action)) {
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
                $filePath = $_GET['path'] ?? '';
                if (empty($filePath)) {
                    http_response_code(400);
                    echo "Archivo no especificado";
                    exit;
                }
                
                // Validar que el archivo esté dentro del directorio uploads
                $fullPath = __DIR__ . '/' . $filePath;
                $uploadsDir = __DIR__ . '/uploads/';
                
            // Verificar que el archivo esté dentro del directorio uploads
            $realFullPath = realpath($fullPath);
            $realUploadsDir = realpath($uploadsDir);
            
            if (!$realFullPath || !$realUploadsDir || strpos($realFullPath, $realUploadsDir) !== 0) {
                http_response_code(403);
                echo "Acceso denegado";
                exit;
            }
                
                // Verificar que el archivo existe
                if (!file_exists($fullPath)) {
                    http_response_code(404);
                    echo "Archivo no encontrado";
                    exit;
                }
                
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
                error_log("=== ABOUT TO CALL CHECKACCESS FOR ADMIN DASHBOARD ===");
                checkAccess(1);
                error_log("=== CHECKACCESS COMPLETED, LOADING ADMIN CONTROLLER ===");
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
                $controller = loadController('AdminController');
                $controller->editUser($id);
                break;
                
            case 'admin_edit_product':
                checkAccess(1);
                $controller = loadController('AdminController');
                $controller->editProduct($id);
                break;
                
            case 'admin_edit_cpc':
                checkAccess(1);
                $controller = loadController('AdminController');
                $controller->editCPC($id);
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

            case 'admin_get_offer_ratings':
                checkAccess(1);
                $controller = loadController('AdminController');
                $controller->getOfferRatings();
                break;

            case 'admin_save_offer_rating':
                checkAccess(1);
                $controller = loadController('AdminController');
                $controller->saveOfferRating();
                break;
                
            case 'admin_toggle_user_status':
                checkAccess(1);
                try {
                    $controller = loadController('AdminController');
                    $controller->toggleUserStatus();
                } catch (Exception $e) {
                    error_log('Error in toggle-user-status: ' . $e->getMessage());
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
                }
                break;
                
            case 'admin_manage_product':
                checkAccess(1);
                $controller = loadController('AdminController');
                $controller->manageProduct($id);
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
                $controller = loadController('ParticipantController');
                $controller->viewProduct($id);
                break;

            case 'participant_puja':
                checkAccess(3);
                $controller = loadController('ParticipantController');
                $controller->pujaWindow($id);
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
                
            case 'participant_phase':
                checkAccess(3);
                $controller = loadController('ParticipantController');
                $controller->loadPhaseContent($id);
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

            case 'participant_get_offer_rating':
                checkAccess(3);
                $controller = loadController('ParticipantController');
                $controller->getOfferRating();
                break;

            case 'participant_submit_initial_offer':
                checkAccess(3);
                $controller = loadController('ParticipantController');
                $controller->submitInitialOffer();
                break;

            case 'participant_download_convalidation_pdf':
                checkAccess(3);
                $controller = loadController('ParticipantController');
                $controller->downloadConvalidationPdf();
                break;
                
            // Rutas del Moderador
            case 'moderator_dashboard':
                checkAccess(2);
                $controller = loadController('ModeratorController');
                $controller->dashboard();
                break;
                
            case 'moderator_manage_product':
                checkAccess(2);
                $controller = loadController('ModeratorController');
                $controller->manageProduct($id);
                break;
                
            case 'moderator_edit_product':
                checkAccess(2);
                $controller = loadController('ModeratorController');
                $controller->editProduct($id);
                break;
                
            case 'moderator_manage_cpcs':
                checkAccess(2);
                $controller = loadController('ModeratorController');
                $controller->manageCPCs();
                break;
                
            case 'moderator_edit_cpc':
                checkAccess(2);
                $controller = loadController('ModeratorController');
                $controller->editCPC($id);
                break;
                
            case 'moderator_manage_questions':
                checkAccess(2);
                $controller = loadController('ModeratorController');
                $controller->manageQuestions($id);
                break;
                
            case 'moderator_evaluate_participants':
                checkAccess(2);
                $controller = loadController('ModeratorController');
                $controller->evaluateParticipants($id);
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
                    echo json_encode(['error' => 'Página no encontrada']);
                } else {
                    header('Location: ' . LOGIN_URL);
                }
                break;
        }
    } else {
        // Sistema legacy - rutas amigables
        switch ($route) {
            case '':
            case 'login':
                $controller = loadController('AuthController');
                $controller->login();
                break;

            case 'logout':
                $controller = loadController('AuthController');
                $controller->logout();
                break;

            // Rutas del Administrador
            case 'admin/dashboard':
                error_log("=== ABOUT TO CALL CHECKACCESS FOR ADMIN DASHBOARD ===");
                checkAccess(1);
                error_log("=== CHECKACCESS COMPLETED, LOADING ADMIN CONTROLLER ===");
                $controller = loadController('AdminController');
                $controller->dashboard();
                break;

            case 'admin/create-product':
                checkAccess(1);
                $controller = loadController('AdminController');
                $controller->createProduct();
                break;

            case 'admin/create-user':
                checkAccess(1);
                $controller = loadController('AdminController');
                $controller->createUser();
                break;

            case 'admin/create-cpc':
                checkAccess(1);
                $controller = loadController('AdminController');
                $controller->createCPC();
                break;

            case (preg_match('/^admin\/edit-user\/(\d+)$/', $route, $matches) ? true : false):
                checkAccess(1);
                $controller = loadController('AdminController');
                $controller->editUser($matches[1]);
                break;

            case (preg_match('/^admin\/edit-product\/(\d+)$/', $route, $matches) ? true : false):
                checkAccess(1);
                $controller = loadController('AdminController');
                $controller->editProduct($matches[1]);
                break;

            case (preg_match('/^admin\/edit-cpc\/(\d+)$/', $route, $matches) ? true : false):
                checkAccess(1);
                $controller = loadController('AdminController');
                $controller->editCPC($matches[1]);
                break;

            case 'admin/toggle-user-status':
                checkAccess(1);
                try {
                    $controller = loadController('AdminController');
                    $controller->toggleUserStatus();
                } catch (Exception $e) {
                    error_log('Error in toggle-user-status: ' . $e->getMessage());
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
                }
                break;

            case 'admin/get-offer-ratings':
                checkAccess(1);
                $controller = loadController('AdminController');
                $controller->getOfferRatings();
                break;

            case 'admin/save-offer-rating':
                checkAccess(1);
                $controller = loadController('AdminController');
                $controller->saveOfferRating();
                break;

            case 'admin/get-unanswered-questions':
                checkAccess(1);
                $controller = loadController('AdminController');
                $controller->getUnansweredQuestions();
                break;

            case 'admin/answer-questions':
                error_log("=== ANSWER QUESTIONS ROUTE MATCHED ===");
                error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
                error_log("POST data: " . print_r($_POST, true));
                checkAccess(1);
                $controller = loadController('AdminController');
                $controller->answerQuestions();
                break;

            case (preg_match('/^admin\/manage-product\/(\d+)$/', $route, $matches) ? true : false):
                checkAccess(1);
                $controller = loadController('AdminController');
                $controller->manageProduct($matches[1]);
                break;

            case 'admin/delete-user':
                checkAccess(1);
                $controller = loadController('AdminController');
                $controller->deleteUser();
                break;
        
            case 'admin/delete-product':
                checkAccess(1);
                $controller = loadController('AdminController');
                $controller->deleteProduct();
                break;
        
            case 'admin/delete-cpc':
                checkAccess(1);
                $controller = loadController('AdminController');
                $controller->deleteCPC();
                break;

            // Rutas del Participante
            case 'participant/dashboard':
                error_log("=== PARTICIPANT DASHBOARD ROUTE HIT ===");
                error_log("Session data: " . print_r($_SESSION, true));
                checkAccess(3);
                error_log("=== CHECKACCESS PASSED, LOADING PARTICIPANT CONTROLLER ===");
                $controller = loadController('ParticipantController');
                error_log("=== CONTROLLER LOADED, CALLING DASHBOARD METHOD ===");
                $controller->dashboard();
                error_log("=== DASHBOARD METHOD COMPLETED ===");
                break;

            case (preg_match('/^participant\/view-product\/(\d+)$/', $route, $matches) ? true : false):
                checkAccess(3);
                $controller = loadController('ParticipantController');
                $controller->viewProduct($matches[1]);
                break;

            case (preg_match('/^participant\/puja\/(\d+)$/', $route, $matches) ? true : false):
                checkAccess(3);
                $controller = loadController('ParticipantController');
                $controller->pujaWindow($matches[1]);
                break;

            case 'participant/profile':
                checkAccess(3);
                $controller = loadController('ParticipantController');
                $controller->profile();
                break;

            case 'participant/search-process':
                checkAccess(3);
                $controller = loadController('ParticipantController');
                $controller->searchProcess();
                break;
    
            case 'participant/add-cpc':
                checkAccess(3);
                $controller = loadController('ParticipantController');
                $controller->addCpc();
                break;
    
            case 'participant/remove-cpc':
                checkAccess(3);
                $controller = loadController('ParticipantController');
                $controller->removeCpc();
                break;

            case 'participant/submit-bid':
                checkAccess(3);
                $controller = loadController('ParticipantController');
                $controller->submitBid();
                break;

            case (preg_match('/^participant\/puja-status\/(\d+)$/', $route, $matches) ? true : false):
                checkAccess(3);
                $controller = loadController('ParticipantController');
                $controller->getPujaStatus($matches[1]);
                break;
    
            case (preg_match('/^participant\/phase\/([a-zA-Z0-9]+)$/', $route, $matches) ? true : false):
                checkAccess(3);
                $controller = loadController('ParticipantController');
                $controller->loadPhaseContent($matches[1]);
                break;

            case 'participant/submit-question':
                checkAccess(3);
                $controller = loadController('ParticipantController');
                $controller->submitQuestion();
                break;

            case 'participant/get-questions':
                checkAccess(3);
                $controller = loadController('ParticipantController');
                $controller->getQuestions();
                break;

            case (preg_match('/^participant\/pyr\/(\d+)$/', $route, $matches) ? true : false):
                checkAccess(3);
                $controller = loadController('ParticipantController');
                $controller->pyrDirect($matches[1]);
                break;

            case 'participant/pyr':
                checkAccess(3);
                $controller = loadController('ParticipantController');
                $controller->pyrDirect();
                break;

            case 'participant/upload-offer':
                checkAccess(3);
                $controller = loadController('ParticipantController');
                $controller->uploadOffer();
                break;

            case 'participant/get-offers':
                checkAccess(3);
                $controller = loadController('ParticipantController');
                $controller->getOffers();
                break;

            case 'participant/delete-offer':
                checkAccess(3);
                $controller = loadController('ParticipantController');
                $controller->deleteOffer();
                break;

            case 'participant/process-offer':
                checkAccess(3);
                $controller = loadController('ParticipantController');
                $controller->processOffer();
                break;

            case 'participant/submit-initial-offer':
                checkAccess(3);
                $controller = loadController('ParticipantController');
                $controller->submitInitialOffer();
                break;

            case 'participant/download-offer-pdf':
                checkAccess(3);
                $controller = loadController('ParticipantController');
                $controller->downloadOfferPdf();
                break;

            case 'participant/submit-convalidation':
                checkAccess(3);
                $controller = loadController('ParticipantController');
                $controller->submitConvalidation();
                break;

            case 'participant/get-convalidation':
                checkAccess(3);
                $controller = loadController('ParticipantController');
                $controller->getConvalidation();
                break;

            case 'participant/download-convalidation-pdf':
                checkAccess(3);
                $controller = loadController('ParticipantController');
                $controller->downloadConvalidationPdf();
                break;

            case 'participant/get-offer-rating':
                checkAccess(3);
                $controller = loadController('ParticipantController');
                $controller->getOfferRating();
                break;
    
            // Rutas del Moderador
            case 'moderator/dashboard':
                checkAccess(2);
                $controller = loadController('ModeratorController');
                $controller->dashboard();
                break;
    
            case (preg_match('/^moderator\/manage-product\/(\d+)$/', $route, $matches) ? true : false):
                checkAccess(2);
                $controller = loadController('ModeratorController');
                $controller->manageProduct($matches[1]);
                break;
                
            case (preg_match('/^moderator\/edit-product\/(\d+)$/', $route, $matches) ? true : false):
                checkAccess(2);
                $controller = loadController('ModeratorController');
                $controller->editProduct($matches[1]);
                break;
    
            case 'moderator/manage-cpcs':
                checkAccess(2);
                $controller = loadController('ModeratorController');
                $controller->manageCPCs();
                break;
    
            case (preg_match('/^moderator\/edit-cpc\/(\d+)$/', $route, $matches) ? true : false):
                checkAccess(2);
                $controller = loadController('ModeratorController');
                $controller->editCPC($matches[1]);
                break;
    
            case (preg_match('/^moderator\/manage-questions\/(\d+)$/', $route, $matches) ? true : false):
                checkAccess(2);
                $controller = loadController('ModeratorController');
                $controller->manageQuestions($matches[1]);
                break;
    
            case (preg_match('/^moderator\/evaluate-participants\/(\d+)$/', $route, $matches) ? true : false):
                checkAccess(2);
                $controller = loadController('ModeratorController');
                $controller->evaluateParticipants($matches[1]);
                break;
    
            case 'moderator/delete-cpc':
                checkAccess(2);
                $controller = loadController('ModeratorController');
                $controller->deleteCPC();
                break;

            case 'moderator/get-unanswered-questions':
                checkAccess(2);
                $controller = loadController('ModeratorController');
                $controller->getUnansweredQuestions();
                break;

            case 'moderator/answer-questions':
                error_log("=== MODERATOR ANSWER QUESTIONS ROUTE MATCHED ===");
                error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
                error_log("POST data: " . print_r($_POST, true));
                checkAccess(2);
                $controller = loadController('ModeratorController');
                $controller->answerQuestions();
                break;

            case 'moderator/get-offer-ratings':
                checkAccess(2);
                $controller = loadController('ModeratorController');
                $controller->getOfferRatings();
                break;

            case 'moderator/save-offer-rating':
                checkAccess(2);
                $controller = loadController('ModeratorController');
                $controller->saveOfferRating();
                break;
    
            default:
                // Página 404 o redirigir a login
                if (isAjaxRequest()) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Página no encontrada']);
                } else {
                    header('Location: ' . LOGIN_URL);
                }
                break;
        }
    }
} catch (Exception $e) {
        error_log("EXCEPTION CAUGHT: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        // Si es un error de WordPress, ignorar y continuar
        if (strpos($e->getMessage(), 'wp-includes') !== false || 
            strpos($e->getMessage(), 'wordpress') !== false ||
            strpos($e->getMessage(), 'dt_theme') !== false) {
            error_log("WordPress error ignored, continuing...");
            return;
        }
        
        if (DEBUG) {
            echo "Error: " . $e->getMessage() . "<br>";
            echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
        } else {
            if (isAjaxRequest()) {
                http_response_code(500);
                echo json_encode(['error' => 'Error interno del servidor']);
            } else {
                require_once BASE_PATH . '/views/error.php';
            }
        }
    }
    
    // Debug comentado para producción
    /*
    if (DEBUG) {
        echo "End of index.php reached.";
    }
    */