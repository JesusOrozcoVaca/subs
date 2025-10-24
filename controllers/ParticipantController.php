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
    
        // Obtener estados desde la base de datos
        require_once BASE_PATH . '/models/ProductState.php';
        $productStateModel = new ProductState();
        $allPhases = $productStateModel->getStatesForSelect();
        
        // Implementar lógica de desbloqueo de fases basada en el estado del producto
        $phases = $this->getUnlockedPhases($product, $allPhases);
        $currentStateCode = $this->getCurrentStateCode($product);
        
        error_log("=== RENDERING VIEW PRODUCT ===");
        require_once BASE_PATH . '/views/participant/part_view_product.php';
        error_log("=== VIEW PRODUCT RENDERED ===");
    }
    
    public function loadPhaseContent($phase) {
        error_log("=== LOADPHASECONTENT START ===");
        error_log("Phase: " . $phase);
        error_log("Is AJAX: " . ($this->isAjaxRequest() ? 'YES' : 'NO'));
        
        // Obtener el ID del producto desde el parámetro GET
        $productId = $_GET['producto_id'] ?? null;
        error_log("Product ID from GET: " . $productId);
        
        if (!$productId) {
            error_log("No product ID found in GET parameter");
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID de producto no encontrado']);
            return;
        }
        
        // Mapear números a códigos de fase
        $phaseMap = [
            '1' => 'pyr',
            '2' => 'eof', 
            '3' => 'conv',
            '4' => 'calif',
            '5' => 'ofini',
            '6' => 'puja',
            '7' => 'adj'
        ];
        
        $allowedPhases = ['pyr', 'eof', 'conv', 'calif', 'ofini', 'puja', 'adj'];
        
        // Si es un número, convertirlo a código de fase
        if (isset($phaseMap[$phase])) {
            $phase = $phaseMap[$phase];
            error_log("Phase mapped to: " . $phase);
        }
        
        if (!in_array($phase, $allowedPhases)) {
            error_log("Phase not allowed: " . $phase);
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Fase no válida']);
            return;
        }
    
        // Construir la ruta del archivo de vista
        $viewFile = BASE_PATH . "/views/participant/phases/{$phase}.php";
        error_log("View file: " . $viewFile);
        error_log("File exists: " . (file_exists($viewFile) ? 'YES' : 'NO'));
        
        if (file_exists($viewFile)) {
            // Limpiar todos los buffers existentes ANTES de generar el contenido
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            // Iniciar un nuevo buffer para capturar el contenido de la vista
            ob_start();
            
            // Obtener información completa del producto
            $product = $this->productModel->getProductById($productId);
            if (!$product) {
                ob_end_clean();
                error_log("Product not found: " . $productId);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
                exit;
            }
            
            include $viewFile;
            $content = ob_get_clean();
            error_log("Content length: " . strlen($content));
            
            // Asegurar que no hay output previo
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'content' => $content]);
            error_log("=== LOADPHASECONTENT COMPLETED SUCCESSFULLY ===");
            exit;
        } else {
            // Limpiar todos los buffers
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            error_log("View file not found: " . $viewFile);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Contenido de fase no encontrado']);
            exit;
        }
    }

    private function getProductIdFromReferer() {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        error_log("Referer URL: " . $referer);
        
        // Extraer ID del producto de la URL de referencia
        if (preg_match('/participant\/view-product\/(\d+)/', $referer, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    private function getUnlockedPhases($product, $allPhases) {
        // Obtener el código del estado actual del producto
        $currentStateCode = $this->getCurrentStateCode($product);
        error_log("Current state code: " . $currentStateCode);
        
        // Mapeo de códigos de estado a fases desbloqueadas
        $statePhaseMap = [
            'pyr' => ['pyr'], // Preguntas y Respuestas - siempre disponible
            'eof' => ['pyr', 'eof'], // Entrega de Ofertas - desbloquea Preguntas y Respuestas (solo lectura) + Entrega de Ofertas
            'conv' => ['pyr', 'eof', 'conv'], // Convalidación - desbloquea todas las anteriores
            'calif' => ['pyr', 'eof', 'conv', 'calif'], // Calificación
            'ofini' => ['pyr', 'eof', 'conv', 'calif', 'ofini'], // Oferta Inicial
            'puja' => ['pyr', 'eof', 'conv', 'calif', 'ofini', 'puja'], // Puja
            'adj' => ['pyr', 'eof', 'conv', 'calif', 'ofini', 'puja', 'adj'] // Adjudicado
        ];
        
        $unlockedPhases = $statePhaseMap[$currentStateCode] ?? ['pyr'];
        
        // Filtrar solo las fases desbloqueadas
        $phases = [];
        foreach ($allPhases as $phaseKey => $phaseName) {
            if (in_array($phaseKey, $unlockedPhases)) {
                $phases[$phaseKey] = $phaseName;
            }
        }
        
        error_log("Current state code: " . $currentStateCode);
        error_log("Unlocked phases: " . implode(', ', $unlockedPhases));
        error_log("Available phases: " . implode(', ', array_keys($phases)));
        
        return $phases;
    }

    private function getCurrentStateCode($product) {
        error_log("getCurrentStateCode - Product estado_id: " . ($product['estado_id'] ?? 'null'));
        
        // Si el producto tiene estado_id, obtener el código del estado
        if (isset($product['estado_id']) && $product['estado_id']) {
            require_once BASE_PATH . '/models/ProductState.php';
            $productStateModel = new ProductState();
            $stateCode = $productStateModel->getStateCodeById($product['estado_id']);
            error_log("getCurrentStateCode - State code: " . $stateCode);
            return $stateCode;
        }
        
        // Si no se encuentra, asumir que está en estado inicial
        error_log("getCurrentStateCode - Defaulting to pyr");
        return 'pyr';
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
        error_log("=== SENDJSONRESPONSE START ===");
        error_log("Success: " . ($success ? 'YES' : 'NO'));
        error_log("Message: " . $message);
        
        // Limpiar TODOS los buffers de salida
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Asegurar que no haya salida previa
        if (headers_sent($file, $line)) {
            error_log("WARNING: Headers already sent in $file on line $line");
        }
        
        header('Content-Type: application/json');
        $response = [
            'success' => $success,
            'message' => $message,
            'data' => $data
        ];
        
        error_log("Response: " . json_encode($response));
        echo json_encode($response);
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

    public function submitQuestion() {
        error_log("=== SUBMITQUESTION START ===");
        error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
        error_log("Is AJAX: " . ($this->isAjaxRequest() ? 'YES' : 'NO'));
        error_log("POST data: " . print_r($_POST, true));
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->isAjaxRequest()) {
            $productoId = $_POST['producto_id'] ?? null;
            $pregunta = trim($_POST['pregunta'] ?? '');
            
            error_log("Producto ID: " . $productoId);
            error_log("Pregunta: " . $pregunta);
            
            if (!$productoId || empty($pregunta)) {
                error_log("Datos incompletos");
                $this->sendJsonResponse(false, "Datos incompletos");
                return;
            }
            
            if (strlen($pregunta) > 500) {
                error_log("Pregunta muy larga: " . strlen($pregunta));
                $this->sendJsonResponse(false, "La pregunta no puede exceder 500 caracteres");
                return;
            }
            
            require_once BASE_PATH . '/models/Question.php';
            $questionModel = new Question();
            
            $result = $questionModel->create([
                'producto_id' => $productoId,
                'usuario_id' => $_SESSION['user_id'],
                'pregunta' => $pregunta
            ]);
            
            error_log("Create result: " . ($result ? 'SUCCESS' : 'FAILED'));
            
            if ($result) {
                $this->sendJsonResponse(true, "Pregunta enviada exitosamente");
            } else {
                $this->sendJsonResponse(false, "Error al enviar la pregunta");
            }
        } else {
            error_log("Método no permitido");
            $this->sendJsonResponse(false, "Método no permitido");
        }
    }

    public function getQuestions() {
        if ($this->isAjaxRequest()) {
            $productoId = $_GET['producto_id'] ?? null;
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 5);
            
            if (!$productoId) {
                $this->sendJsonResponse(false, "ID de producto requerido");
                return;
            }
            
            require_once BASE_PATH . '/models/Question.php';
            $questionModel = new Question();
            
            $data = $questionModel->getProductQuestionsPaginated($productoId, $page, $limit);
            
            $this->sendJsonResponse(true, "", $data);
        } else {
            $this->sendJsonResponse(false, "Método no permitido");
        }
    }

    public function uploadOffer() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->isAjaxRequest()) {
            $productoId = $_POST['producto_id'] ?? null;
            $usuarioId = $_SESSION['user_id'];
            
            if (!$productoId) {
                $this->sendJsonResponse(false, "ID de producto requerido");
                return;
            }
            
            if (!isset($_FILES['documento_oferta']) || $_FILES['documento_oferta']['error'] !== UPLOAD_ERR_OK) {
                $this->sendJsonResponse(false, "Error al subir el archivo");
                return;
            }
            
            $file = $_FILES['documento_oferta'];
            
            // Validaciones
            $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
            $maxSize = 512 * 1024; // 512KB
            
            if (!in_array($file['type'], $allowedTypes)) {
                $this->sendJsonResponse(false, "Tipo de archivo no permitido. Solo PDF, JPG y PNG");
                return;
            }
            
            if ($file['size'] > $maxSize) {
                $this->sendJsonResponse(false, "El archivo excede el tamaño máximo de 512KB");
                return;
            }
            
            require_once BASE_PATH . '/models/Document.php';
            $documentModel = new Document();
            
            $result = $documentModel->uploadDocument($productoId, $usuarioId, $file);
            
            if ($result['success']) {
                $this->sendJsonResponse(true, "Archivo subido exitosamente", $result);
            } else {
                $this->sendJsonResponse(false, $result['message']);
            }
        } else {
            $this->sendJsonResponse(false, "Método no permitido");
        }
    }

    public function getOffers() {
        if ($this->isAjaxRequest()) {
            $productoId = $_GET['producto_id'] ?? null;
            $usuarioId = $_SESSION['user_id'];
            
            if (!$productoId) {
                $this->sendJsonResponse(false, "ID de producto requerido");
                return;
            }
            
            require_once BASE_PATH . '/models/Document.php';
            $documentModel = new Document();
            
            $ofertas = $documentModel->getUserDocuments($productoId, $usuarioId);
            
            $this->sendJsonResponse(true, "", ['ofertas' => $ofertas]);
        } else {
            $this->sendJsonResponse(false, "Método no permitido");
        }
    }

    public function deleteOffer() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->isAjaxRequest()) {
            $fileId = $_POST['file_id'] ?? null;
            $usuarioId = $_SESSION['user_id'];
            
            if (!$fileId) {
                $this->sendJsonResponse(false, "ID de archivo requerido");
                return;
            }
            
            require_once BASE_PATH . '/models/Document.php';
            $documentModel = new Document();
            
            $result = $documentModel->deleteDocument($fileId, $usuarioId);
            
            if ($result['success']) {
                $this->sendJsonResponse(true, "Archivo eliminado exitosamente");
            } else {
                $this->sendJsonResponse(false, $result['message']);
            }
        } else {
            $this->sendJsonResponse(false, "Método no permitido");
        }
    }

    public function processOffer() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->isAjaxRequest()) {
            $productoId = $_POST['producto_id'] ?? null;
            $usuarioId = $_SESSION['user_id'];
            
            if (!$productoId) {
                $this->sendJsonResponse(false, "ID de producto requerido");
                return;
            }
            
            require_once BASE_PATH . '/models/Document.php';
            $documentModel = new Document();
            
            $result = $documentModel->processOffer($productoId, $usuarioId);
            
            if ($result) {
                $this->sendJsonResponse(true, "Entrega de ofertas procesada exitosamente");
            } else {
                $this->sendJsonResponse(false, "Error al procesar la entrega");
            }
        } else {
            $this->sendJsonResponse(false, "Método no permitido");
        }
    }

    public function pyrDirect($productId = null) {
        error_log("=== PYRDIRECT START ===");
        
        // Si no se proporciona el ID, intentar obtenerlo de la URL
        if (!$productId) {
            $productId = $this->getProductIdFromURL();
        }
        
        error_log("Product ID: " . $productId);
        
        if (!$productId) {
            error_log("No product ID found");
            header('Location: ' . url('participant/dashboard'));
            exit;
        }
        
        // Obtener información del producto
        $product = $this->productModel->getProductById($productId);
        if (!$product) {
            error_log("Product not found");
            header('Location: ' . url('participant/dashboard'));
            exit;
        }
        
        error_log("Product found: " . $product['codigo']);
        
        // Renderizar vista directa
        $content = $this->renderView('pyr_test.php', [
            'product' => $product
        ]);
        
        // Enviar el contenido al navegador
        echo $content;
    }
    
    private function getProductIdFromURL() {
        $pathParts = explode('/', $_SERVER['REQUEST_URI']);
        $productId = end($pathParts);
        return is_numeric($productId) ? $productId : null;
    }
}