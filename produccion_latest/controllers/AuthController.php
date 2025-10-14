<?php
require_once BASE_PATH . '/models/User.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login() {
        if ($this->isLoggedIn()) {
            $this->redirectToDashboard();
        }

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
    }

    private function destroySession() {
        session_unset();
        session_destroy();
    }

    private function isLoggedIn() {
        return isset($_SESSION['user_id']);
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
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}