<?php
require_once BASE_PATH . '/models/Product.php';
require_once BASE_PATH . '/models/CPC.php';
require_once BASE_PATH . '/models/User.php';
require_once BASE_PATH . '/models/Question.php';
require_once BASE_PATH . '/models/Bid.php';

class ModeratorController {
    private $productModel;
    private $cpcModel;
    private $userModel;
    private $questionModel;
    private $bidModel;

    public function __construct() {
        $this->productModel = new Product();
        $this->cpcModel = new CPC();
        $this->userModel = new User();
        $this->questionModel = new Question();
        $this->bidModel = new Bid();
    }

    private function isAjaxRequest() {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }

    public function dashboard() {
        $products = $this->productModel->getAllActive();
        
        if ($this->isAjaxRequest()) {
            require_once BASE_PATH . '/views/moderator/mod_dashboard_content.php';
        } else {
            require_once BASE_PATH . '/views/moderator/mod_dashboard.php';
        }
    }

    public function manageProduct($id) {
        $product = $this->productModel->getProductById($id);
        $participants = $this->productModel->getParticipants($id);
        $questions = $this->questionModel->getProductQuestions($id);
        $bids = $this->bidModel->getProductBids($id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'];
            switch ($action) {
                case 'update_status':
                    $newStatus = $_POST['new_status'];
                    $this->productModel->updateProductStatus($id, $newStatus);
                    $_SESSION['success_message'] = "Estado del producto actualizado exitosamente.";
                    break;
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
        
        require_once BASE_PATH . '/views/moderator/mod_manage_product.php';
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
                    case 'edit':
                        $result = $this->cpcModel->updateCPC($_POST['id'], $_POST);
                        if ($result) {
                            $this->sendJsonResponse(true, "CPC actualizado exitosamente.");
                        } else {
                            $this->sendJsonResponse(false, "Error al actualizar el CPC.");
                        }
                        break;
                    case 'delete':
                        $id = $_POST['id'] ?? null;
                        if ($id) {
                            $result = $this->cpcModel->deleteCPC($id);
                            if ($result) {
                                $this->sendJsonResponse(true, "CPC eliminado exitosamente.");
                            } else {
                                $this->sendJsonResponse(false, "Error al eliminar el CPC.");
                            }
                        } else {
                            $this->sendJsonResponse(false, "ID de CPC no proporcionado.");
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
                    // Respuesta simple: siempre JSON con mensaje de éxito
                    $this->sendJsonResponse(true, "El cambio ha sido realizado con éxito");
                } else {
                    $this->sendJsonResponse(false, "Error al actualizar el CPC.");
                }
            } else {
                $cpc = $this->cpcModel->getCPCById($id);
                if (!$cpc) {
                    throw new Exception("CPC no encontrado.");
                }
                
                if ($this->isAjaxRequest()) {
                    require_once BASE_PATH . '/views/moderator/mod_edit_cpc_content.php';
                } else {
                    require_once BASE_PATH . '/views/moderator/mod_edit_cpc.php';
                }
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

    private function sendJsonResponse($success, $message, $data = []) {
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
        exit();
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
}