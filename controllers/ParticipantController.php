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
                header('Location: ' . BASE_URL . 'participant/profile');
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
        $userId = $_SESSION['user_id'];
        $products = $this->productModel->getParticipantProducts($userId);
        $pageTitle = "Dashboard de Participante";
        $content = $this->renderView('part_dashboard.php', ['products' => $products]);
        $this->renderResponse($pageTitle, $content);
    }

    public function viewProduct($id) {
        $userId = $_SESSION['user_id'];
        $product = $this->productModel->getProductById($id);
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
    
        require_once BASE_PATH . '/views/participant/part_view_product.php';
    }
    
    public function loadPhaseContent($phase) {
        $allowedPhases = ['pyr', 'eof', 'conv', 'calif', 'ofini', 'puja'];
        
        if (!in_array($phase, $allowedPhases)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Fase no válida']);
            return;
        }
    
        $viewFile = BASE_PATH . "/views/participant/phases/{$phase}.php";
        if (file_exists($viewFile)) {
            ob_start();
            include $viewFile;
            $content = ob_get_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'content' => $content]);
        } else {
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
        extract($data);
        ob_start();
        require_once BASE_PATH . '/views/participant/' . $viewName;
        return ob_get_clean();
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
        if ($this->isAjaxRequest()) {
            $this->sendJsonResponse(true, '', ['content' => $content, 'title' => $pageTitle]);
        } else {
            require_once BASE_PATH . '/views/participant/participant_layout.php';
        }
    }

    private function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}