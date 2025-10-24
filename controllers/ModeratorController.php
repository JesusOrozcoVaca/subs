<?php
require_once BASE_PATH . '/models/Product.php';
require_once BASE_PATH . '/models/CPC.php';
require_once BASE_PATH . '/models/User.php';
require_once BASE_PATH . '/models/Question.php';
require_once BASE_PATH . '/models/Bid.php';
require_once BASE_PATH . '/models/ProductState.php';

class ModeratorController {
    private $productModel;
    private $cpcModel;
    private $userModel;
    private $questionModel;
    private $bidModel;
    private $productStateModel;

    public function __construct() {
        $this->productModel = new Product();
        $this->cpcModel = new CPC();
        $this->userModel = new User();
        $this->questionModel = new Question();
        $this->bidModel = new Bid();
        $this->productStateModel = new ProductState();
    }

    private function isAjaxRequest() {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                   strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
        
        error_log("=== MODERATOR IS AJAX REQUEST DEBUG ===");
        error_log("HTTP_X_REQUESTED_WITH: " . ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? 'NOT SET'));
        error_log("HTTP_ACCEPT: " . ($_SERVER['HTTP_ACCEPT'] ?? 'NOT SET'));
        error_log("REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'NOT SET'));
        error_log("Is AJAX: " . ($isAjax ? 'YES' : 'NO'));
        
        // Fallback: si es POST y tiene action=change_status, tratarlo como AJAX
        if (!$isAjax && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_status') {
            error_log("=== FALLBACK: Treating as AJAX due to change_status action ===");
            $isAjax = true;
        }
        
        return $isAjax;
    }

    public function dashboard() {
        // Usar getAllProducts() en lugar de getAllActive() para mostrar todos los productos
        $products = $this->productModel->getAllProducts();
        error_log("=== MODERATOR DASHBOARD ===");
        error_log("Products loaded: " . count($products));
        
        if ($this->isAjaxRequest()) {
            require_once BASE_PATH . '/views/moderator/mod_dashboard_content.php';
        } else {
            require_once BASE_PATH . '/views/moderator/mod_dashboard.php';
        }
    }

    public function manageProduct($id) {
        try {
            error_log("=== MODERATOR MANAGE PRODUCT START ===");
            error_log("Product ID received: " . $id);
            error_log("Product ID type: " . gettype($id));
            error_log("Product ID empty: " . (empty($id) ? 'YES' : 'NO'));
            
            // Manejar cambio de estado
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_status') {
                error_log("=== MODERATOR CHANGE STATUS REQUEST ===");
                error_log("Is AJAX: " . ($this->isAjaxRequest() ? 'YES' : 'NO'));
                error_log("POST data: " . print_r($_POST, true));
                
                // Verificar que el ID del producto sea válido
                if (empty($id) || !is_numeric($id)) {
                    error_log("ERROR: Invalid product ID: " . $id);
                    if ($this->isAjaxRequest()) {
                        $this->sendJsonResponse(false, "ID de producto inválido.");
                    } else {
                        $_SESSION['error_message'] = "ID de producto inválido.";
                    }
                    return;
                }
                
                $estadoId = $_POST['estado_id'];
                error_log("Estado ID: " . $estadoId);
                error_log("Product ID: " . $id);
                
                try {
                    $result = $this->productModel->updateProductStatus($id, $estadoId);
                    error_log("Update result: " . ($result ? 'SUCCESS' : 'FAILED'));
                } catch (Exception $e) {
                    error_log("Error updating product status: " . $e->getMessage());
                    error_log("Stack trace: " . $e->getTraceAsString());
                    throw $e;
                }
                
                if ($this->isAjaxRequest()) {
                    error_log("=== MODERATOR SENDING JSON RESPONSE ===");
                    if ($result) {
                        $this->sendJsonResponse(true, "Estado del producto actualizado exitosamente.");
                    } else {
                        $this->sendJsonResponse(false, "Error al actualizar el estado del producto.");
                    }
                    return; // Salir para evitar cargar la vista
                } else {
                    error_log("=== MODERATOR NOT AJAX, USING SESSION MESSAGES ===");
                    if ($result) {
                        $_SESSION['success_message'] = "Estado del producto actualizado exitosamente.";
                        header('Location: ' . url('moderator/manage-product/' . $id));
                        exit;
                    } else {
                        $_SESSION['error_message'] = "Error al actualizar el estado del producto.";
                        header('Location: ' . url('moderator/manage-product/' . $id));
                        exit;
                    }
                }
            }
            
            // Manejar otras acciones
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $action = $_POST['action'];
                switch ($action) {
                    case 'evaluate_participant':
                        $participantId = $_POST['participant_id'];
                        $status = $_POST['status'];
                        $this->productModel->updateParticipantStatus($id, $participantId, $status);
                        $_SESSION['success_message'] = "Evaluación del participante actualizada exitosamente.";
                        break;
                    case 'answer_question':
                        $questionId = $_POST['question_id'];
                        $answer = $_POST['answer'];
                        $this->questionModel->answer($questionId, $answer);
                        $_SESSION['success_message'] = "Respuesta enviada exitosamente.";
                        break;
                }
                header('Location: ' . url('moderator/manage-product/' . $id));
                exit;
            }
            
