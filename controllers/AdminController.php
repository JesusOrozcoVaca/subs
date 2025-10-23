<?php
require_once BASE_PATH . '/models/User.php';
require_once BASE_PATH . '/models/Product.php';
require_once BASE_PATH . '/models/CPC.php';
require_once BASE_PATH . '/models/ProductState.php';

class AdminController {
    private $userModel;
    private $productModel;
    private $cpcModel;
    private $productStateModel;

    public function __construct() {
        $this->userModel = new User();
        $this->productModel = new Product();
        $this->cpcModel = new CPC();
        $this->productStateModel = new ProductState();
    }

    public function dashboard() {
        try {
            $users = $this->userModel->getAllUsers();
            $products = $this->productModel->getAllProducts();
            $cpcs = $this->cpcModel->getAllCPCs();
            
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
                $result = $this->userModel->createUser($_POST);
                if ($result) {
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
            } catch (Exception $e) {
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

    private function sendJsonResponse($success, $message) {
        error_log("=== SENDING JSON RESPONSE ===");
        error_log("Success: " . ($success ? 'true' : 'false'));
        error_log("Message: " . $message);
        
        // Limpiar cualquier output previo
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json');
        $response = ['success' => $success, 'message' => $message];
        error_log("JSON Response: " . json_encode($response));
        echo json_encode($response);
        exit;
    }
}