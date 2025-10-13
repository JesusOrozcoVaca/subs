<?php
<?php
// === Base URL auto (local vs prod fixed) ===
$__host = $_SERVER['HTTP_HOST'] ?? '';
$__is_local = (stripos($__host,'localhost') !== false) || ($__host === '127.0.0.1');
$baseUrl = $__is_local ? 'http://localhost/subs/' : 'https://sie.hjconsulting.com.ec/';
if (!defined('BASE_URL')) { define('BASE_URL', $baseUrl); }
$basePath = $__is_local ? '/subs/' : '/';
// === end base url auto ===
 
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('BASE_PATH', dirname(__FILE__));
define('DEBUG', false);

if (DEBUG) {
    echo "BASE_PATH: " . BASE_PATH . "<br>";
    echo "Current file: " . __FILE__ . "<br>";
    echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "<br>";
    echo "QUERY_STRING: " . $_SERVER['QUERY_STRING'] . "<br>";
    echo "SESSION: "; print_r($_SESSION); echo "<br>";
}

function loadController($controllerName) {
    $controllerFile = BASE_PATH . "/controllers/{$controllerName}.php";
    if (DEBUG) {
        echo "Attempting to load controller: " . $controllerFile . "<br>";
    }
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        return new $controllerName();
    } else {
        throw new Exception("Controller not found: {$controllerName}");
    }
}

function checkAccess($requiredLevel) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['nivel_acceso'])) {
        header('Location: ' . BASE_URL . 'login');
        exit();
    }
    if ($_SESSION['nivel_acceso'] < $requiredLevel) {
        header('Location: ' . BASE_URL . 'unauthorized');
        exit();
    }
}

$uri = $_SERVER['REQUEST_URI'];
$basePath = $__is_local ? '/subs/' : '/';
$route = str_replace($basePath, '', $uri);
$route = strtok($route, '?');
$route = trim($route, '/');

if (DEBUG) {
    echo "Processed route: " . $route . "<br>";
}

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
            checkAccess(1);
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
                    header('Location: ' . BASE_URL . 'login');
                }
                break;
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
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
    
    function isAjaxRequest() {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ||
               (!empty($_SERVER['HTTP_ACCEPT']) && 
                strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }
    
    if (DEBUG) {
        echo "End of index.php reached.";
    }