<?php
require_once BASE_PATH . '/models/User.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login() {
        // Debug: Log para verificar el estado de login
        error_log("AuthController::login() called");
        error_log("isLoggedIn(): " . ($this->isLoggedIn() ? 'true' : 'false'));
        error_log("Session data: " . print_r($_SESSION, true));
        
        // TEMPORAL: Comentar redirección automática para romper el bucle
        /*
        if ($this->isLoggedIn()) {
            error_log("User is already logged in, redirecting to dashboard");
            $this->redirectToDashboard();
        }
        */

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = $this->userModel->authenticate($username, $password);
            if ($user) {
                $this->createSession($user);
                if ($this->isAjaxRequest()) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'redirect' => $this->getDashboardUrl()]);
                    exit();
                } else {
                    $this->redirectToDashboard();
                }
            } else {
                if ($this->isAjaxRequest()) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Credenciales inválidas']);
                    exit();
                } else {
                    $error = "Credenciales inválidas";
                    require_once BASE_PATH . '/views/auth/login.php';
                }
            }
        } else {
            require_once BASE_PATH . '/views/auth/login.php';
        }
    }

    public function logout() {
        $this->destroySession();
        header('Location: ' . LOGIN_URL);
        exit();
    }

    private function createSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nivel_acceso'] = $user['nivel_acceso'];
        $_SESSION['nombre_completo'] = $user['nombre_completo'];
        
        // Debug: Log para verificar que la sesión se crea
        error_log("Session created for user: " . $user['id'] . " with level: " . $user['nivel_acceso']);
    }

    private function destroySession() {
        session_unset();
        session_destroy();
    }

    private function isLoggedIn() {
        $isLoggedIn = isset($_SESSION['user_id']);
        error_log("isLoggedIn() check: user_id=" . ($_SESSION['user_id'] ?? 'not set') . ", result=" . ($isLoggedIn ? 'true' : 'false'));
        return $isLoggedIn;
    }

    private function redirectToDashboard() {
        header('Location: ' . $this->getDashboardUrl());
        exit();
    }

    private function getDashboardUrl() {
        switch ($_SESSION['nivel_acceso']) {
            case 1:
                return ADMIN_DASHBOARD_URL;
            case 2:
                return MODERATOR_DASHBOARD_URL;
            case 3:
            default:
                return PARTICIPANT_DASHBOARD_URL;
        }
    }

    private function isAjaxRequest() {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ||
               (!empty($_SERVER['HTTP_ACCEPT']) && 
                strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }
}