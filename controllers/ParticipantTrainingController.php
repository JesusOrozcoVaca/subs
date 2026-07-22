<?php
require_once BASE_PATH . '/models/PracticaSala.php';
require_once BASE_PATH . '/models/PracticaRonda.php';
require_once BASE_PATH . '/models/PracticaInscripcion.php';
require_once BASE_PATH . '/models/PracticaBid.php';
require_once BASE_PATH . '/services/PracticaRondaService.php';
require_once BASE_PATH . '/services/ReverseAuctionEngine.php';
require_once BASE_PATH . '/utils/url_helpers.php';

class ParticipantTrainingController {
    private $salaModel;
    private $rondaModel;
    private $inscripcionModel;
    private $bidModel;
    private $rondaService;

    public function __construct() {
        $this->salaModel = new PracticaSala();
        $this->rondaModel = new PracticaRonda();
        $this->inscripcionModel = new PracticaInscripcion();
        $this->bidModel = new PracticaBid();
        $this->rondaService = new PracticaRondaService();
    }

    public function listPractices() {
        $userId = $_SESSION['user_id'];
        $rondas = $this->rondaModel->listJoinableForParticipants();
        $items = [];
        foreach ($rondas as $ronda) {
            $ronda = $this->rondaService->syncEstado($ronda);
            if (!in_array($ronda['estado'], ['programada', 'en_curso'], true)) {
                continue;
            }
            $inscription = $this->inscripcionModel->getByRondaAndUser($ronda['id'], $userId);
            $schedule = $this->rondaService->getSchedule($ronda);
            $items[] = [
                'ronda' => $ronda,
                'schedule' => $schedule,
                'inscrito' => (bool)$inscription,
                'activo' => $inscription ? (int)$inscription['activo'] === 1 : false,
                'oferta_inicial' => $inscription['oferta_inicial'] ?? null
            ];
        }
        $pageTitle = 'Prácticas de Puja';
        ob_start();
        require BASE_PATH . '/views/participant/training/list.php';
        $content = ob_get_clean();
        require BASE_PATH . '/views/participant/participant_layout.php';
    }

