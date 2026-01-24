<?php
require_once BASE_PATH . '/models/User.php';
require_once BASE_PATH . '/utils/logger.php';
require_once BASE_PATH . '/models/OfferRating.php';
require_once BASE_PATH . '/models/Product.php';
require_once BASE_PATH . '/models/CPC.php';
require_once BASE_PATH . '/models/ProductState.php';
require_once BASE_PATH . '/services/PyrPdfGenerator.php';

class AdminController {
    private $userModel;
    private $productModel;
    private $cpcModel;
    private $productStateModel;
    private $offerRatingModel;

    public function __construct() {
        $this->userModel = new User();
        $this->productModel = new Product();
        $this->cpcModel = new CPC();
        $this->productStateModel = new ProductState();
        $this->offerRatingModel = new OfferRating();
    }

    public function dashboard() {
        try {
            $perPage = 5;
            $usersPage = max(1, (int)($_GET['users_page'] ?? 1));
            $productsPage = max(1, (int)($_GET['products_page'] ?? 1));
            $cpcsPage = max(1, (int)($_GET['cpcs_page'] ?? 1));

            $usersTotal = $this->userModel->getUsersCount();
            $productsTotal = $this->productModel->getProductsCount();
            $cpcsTotal = $this->cpcModel->getCpcsCount();

            $usersTotalPages = max(1, (int)ceil($usersTotal / $perPage));
            $productsTotalPages = max(1, (int)ceil($productsTotal / $perPage));
            $cpcsTotalPages = max(1, (int)ceil($cpcsTotal / $perPage));

            $usersPage = min($usersPage, $usersTotalPages);
            $productsPage = min($productsPage, $productsTotalPages);
            $cpcsPage = min($cpcsPage, $cpcsTotalPages);

            $users = $this->userModel->getUsersPaginated($perPage, ($usersPage - 1) * $perPage);
            $products = $this->productModel->getProductsPaginated($perPage, ($productsPage - 1) * $perPage);
            $cpcs = $this->cpcModel->getCpcsPaginated($perPage, ($cpcsPage - 1) * $perPage);

            $usersPagination = [
                'page' => $usersPage,
                'total_pages' => $usersTotalPages
            ];
            $productsPagination = [
                'page' => $productsPage,
                'total_pages' => $productsTotalPages
            ];
            $cpcsPagination = [
                'page' => $cpcsPage,
                'total_pages' => $cpcsTotalPages
            ];
            
            if ($this->isAjaxRequest()) {
                require BASE_PATH . '/views/admin/dashboard_content.php';
            } else {
                require BASE_PATH . '/views/admin/dashboard.php';
            }
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    public function createProduct() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $result = $this->productModel->createProduct($_POST);
                if ($result) {
                    if ($this->isAjaxRequest()) {
                        $this->sendJsonResponse(true, "Producto creado exitosamente.");
                    } else {
                        $_SESSION['success_message'] = "Producto creado exitosamente.";
                        header('Location: ' . url('admin/dashboard'));
                        exit();
                    }
                } else {
                    throw new Exception("Error al crear el producto.");
                }
            } catch (Exception $e) {
                $this->handleError($e);
            }
        }
        $cpcs = $this->cpcModel->getAllCPCs();
        $estados = $this->productStateModel->getAllStates();
        
