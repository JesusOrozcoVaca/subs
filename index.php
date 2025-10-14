<?php 
// Verificación segura de output buffer para evitar Quirks mode
if (ob_get_level()) {
    ob_clean();
}

// Deshabilitar errores de WordPress que interfieren
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

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

error_log("=== ROUTING ===");
error_log("REQUEST_URI: " . $uri);
error_log("BASE_URL: " . BASE_URL);
error_log("Processed route: " . $route);
error_log("Session ID: " . session_id());

try {
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
            checkAccess(3);
            $controller = loadController('ParticipantController');
            $controller->dashboard();
            break;

        case (preg_match('/^participant\/view-product\/(\d+)$/', $route, $matches) ? true : false):
            checkAccess(3);
            $controller = loadController('ParticipantController');
            $controller->viewProduct($matches[1]);
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
    
            case (preg_match('/^participant\/phase\/(\w+)$/', $route, $matches) ? true : false):
                checkAccess(3);
                $controller = loadController('ParticipantController');
                $controller->loadPhaseContent($matches[1]);
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
                $controller->manageCPCs();  // Utilizamos el mismo método para manejar la eliminación
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