    public function join($rondaId = null) {
        $rondaId = $rondaId ?: ($_GET['id'] ?? $_POST['ronda_id'] ?? null);
        $userId = $_SESSION['user_id'];
        $ronda = $this->rondaModel->getById($rondaId);
        if (!$ronda || $ronda['estado_sala'] !== 'activa') {
            $_SESSION['error_message'] = 'Práctica no disponible.';
            header('Location: ' . $this->url('participant_training_list'));
            exit;
        }
        $ronda = $this->rondaService->syncEstado($ronda);
        if (!in_array($ronda['estado'], ['programada', 'en_curso'], true)) {
            $_SESSION['error_message'] = 'Esta ronda ya no acepta participantes.';
            header('Location: ' . $this->url('participant_training_list'));
            exit;
        }

        $existing = $this->inscripcionModel->getByRondaAndUser($rondaId, $userId);
        if ($existing && (int)$existing['activo'] === 1) {
            header('Location: ' . $this->url('participant_training_puja', ['id' => $rondaId]));
            exit;
        }
        if ($existing && (int)$existing['activo'] !== 1) {
            $_SESSION['error_message'] = 'Su inscripción fue desactivada por el administrador.';
            header('Location: ' . $this->url('participant_training_list'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $oferta = ReverseAuctionEngine::parseMoneyValue($_POST['oferta_inicial'] ?? '');
            if ($oferta === null) {
                $raw = str_replace(',', '.', trim($_POST['oferta_inicial'] ?? ''));
                $oferta = is_numeric($raw) ? (float)$raw : null;
            }
            $presupuesto = (float)$ronda['presupuesto_referencial'];
            if ($oferta === null || $oferta <= 0) {
                $_SESSION['error_message'] = 'Ingrese una oferta inicial válida.';
                header('Location: ' . $this->url('participant_training_join', ['id' => $rondaId]));
                exit;
            }
            if ($oferta > $presupuesto) {
                $_SESSION['error_message'] = 'La oferta inicial no puede superar el presupuesto referencial.';
                header('Location: ' . $this->url('participant_training_join', ['id' => $rondaId]));
                exit;
            }

            $result = $this->inscripcionModel->join($rondaId, $userId, $oferta);
            if (!$result['success']) {
                $_SESSION['error_message'] = $result['message'];
                header('Location: ' . $this->url('participant_training_join', ['id' => $rondaId]));
                exit;
            }
            header('Location: ' . $this->url('participant_training_puja', ['id' => $rondaId]));
            exit;
        }

        $schedule = $this->rondaService->getSchedule($ronda);
        $pageTitle = 'Ingresar a práctica';
        ob_start();
        require BASE_PATH . '/views/participant/training/join.php';
        $content = ob_get_clean();
        require BASE_PATH . '/views/participant/participant_layout.php';
    }

    public function pujaWindow($rondaId = null) {
        $rondaId = $rondaId ?: ($_GET['id'] ?? null);
        $userId = $_SESSION['user_id'];
        $ronda = $this->rondaModel->getById($rondaId);
        if (!$ronda) {
            header('Location: ' . $this->url('participant_training_list'));
            exit;
        }
        $ronda = $this->rondaService->syncEstado($ronda);
        $inscription = $this->inscripcionModel->getActiveByRondaAndUser($rondaId, $userId);
        if (!$inscription) {
            $_SESSION['error_message'] = 'Debe ingresar su oferta inicial para participar.';
            header('Location: ' . $this->url('participant_training_join', ['id' => $rondaId]));
            exit;
        }

        if ($ronda['estado'] === 'finalizada') {
            header('Location: ' . $this->url('participant_training_summary', ['id' => $rondaId]));
            exit;
        }
        if ($ronda['estado'] === 'cancelada') {
            $_SESSION['error_message'] = 'La ronda fue cancelada.';
            header('Location: ' . $this->url('participant_training_list'));
            exit;
        }

        $schedule = $this->rondaService->getSchedule($ronda);
        $userLastBid = $this->bidModel->getUserLastBid($rondaId, $userId);
        $lowestBid = $this->bidModel->getLowestBid($rondaId);
        $status = ReverseAuctionEngine::buildStatusData($userLastBid, $lowestBid);
        $initialOfferValue = (float)$inscription['oferta_inicial'];
        $variationPercent = (float)$ronda['variacion_minima'];
        $variationAmount = round($initialOfferValue * ($variationPercent / 100), 2);
        $blockPuja = false;
        $pujaBlockMessage = '';

        require BASE_PATH . '/views/participant/training/puja_window.php';
    }

    public function submitBid() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->isAjaxRequest()) {
            $this->sendJsonResponse(false, 'Método no permitido.');
            return;
        }

        $rondaId = $_POST['ronda_id'] ?? null;
        $valorRaw = $_POST['valor'] ?? '';
        $userId = $_SESSION['user_id'] ?? null;
        if (!$rondaId || !$userId) {
            $this->sendJsonResponse(false, 'Datos incompletos para registrar la puja.');
            return;
        }

        $ronda = $this->rondaModel->getById($rondaId);
        if (!$ronda) {
            $this->sendJsonResponse(false, 'Ronda no encontrada.');
            return;
        }
        $ronda = $this->rondaService->syncEstado($ronda);
        if ($ronda['estado'] !== 'en_curso') {
            $msg = $ronda['estado'] === 'programada'
                ? 'La puja aún no ha iniciado.'
                : 'La puja ha finalizado.';
            $this->sendJsonResponse(false, $msg);
            return;
        }

        $schedule = $this->rondaService->getSchedule($ronda);
        $window = ReverseAuctionEngine::evaluateWindow($schedule);
        if (!$window['open']) {
            if ($window['phase'] === 'after') {
                $this->rondaService->finalizeRonda($ronda);
            }
            $this->sendJsonResponse(false, $window['message']);
            return;
        }

        $inscription = $this->inscripcionModel->getActiveByRondaAndUser($rondaId, $userId);
        if (!$inscription) {
            $this->sendJsonResponse(false, 'No está inscrito en esta práctica.');
            return;
        }

        $bidModel = $this->bidModel;
        $result = ReverseAuctionEngine::submitBid($valorRaw, [
            'presupuesto_referencial' => $ronda['presupuesto_referencial'],
            'variacion_minima' => $ronda['variacion_minima'],
            'oferta_inicial' => (float)$inscription['oferta_inicial'],
            'user_last_bid' => $bidModel->getUserLastBid($rondaId, $userId),
            'lowest_bid' => $bidModel->getLowestBid($rondaId),
            'create_callback' => function ($valor, $fechaMs) use ($bidModel, $rondaId, $userId) {
                return $bidModel->create([
                    'ronda_id' => $rondaId,
                    'usuario_id' => $userId,
                    'valor' => $valor,
                    'fecha_puja_ms' => $fechaMs
                ]);
            }
        ]);

        if (!$result['success']) {
            $this->sendJsonResponse(false, $result['message']);
            return;
        }

        $status = ReverseAuctionEngine::buildStatusData(
            $bidModel->getUserLastBid($rondaId, $userId),
            $bidModel->getLowestBid($rondaId)
        );
        $this->sendJsonResponse(true, $result['message'], $status);
    }

