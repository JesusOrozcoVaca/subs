<?php
require_once BASE_PATH . '/models/User.php';
require_once BASE_PATH . '/models/Product.php';
require_once BASE_PATH . '/models/CPC.php';

class AdminController {
    private $userModel;
    private $productModel;
    private $cpcModel;

    public function __construct() {
        $this->userModel = new User();
        $this->productModel = new Product();
        $this->cpcModel = new CPC();
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
            $product = $this->productModel->getProductById($id);
            // Aquí implementaremos la lógica para gestionar el producto según los requerimientos
            require BASE_PATH . '/views/admin/manage_product.php';
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    private function isAjaxRequest() {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ||
               (!empty($_SERVER['HTTP_ACCEPT']) && 
                strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
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
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    }
}