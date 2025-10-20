<?php
require_once BASE_PATH . '/models/Product.php';
require_once BASE_PATH . '/models/User.php';
require_once BASE_PATH . '/models/CPC.php';

class ParticipantController {
    private $productModel;
    private $userModel;
    private $cpcModel;

    public function __construct() {
        $this->productModel = new Product();
        $this->userModel = new User();
        $this->cpcModel = new CPC();
    }

    public function profile() {
        $userId = $_SESSION['user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->isAjaxRequest()) {
                header('Location: ' . url('participant/profile'));
                exit;
            }
    
            $action = $_POST['action'] ?? '';
    
            if (empty($action)) {
                $this->sendJsonResponse(true, "No se realizó ninguna acción.", null);
                return;
            }
    
            try {
                $result = false;

                switch ($action) {
                    case 'update_profile':
                        $result = $this->userModel->updateUser($userId, $_POST);
                        break;
                    case 'add_cpc':
                        $result = $this->userModel->addCPC($userId, $_POST['cpc_id']);
                        break;
                    case 'remove_cpc':
                        $result = $this->userModel->removeCPC($userId, $_POST['cpc_id']);
                        break;
                    default:
                        throw new Exception("Acción no reconocida.");
                }
                
                if ($result) {
                    $user = $this->userModel->getUserById($userId);
                    $userCPCs = $this->userModel->getUserCPCs($userId);
                    $allCPCs = $this->cpcModel->getUnassignedCPCs($userId);
    
                    $this->sendJsonResponse(true, "Operación realizada con éxito.", [
                        'user' => $user,
                        'userCPCs' => $userCPCs,
                        'availableCPCs' => $allCPCs
                    ]);
                } else {
                    $this->sendJsonResponse(false, "Error al realizar la operación.");
                }
            } catch (Exception $e) {
                $this->sendJsonResponse(false, "Error: " . $e->getMessage());
            }
            return;
        }
    
        $user = $this->userModel->getUserById($userId);
        $userCPCs = $this->userModel->getUserCPCs($userId);
        $allCPCs = $this->cpcModel->getUnassignedCPCs($userId);
        
        $pageTitle = "Mi Perfil";
        $content = $this->renderView('part_profile.php', compact('user', 'userCPCs', 'allCPCs'));
        