    public function pujaStatus($rondaId = null) {
        if (!$this->isAjaxRequest()) {
            $this->sendJsonResponse(false, 'Método no permitido.');
            return;
        }
        $rondaId = $rondaId ?: ($_GET['id'] ?? $_GET['ronda_id'] ?? null);
        $userId = $_SESSION['user_id'] ?? null;
        if (!$rondaId || !$userId) {
            $this->sendJsonResponse(false, 'Datos incompletos.');
            return;
        }

        $ronda = $this->rondaModel->getById($rondaId);
        if (!$ronda) {
            $this->sendJsonResponse(false, 'Ronda no encontrada.');
            return;
        }
        $ronda = $this->rondaService->syncEstado($ronda);
        $inscription = $this->inscripcionModel->getActiveByRondaAndUser($rondaId, $userId);
        if (!$inscription) {
            $this->sendJsonResponse(false, 'No está inscrito en esta práctica.');
            return;
        }

        $status = ReverseAuctionEngine::buildStatusData(
            $this->bidModel->getUserLastBid($rondaId, $userId),
            $this->bidModel->getLowestBid($rondaId)
        );
        $status['ronda_estado'] = $ronda['estado'];
        $schedule = $this->rondaService->getSchedule($ronda);
        $status['ended'] = $ronda['estado'] === 'finalizada' || ($schedule && time() > (int)$schedule['end_ts']);
        $this->sendJsonResponse(true, '', $status);
    }

    public function summary($rondaId = null) {
        $rondaId = $rondaId ?: ($_GET['id'] ?? null);
        $userId = $_SESSION['user_id'];
        $ronda = $this->rondaModel->getById($rondaId);
        if (!$ronda) {
            header('Location: ' . $this->url('participant_training_list'));
            exit;
        }
        $ronda = $this->rondaService->syncEstado($ronda);
        $ronda = $this->rondaModel->getById($rondaId);
        $inscription = $this->inscripcionModel->getByRondaAndUser($rondaId, $userId);
        if (!$inscription) {
            $_SESSION['error_message'] = 'No participó en esta ronda.';
            header('Location: ' . $this->url('participant_training_list'));
            exit;
        }
        if ($ronda['estado'] !== 'finalizada') {
            header('Location: ' . $this->url('participant_training_puja', ['id' => $rondaId]));
            exit;
        }
        $summary = $this->rondaService->buildSummary($ronda);
        $schedule = $this->rondaService->getSchedule($ronda);
        $pageTitle = 'Resumen de práctica';
        ob_start();
        require BASE_PATH . '/views/participant/training/summary.php';
        $content = ob_get_clean();
        require BASE_PATH . '/views/participant/participant_layout.php';
    }

    private function url($action, $params = []) {
        $url = BASE_URL . 'index.php?action=' . $action;
        if (!empty($params)) {
            $url .= '&' . http_build_query($params);
        }
        return $url;
    }

    private function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    private function sendJsonResponse($success, $message, $data = null) {
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
        exit;
    }
}