            $product = $this->productModel->getProductById($id);
            $participants = $this->productModel->getParticipants($id);
            $questions = $this->questionModel->getProductQuestions($id);
            $bids = $this->bidModel->getProductBids($id);
            $estados = $this->productStateModel->getAllStates();
            
            // Para el dashboard, también necesitamos todos los productos
            $products = $this->productModel->getAllProducts();
            
            error_log("=== MODERATOR LOADING MANAGE PRODUCT VIEW ===");
            // Cargar el dashboard del moderador con el contenido de gestión de producto
            if ($this->isAjaxRequest()) {
                require_once BASE_PATH . '/views/moderator/mod_manage_product_content.php';
            } else {
                // Cargar el dashboard principal y el contenido de gestión
                require_once BASE_PATH . '/views/moderator/mod_dashboard.php';
            }
            error_log("=== MODERATOR MANAGE PRODUCT VIEW LOADED ===");
        } catch (Exception $e) {
            error_log("Error in moderator manageProduct: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->handleError($e);
        }
    }

    public function manageCPCs() {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $action = $_POST['action'] ?? '';
                
                switch ($action) {
                    case 'add':
                        $result = $this->cpcModel->createCPC($_POST);
                        if ($result) {
                            $this->sendJsonResponse(true, "CPC creado exitosamente.");
                        } else {
                            $this->sendJsonResponse(false, "Error al crear el CPC.");
                        }
                        break;
                    default:
                        $this->sendJsonResponse(false, "Acción no reconocida.");
                }
            } else {
                $cpcs = $this->cpcModel->getAllCPCs();
                
                if ($this->isAjaxRequest()) {
                    require_once BASE_PATH . '/views/moderator/mod_manage_cpcs_content.php';
                } else {
                    require_once BASE_PATH . '/views/moderator/mod_manage_cpcs.php';
                }
            }
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    public function editCPC($id) {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $result = $this->cpcModel->updateCPC($id, $_POST);
                if ($result) {
                    $this->sendJsonResponse(true, "CPC actualizado exitosamente.");
                } else {
                    throw new Exception("Error al actualizar el CPC.");
                }
            } else {
                $cpc = $this->cpcModel->getCPCById($id);
                if (!$cpc) {
                    throw new Exception("CPC no encontrado.");
                }
                require_once BASE_PATH . '/views/moderator/mod_edit_cpc_content.php';
            }
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    public function manageQuestions($productId) {
        $questions = $this->questionModel->getProductQuestions($productId);
        $product = $this->productModel->getProductById($productId);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $questionId = $_POST['question_id'];
            $answer = $_POST['answer'];
            $result = $this->questionModel->answer($questionId, $answer);
            if ($result) {
                $_SESSION['success_message'] = "Respuesta enviada exitosamente.";
            } else {
                $_SESSION['error_message'] = "Error al enviar la respuesta.";
            }
            header('Location: ' . url('moderator/manage-questions/' . $productId));
            exit;
        }
        
        require_once BASE_PATH . '/views/moderator/mod_manage_questions.php';
    }

    public function evaluateParticipants($productId) {
        $participants = $this->productModel->getParticipants($productId);
        $product = $this->productModel->getProductById($productId);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $participantId = $_POST['participant_id'];
            $status = $_POST['status'];
            $result = $this->productModel->updateParticipantStatus($productId, $participantId, $status);
            if ($result) {
                $_SESSION['success_message'] = "Evaluación del participante actualizada exitosamente.";
            } else {
                $_SESSION['error_message'] = "Error al actualizar la evaluación del participante.";
            }
            header('Location: ' . url('moderator/evaluate-participants/' . $productId));
            exit;
        }
        
        require_once BASE_PATH . '/views/moderator/mod_evaluate_participants.php';
    }

    public function deleteCPC() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cpcId = $_POST['id'] ?? null;
            if ($cpcId) {
                // Verificar si hay productos que dependen de este CPC
                $productsUsingCpc = $this->productModel->getProductsByCpcId($cpcId);
                if (!empty($productsUsingCpc)) {
                    $this->sendJsonResponse(false, "No se puede eliminar este CPC porque está siendo utilizado por " . count($productsUsingCpc) . " producto(s). Elimine primero los productos relacionados.");
                } else {
                    $result = $this->cpcModel->deleteCPC($cpcId);
                    if ($result) {
                        $this->sendJsonResponse(true, "CPC eliminado exitosamente.");
                    } else {
                        $this->sendJsonResponse(false, "Error al eliminar el CPC.");
                    }
                }
            } else {
                $this->sendJsonResponse(false, "ID de CPC no proporcionado.");
            }
        } else {
            $this->sendJsonResponse(false, "Método no permitido.");
        }
    }

    private function sendJsonResponse($success, $message, $data = null) {
        error_log("=== MODERATOR SENDING JSON RESPONSE ===");
        error_log("Success: " . ($success ? 'true' : 'false'));
        error_log("Message: " . $message);
        if ($data) {
            error_log("Data: " . print_r($data, true));
        }
        
        // Limpiar cualquier output previo
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json');
        $response = ['success' => $success, 'message' => $message];
        
        if ($data) {
            $response['data'] = $data;
        }
        
        error_log("JSON Response: " . json_encode($response));
        echo json_encode($response);
        exit;
    }

    private function handleError(Exception $e) {
        error_log("Error en ModeratorController: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
        if ($this->isAjaxRequest()) {
            $this->sendJsonResponse(false, $e->getMessage());
        } else {
            $_SESSION['error_message'] = $e->getMessage();
            header('Location: ' . url('moderator/dashboard'));
            exit();
        }
    }

    public function getUnansweredQuestions() {
        error_log("=== MODERATOR GET UNANSWERED QUESTIONS START ===");
        error_log("Is AJAX: " . ($this->isAjaxRequest() ? 'YES' : 'NO'));
        error_log("GET parameters: " . print_r($_GET, true));
        
        if ($this->isAjaxRequest()) {
            $productoId = $_GET['producto_id'] ?? null;
            error_log("Product ID: " . $productoId);
            
            if (!$productoId) {
                error_log("No product ID provided");
                $this->sendJsonResponse(false, "ID de producto requerido");
                return;
            }
            
            $questions = $this->questionModel->getAllQuestions($productoId);
            error_log("Questions found: " . count($questions));
            error_log("Questions data: " . print_r($questions, true));
            
            $this->sendJsonResponse(true, "", ['questions' => $questions]);
        } else {
            error_log("Not an AJAX request");
            $this->sendJsonResponse(false, "Método no permitido");
        }
    }

    public function answerQuestions() {
        error_log("=== MODERATOR ANSWER QUESTIONS START ===");
        error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
        error_log("Is AJAX: " . ($this->isAjaxRequest() ? 'YES' : 'NO'));
        error_log("POST data: " . print_r($_POST, true));
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->isAjaxRequest()) {
            $answersJson = $_POST['answers'] ?? '';
            error_log("Answers JSON received: " . $answersJson);
            
            $answers = json_decode($answersJson, true);
            error_log("Answers decoded: " . print_r($answers, true));
            
            if (empty($answers)) {
                error_log("No answers provided");
                $this->sendJsonResponse(false, "No hay respuestas para procesar");
                return;
            }
            
            $result = $this->questionModel->answerMultiple($answers);
            error_log("Answer multiple result: " . ($result ? 'SUCCESS' : 'FAILED'));
            
            if ($result) {
                $this->sendJsonResponse(true, "Respuestas publicadas exitosamente");
            } else {
                $this->sendJsonResponse(false, "Error al publicar las respuestas");
            }
        } else {
            error_log("Invalid request method or not AJAX");
            $this->sendJsonResponse(false, "Método no permitido");
        }
    }

    public function editProduct($id) {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $result = $this->productModel->updateProduct($id, $_POST);
                if ($result) {
                    $this->sendJsonResponse(true, "Producto actualizado exitosamente.");
                } else {
                    throw new Exception("Error al actualizar el producto.");
                }
            } else {
                $product = $this->productModel->getProductById($id);
                $cpcs = $this->cpcModel->getAllCPCs();
                $estados = $this->productStateModel->getAllStates();
                
                // Si es una petición AJAX (popup), devolver solo el formulario
                if ($this->isAjaxRequest()) {
                    require BASE_PATH . '/views/moderator/mod_edit_product_form.php';
                } else {
                    require BASE_PATH . '/views/moderator/mod_edit_product.php';
                }
            }
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }
}