        if ($this->isAjaxRequest()) {
            require BASE_PATH . '/views/admin/create_product.php';
        } else {
            require BASE_PATH . '/views/admin/dashboard.php';
        }
    }

    public function createUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                app_log('AdminController.createUser POST start', [
                    'is_ajax' => $this->isAjaxRequest(),
                    'post_keys' => array_keys($_POST),
                    'post_data' => $_POST
                ]);
                $correo = isset($_POST['correo_electronico']) ? trim((string)$_POST['correo_electronico']) : '';
                if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                    $message = "Correo electrónico inválido. Verifica el formato.";
                    if ($this->isAjaxRequest()) {
                        $this->sendJsonResponse(false, $message);
                    } else {
                        $_SESSION['error_message'] = $message;
                        header('Location: ' . url('admin/dashboard'));
                        exit;
                    }
                }

                if ($this->userModel->emailExists($correo)) {
                    $message = "El correo electrónico ya existe y no puede ser duplicado.\nCorreo: {$correo}";
                    if ($this->isAjaxRequest()) {
                        $this->sendJsonResponse(false, $message);
                    } else {
                        $_SESSION['error_message'] = $message;
                        header('Location: ' . url('admin/dashboard'));
                        exit;
                    }
                }

                $telefono = isset($_POST['telefono']) ? trim((string)$_POST['telefono']) : '';
                if ($telefono === '' || !preg_match('/^\d{10}$/', $telefono)) {
                    $message = "Teléfono inválido. Asegúrate de haber ingresado el teléfono correcto.";
                    if ($this->isAjaxRequest()) {
                        $this->sendJsonResponse(false, $message);
                    } else {
                        $_SESSION['error_message'] = $message;
                        header('Location: ' . url('admin/dashboard'));
                        exit;
                    }
                }

                if ($this->userModel->phoneExists($telefono)) {
                    $message = "El teléfono ya existe y no puede ser duplicado.\nTeléfono: {$telefono}";
                    if ($this->isAjaxRequest()) {
                        $this->sendJsonResponse(false, $message);
                    } else {
                        $_SESSION['error_message'] = $message;
                        header('Location: ' . url('admin/dashboard'));
                        exit;
                    }
                }
                $result = $this->userModel->createUser($_POST);
                if ($result) {
                    app_log('AdminController.createUser POST success', [
                        'is_ajax' => $this->isAjaxRequest()
                    ]);
                    if ($this->isAjaxRequest()) {
                        $this->sendJsonResponse(true, "Usuario creado exitosamente.");
                    } else {
                        $_SESSION['success_message'] = "Usuario creado exitosamente.";
                        header('Location: ' . url('admin/dashboard'));
                        exit();
                    }
                } else {
                    throw new Exception("Error al crear el usuario.");
                }
            } catch (PDOException $e) {
                $isDuplicate = stripos($e->getMessage(), 'Duplicate entry') !== false
                    && stripos($e->getMessage(), 'cedula') !== false;
                $cedula = isset($_POST['cedula']) ? trim((string)$_POST['cedula']) : '';
                $cedulaValue = $cedula !== '' ? $cedula : 'N/D';
                $message = $isDuplicate
                    ? "El usuario ya existe y no puede ser creado como duplicado. Asegúrate de revisar los datos:\nCédula: {$cedulaValue}"
                    : $e->getMessage();

                app_log('AdminController.createUser POST error', [
                    'message' => $e->getMessage(),
                    'is_duplicate' => $isDuplicate,
                    'trace' => $e->getTraceAsString()
                ]);

                if ($this->isAjaxRequest()) {
                    $this->sendJsonResponse(false, $message);
                } else {
                    $_SESSION['error_message'] = $message;
                    header('Location: ' . url('admin/dashboard'));
                    exit;
                }
            } catch (Exception $e) {
                app_log('AdminController.createUser POST error', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $this->handleError($e);
            }
        }
        
        if ($this->isAjaxRequest()) {
            require BASE_PATH . '/views/admin/create_user.php';
        } else {
            require BASE_PATH . '/views/admin/dashboard.php';
        }
    }

    public function deleteUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['id'] ?? null;
            if ($userId) {
                $result = $this->userModel->deleteUser($userId);
                if ($result) {
                    $this->sendJsonResponse(true, "Usuario eliminado exitosamente.");
                } else {
                    $this->sendJsonResponse(false, "Error al eliminar el usuario.");
                }
            } else {
                $this->sendJsonResponse(false, "ID de usuario no proporcionado.");
            }
        } else {
            $this->sendJsonResponse(false, "Método no permitido.");
        }
    }

    public function deleteProduct() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = $_POST['id'] ?? null;
            if ($productId) {
                $result = $this->productModel->deleteProduct($productId);
                if ($result) {
                    $this->sendJsonResponse(true, "Producto eliminado exitosamente.");
                } else {
                    $this->sendJsonResponse(false, "Error al eliminar el producto.");
                }
            } else {
                $this->sendJsonResponse(false, "ID de producto no proporcionado.");
            }
        } else {
            $this->sendJsonResponse(false, "Método no permitido.");
        }
    }

    public function deleteCPC() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cpcId = $_POST['id'] ?? null;
            if ($cpcId) {
                $result = $this->cpcModel->deleteCPC($cpcId);
                if ($result) {
                    $this->sendJsonResponse(true, "CPC eliminado exitosamente.");
                } else {
                    $this->sendJsonResponse(false, "Error al eliminar el CPC.");
                }
            } else {
                $this->sendJsonResponse(false, "ID de CPC no proporcionado.");
            }
        } else {
            $this->sendJsonResponse(false, "Método no permitido.");
        }
    }

    public function createCPC() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $result = $this->cpcModel->createCPC($_POST);
                if ($result) {
                    if ($this->isAjaxRequest()) {
                        $this->sendJsonResponse(true, "CPC creado exitosamente.");
                    } else {
                        $_SESSION['success_message'] = "CPC creado exitosamente.";
                        header('Location: ' . url('admin/dashboard'));
                        exit();
                    }
                } else {
                    throw new Exception("Error al crear el CPC.");
                }
            } catch (Exception $e) {
                $this->handleError($e);
            }
        }
        
        if ($this->isAjaxRequest()) {
            require BASE_PATH . '/views/admin/create_cpc.php';
        } else {
            require BASE_PATH . '/views/admin/dashboard.php';
        }
    }

    public function editUser($id) {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $correo = isset($_POST['correo_electronico']) ? trim((string)$_POST['correo_electronico']) : '';
                if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                    $this->sendJsonResponse(false, "Correo electrónico inválido. Verifica el formato.");
                    return;
                }
                if ($this->userModel->emailExists($correo, $id)) {
                    $this->sendJsonResponse(false, "El correo electrónico ya existe y no puede ser duplicado.\nCorreo: {$correo}");
                    return;
                }
                $telefono = isset($_POST['telefono']) ? trim((string)$_POST['telefono']) : '';
                if ($telefono === '' || !preg_match('/^\d{10}$/', $telefono)) {
                    $this->sendJsonResponse(false, "Teléfono inválido. Asegúrate de haber ingresado el teléfono correcto.");
                    return;
                }
                if ($this->userModel->phoneExists($telefono, $id)) {
                    $this->sendJsonResponse(false, "El teléfono ya existe y no puede ser duplicado.\nTeléfono: {$telefono}");
                    return;
                }
                $result = $this->userModel->updateUser($id, $_POST);
                if ($result) {
                    $this->sendJsonResponse(true, "Usuario actualizado exitosamente.");
                } else {
                    throw new Exception("Error al actualizar el usuario.");
                }
            } else {
                $user = $this->userModel->getUserById($id);
                if (!$user) {
                    throw new Exception("Usuario no encontrado.");
                }
                require BASE_PATH . '/views/admin/edit_user.php';
            }
        } catch (Exception $e) {
            $this->handleError($e);
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
                require BASE_PATH . '/views/admin/edit_product.php';
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
                require BASE_PATH . '/views/admin/edit_cpc.php';
            }
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    public function toggleUserStatus() {
        error_log("toggleUserStatus method called");
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(false, "Método no permitido.");
            return;
        }
    
        $userId = $_POST['user_id'] ?? null;
        error_log("User ID received: " . ($userId ?? 'null'));
    
        if (!$userId) {
            $this->sendJsonResponse(false, "ID de usuario no proporcionado.");
            return;
        }
    
        $user = $this->userModel->getUserById($userId);
        if (!$user) {
            error_log("User not found for ID: " . $userId);
            $this->sendJsonResponse(false, "Usuario no encontrado.");
            return;
        }
    
        error_log("User found: " . json_encode($user));
        $newStatus = $user['estado'] === 'activo' ? 'inactivo' : 'activo';
        $result = $newStatus === 'inactivo' ? 
            $this->userModel->deactivateUser($userId) : 
            $this->userModel->activateUser($userId);
    
        if ($result) {
            error_log("User status updated successfully to: " . $newStatus);
            $this->sendJsonResponse(true, "Usuario " . ($newStatus === 'activo' ? 'activado' : 'desactivado') . " exitosamente.");
        } else {
            error_log("Failed to update user status");
            $this->sendJsonResponse(false, "Error al cambiar el estado del usuario.");
        }
    }

    public function manageProduct($id) {
        try {
            error_log("=== MANAGE PRODUCT START ===");
            error_log("Product ID: " . $id);
            
            // Manejar cambio de estado
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_status') {
                error_log("=== CHANGE STATUS REQUEST ===");
                error_log("Is AJAX: " . ($this->isAjaxRequest() ? 'YES' : 'NO'));
                error_log("X-Requested-With: " . ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? 'NOT SET'));
                error_log("Accept: " . ($_SERVER['HTTP_ACCEPT'] ?? 'NOT SET'));
                
                $estadoId = $_POST['estado_id'];
                $result = $this->productModel->updateProductStatus($id, $estadoId);
                
                if ($this->isAjaxRequest()) {
                    error_log("=== SENDING JSON RESPONSE ===");
                    if ($result) {
                        $this->sendJsonResponse(true, "Estado del producto actualizado exitosamente.");
                    } else {
                        $this->sendJsonResponse(false, "Error al actualizar el estado del producto.");
                    }
                    return; // Salir para evitar cargar la vista
                } else {
                    error_log("=== NOT AJAX, USING SESSION MESSAGES ===");
                    if ($result) {
                        $_SESSION['success_message'] = "Estado del producto actualizado exitosamente.";
                    } else {
                        $_SESSION['error_message'] = "Error al actualizar el estado del producto.";
                    }
                }
            }
            
            $product = $this->productModel->getProductById($id);
            error_log("Product data: " . print_r($product, true));
            
            if (!$product) {
                error_log("Product not found for ID: " . $id);
                throw new Exception("Producto no encontrado.");
            }
            
            $estados = $this->productStateModel->getAllStates();
            
            error_log("=== LOADING MANAGE PRODUCT CONTENT ===");
            // Cargar el dashboard con el contenido de gestión de producto
            if ($this->isAjaxRequest()) {
                require BASE_PATH . '/views/admin/manage_product_content.php';
            } else {
                // Cargar el dashboard principal y el contenido de gestión
                require BASE_PATH . '/views/admin/dashboard.php';
            }
            error_log("=== MANAGE PRODUCT CONTENT LOADED ===");
        } catch (Exception $e) {
            error_log("Error in manageProduct: " . $e->getMessage());
            $this->handleError($e);
        }
    }

    private function isAjaxRequest() {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                   strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        
        error_log("=== IS AJAX REQUEST DEBUG ===");
        error_log("HTTP_X_REQUESTED_WITH: " . ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? 'NOT SET'));
        error_log("Is AJAX: " . ($isAjax ? 'YES' : 'NO'));
        
        return $isAjax;
    }

    private function handleError(Exception $e) {
        error_log("Error manejado: " . $e->getMessage());
        if ($this->isAjaxRequest()) {
            $this->sendJsonResponse(false, $e->getMessage());
        } else {
            $_SESSION['error_message'] = $e->getMessage();
            header('Location: ' . url('admin/dashboard'));
            exit;
        }
    }

    private function sendJsonResponse($success, $message, $data = null) {
        error_log("=== SENDING JSON RESPONSE ===");
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

    public function getUnansweredQuestions() {
        error_log("=== GET UNANSWERED QUESTIONS START ===");
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
            
            require_once BASE_PATH . '/models/Question.php';
            $questionModel = new Question();
            
            $questions = $questionModel->getAllQuestions($productoId);
            error_log("Questions found: " . count($questions));
            error_log("Questions data: " . print_r($questions, true));
            
            $this->sendJsonResponse(true, "", ['questions' => $questions]);
        } else {
            error_log("Not an AJAX request");
            $this->sendJsonResponse(false, "Método no permitido");
        }
    }

    public function answerQuestions() {
        error_log("=== ANSWER QUESTIONS START ===");
        error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
        error_log("Is AJAX: " . ($this->isAjaxRequest() ? 'YES' : 'NO'));
        error_log("POST data: " . print_r($_POST, true));
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->isAjaxRequest()) {
            $answersJson = $_POST['answers'] ?? '';
            $productId = isset($_POST['producto_id']) ? (int)$_POST['producto_id'] : null;

            error_log("Answers JSON received: " . $answersJson);
            error_log("Product ID for acta generation (admin): " . ($productId ?? 'null'));
            
            $answers = json_decode($answersJson, true);
            error_log("Answers decoded: " . print_r($answers, true));
            
            if (empty($answers)) {
                error_log("No answers provided");
                $this->sendJsonResponse(false, "No hay respuestas para procesar");
                return;
            }

            require_once BASE_PATH . '/models/Question.php';
            $questionModel = new Question();

            if (!$productId) {
                $firstQuestionId = null;
                foreach ($answers as $questionId => $value) {
                    $firstQuestionId = $questionId;
                    break;
                }
                if ($firstQuestionId) {
                    $questionData = $questionModel->getQuestionById($firstQuestionId);
                    if ($questionData && isset($questionData['producto_id'])) {
                        $productId = (int)$questionData['producto_id'];
                        error_log("Product ID deduced from question {$firstQuestionId} (admin): {$productId}");
                    } else {
                        error_log("Unable to deduce product ID from question {$firstQuestionId} (admin)");
                    }
                }
            }
            
            $result = $questionModel->answerMultiple($answers);
            error_log("Answer multiple result: " . ($result ? 'SUCCESS' : 'FAILED'));
            
            if ($result) {
                $actaInfo = null;

                if ($productId) {
                    $product = $this->productModel->getProductById($productId);
                    $questions = $questionModel->getAllQuestions($productId);

                    if ($product && $questions !== false) {
                        $answeredQuestions = array_values(array_filter($questions, function ($question) {
                            return !empty($question['respuesta']);
                        }));

                        if (!empty($answeredQuestions)) {
                            $actaInfo = PyrPdfGenerator::generate($product, $answeredQuestions);
                            error_log("Acta generation result (admin): " . print_r($actaInfo, true));
                        } else {
                            error_log("Acta generation skipped (admin): no answered questions for product {$productId}");
                        }
                    }
                }

                $this->sendJsonResponse(true, "Respuestas publicadas exitosamente", [
                    'acta' => $actaInfo
                ]);
            } else {
                $this->sendJsonResponse(false, "Error al publicar las respuestas");
            }
        } else {
            error_log("Invalid request method or not AJAX");
            $this->sendJsonResponse(false, "Método no permitido");
        }
    }

    public function getOfferRatings() {
        if ($this->isAjaxRequest()) {
            $productoId = $_GET['producto_id'] ?? null;

            if (!$productoId || !is_numeric($productoId)) {
                $this->sendJsonResponse(false, "ID de producto requerido");
                return;
            }

            $ratings = $this->offerRatingModel->getProductOfferRatings((int)$productoId);
            $this->sendJsonResponse(true, "", ['ratings' => $ratings]);
        } else {
            $this->sendJsonResponse(false, "Método no permitido");
        }
    }

    public function saveOfferRating() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->isAjaxRequest()) {
            $productoId = $_POST['producto_id'] ?? null;
            $usuarioId = $_POST['usuario_id'] ?? null;
            $calificacion = trim($_POST['calificacion'] ?? '');
            $comentario = trim($_POST['comentario'] ?? '');

            if (!$productoId || !is_numeric($productoId) || !$usuarioId || !is_numeric($usuarioId)) {
                $this->sendJsonResponse(false, "Datos incompletos para calificar");
                return;
            }

            $allowed = ['Cumple', 'No Cumple'];
            if (!in_array($calificacion, $allowed, true)) {
                $this->sendJsonResponse(false, "Calificación no válida");
                return;
            }

            if (strlen($comentario) > 300) {
                $this->sendJsonResponse(false, "El comentario no puede exceder 300 caracteres");
                return;
            }

            if (!$this->offerRatingModel->hasSubmission((int)$productoId, (int)$usuarioId)) {
                $this->sendJsonResponse(false, "El usuario no tiene una oferta registrada en este producto");
                return;
            }

            $result = $this->offerRatingModel->upsertRating((int)$productoId, (int)$usuarioId, $calificacion, $comentario);
            if ($result) {
                $this->sendJsonResponse(true, "Calificación guardada exitosamente");
            } else {
                $this->sendJsonResponse(false, "No se pudo guardar la calificación");
            }
        } else {
            $this->sendJsonResponse(false, "Método no permitido");
        }
    }
}