        $this->renderResponse($pageTitle, $content);
    }

    public function searchProcess() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codigo = $_POST['codigo'] ?? '';
            $searchResult = $this->productModel->getProductByCode($codigo);
            
            if ($this->isAjaxRequest()) {
                if ($searchResult) {
                    $resultHtml = $this->renderView('part_search_results.php', ['searchResult' => $searchResult]);
                    $this->sendJsonResponse(true, 'Búsqueda realizada con éxito.', ['content' => $resultHtml]);
                } else {
                    $this->sendJsonResponse(true, 'No se encontraron resultados.', ['content' => '<p>No se encontraron resultados.</p>']);
                }
                return;
            }
        }
    
        $pageTitle = "Buscar Proceso";
        $content = $this->renderView('part_search_process.php', ['searchResult' => null]);
        
        $this->renderResponse($pageTitle, $content);
    }

    public function dashboard() {
        error_log("=== DASHBOARD METHOD START ===");
        $userId = $_SESSION['user_id'];
        error_log("User ID: " . $userId);
        
        error_log("=== CALLING GETPARTICIPANTPRODUCTS ===");
        $products = $this->productModel->getParticipantProducts($userId);
        error_log("Products retrieved: " . count($products) . " items");
        
        $pageTitle = "Dashboard de Participante";
        error_log("=== RENDERING VIEW ===");
        $content = $this->renderView('part_dashboard.php', ['products' => $products]);
        error_log("=== VIEW RENDERED SUCCESSFULLY ===");
        
        error_log("=== RENDERING RESPONSE ===");
        $this->renderResponse($pageTitle, $content);
        error_log("=== DASHBOARD METHOD COMPLETED ===");
    }

    public function viewProduct($id) {
        error_log("=== VIEWPRODUCT METHOD START ===");
        $userId = $_SESSION['user_id'];
        error_log("User ID: " . $userId);
        error_log("Product ID: " . $id);
        
        $product = $this->productModel->getProductById($id);
        error_log("Product retrieved: " . print_r($product, true));
        
        // Obtener la descripción del CPC
        $cpcInfo = $this->cpcModel->getCPCById($product['cpc_id']);
        $product['cpc_descripcion'] = $cpcInfo ? $cpcInfo['descripcion'] : 'CPC no encontrado';
        
        $userStatus = $this->productModel->getParticipantStatus($id, $userId);
        $dates = $this->productModel->calculateDates($id);
        $documents = $this->productModel->getProductDocuments($id);
    
        $phases = [
            'pyr' => 'Preguntas y Respuestas',
            'eof' => 'Entrega de Ofertas',
            'conv' => 'Convalidación de errores',
            'calif' => 'Calificación',
            'ofini' => 'Oferta Inicial',
            'puja' => 'Puja'
        ];
        
        error_log("=== RENDERING VIEW PRODUCT ===");
        require_once BASE_PATH . '/views/participant/part_view_product.php';
        error_log("=== VIEW PRODUCT RENDERED ===");
    }
    
    public function loadPhaseContent($phase) {
        error_log("=== LOADPHASECONTENT START ===");
        error_log("Phase: " . $phase);
        error_log("Is AJAX: " . ($this->isAjaxRequest() ? 'YES' : 'NO'));
        
        $allowedPhases = ['pyr', 'eof', 'conv', 'calif', 'ofini', 'puja'];
        
        if (!in_array($phase, $allowedPhases)) {
            error_log("Phase not allowed: " . $phase);
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Fase no válida']);
            return;
        }
    
        $viewFile = BASE_PATH . "/views/participant/phases/{$phase}.php";
        error_log("View file: " . $viewFile);
        error_log("File exists: " . (file_exists($viewFile) ? 'YES' : 'NO'));
        
        if (file_exists($viewFile)) {
            ob_start();
            include $viewFile;
            $content = ob_get_clean();
            error_log("Content length: " . strlen($content));
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'content' => $content]);
            error_log("=== LOADPHASECONTENT COMPLETED SUCCESSFULLY ===");
        } else {
            error_log("View file not found: " . $viewFile);
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Contenido de fase no encontrado']);
        }
    }

    public function addCpc() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->isAjaxRequest()) {
            $userId = $_SESSION['user_id'];
            $cpcId = $_POST['cpc_id'] ?? null;
    
            if ($cpcId) {
                $result = $this->userModel->addCPC($userId, $cpcId);
                if ($result) {
                    $userCPCs = $this->userModel->getUserCPCs($userId);
                    $availableCPCs = $this->cpcModel->getUnassignedCPCs($userId);
    
                    $this->sendJsonResponse(true, "CPC agregado exitosamente.", [
                        'userCPCs' => $userCPCs,
                        'availableCPCs' => $availableCPCs
                    ]);
                } else {
                    $this->sendJsonResponse(false, "Error al agregar el CPC.");
                }
            } else {
                $this->sendJsonResponse(false, "CPC no especificado.");
            }
        } else {
            $this->sendJsonResponse(false, "Método no permitido.");
        }
    }
    
    public function removeCpc() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->isAjaxRequest()) {
            $userId = $_SESSION['user_id'];
            $cpcId = $_POST['cpc_id'] ?? null;
    
            if ($cpcId) {
                $result = $this->userModel->removeCPC($userId, $cpcId);
                if ($result) {
                    $userCPCs = $this->userModel->getUserCPCs($userId);
                    $availableCPCs = $this->cpcModel->getUnassignedCPCs($userId);
    
                    $this->sendJsonResponse(true, "CPC eliminado exitosamente.", [
                        'userCPCs' => $userCPCs,
                        'availableCPCs' => $availableCPCs
                    ]);
                } else {
                    $this->sendJsonResponse(false, "Error al eliminar el CPC.");
                }
            } else {
                $this->sendJsonResponse(false, "CPC no especificado.");
            }
        } else {
            $this->sendJsonResponse(false, "Método no permitido.");
        }
    }

    private function renderView($viewName, $data = []) {
        error_log("=== RENDERVIEW START ===");
        error_log("View name: " . $viewName);
        error_log("Data: " . print_r($data, true));
        
        extract($data);
        ob_start();
        error_log("=== INCLUDING VIEW FILE ===");
        require_once BASE_PATH . '/views/participant/' . $viewName;
        $content = ob_get_clean();
        error_log("=== VIEW CONTENT GENERATED ===");
        return $content;
    }

    private function sendJsonResponse($success, $message, $data = null) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }

    private function renderResponse($pageTitle, $content) {
        error_log("=== RENDERRESPONSE START ===");
        error_log("Page title: " . $pageTitle);
        error_log("Is AJAX: " . ($this->isAjaxRequest() ? 'YES' : 'NO'));
        
        if ($this->isAjaxRequest()) {
            error_log("=== SENDING AJAX RESPONSE ===");
            $this->sendJsonResponse(true, '', ['content' => $content, 'title' => $pageTitle]);
        } else {
            error_log("=== INCLUDING LAYOUT FILE ===");
            require_once BASE_PATH . '/views/participant/participant_layout.php';
            error_log("=== LAYOUT INCLUDED SUCCESSFULLY ===");
        }
    }

    private function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}