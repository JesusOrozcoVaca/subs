<?php
require_once BASE_PATH . '/models/PracticaSala.php';
require_once BASE_PATH . '/models/PracticaRonda.php';
require_once BASE_PATH . '/models/PracticaInscripcion.php';
require_once BASE_PATH . '/models/PracticaBid.php';
require_once BASE_PATH . '/services/PracticaRondaService.php';
require_once BASE_PATH . '/services/ReverseAuctionEngine.php';
require_once BASE_PATH . '/utils/url_helpers.php';

class AdminTrainingController {
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

    public function dashboard() {
        $salas = $this->salaModel->getAll(true);
        require BASE_PATH . '/views/admin/training/dashboard.php';
    }

    public function createSala() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->validateSalaInput($_POST);
            if (!$data['ok']) {
                $_SESSION['error_message'] = $data['message'];
                header('Location: ' . $this->url('admin_training_create_sala'));
                exit;
            }
            $id = $this->salaModel->create(array_merge($data['data'], [
                'created_by' => $_SESSION['user_id'],
                'estado_sala' => $_POST['estado_sala'] ?? 'activa'
            ]));
            if ($id) {
                $_SESSION['success_message'] = 'Sala de práctica creada.';
                header('Location: ' . $this->url('admin_training_view_sala', ['id' => $id]));
            } else {
                $_SESSION['error_message'] = 'No se pudo crear la sala.';
                header('Location: ' . $this->url('admin_training_create_sala'));
            }
            exit;
        }
        $sala = null;
        require BASE_PATH . '/views/admin/training/sala_form.php';
    }

    public function editSala($id = null) {
        $id = $id ?: ($_GET['id'] ?? null);
        $sala = $this->salaModel->getById($id);
        if (!$sala) {
            $_SESSION['error_message'] = 'Sala no encontrada.';
            header('Location: ' . $this->url('admin_training_dashboard'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->validateSalaInput($_POST);
            if (!$data['ok']) {
                $_SESSION['error_message'] = $data['message'];
                header('Location: ' . $this->url('admin_training_edit_sala', ['id' => $id]));
                exit;
            }
            $ok = $this->salaModel->update($id, array_merge($data['data'], [
                'estado_sala' => $_POST['estado_sala'] ?? $sala['estado_sala']
            ]));
            $_SESSION[$ok ? 'success_message' : 'error_message'] = $ok
                ? 'Sala actualizada.'
                : 'No se pudo actualizar la sala.';
            header('Location: ' . $this->url('admin_training_view_sala', ['id' => $id]));
            exit;
        }

        require BASE_PATH . '/views/admin/training/sala_form.php';
    }

    public function viewSala($id = null) {
        $id = $id ?: ($_GET['id'] ?? null);
        $sala = $this->salaModel->getById($id);
        if (!$sala) {
            $_SESSION['error_message'] = 'Sala no encontrada.';
            header('Location: ' . $this->url('admin_training_dashboard'));
            exit;
        }
        $rondas = $this->rondaModel->getBySalaId($id);
        foreach ($rondas as &$ronda) {
            if (in_array($ronda['estado'], ['programada', 'en_curso'], true)) {
                $synced = $this->rondaService->syncEstado($ronda);
                $ronda = array_merge($ronda, $synced);
            }
        }
        unset($ronda);
        $rondaAbierta = $this->rondaModel->getOpenBySalaId($id);
        require BASE_PATH . '/views/admin/training/sala_detail.php';
    }

    public function createRonda($salaId = null) {
        $salaId = $salaId ?: ($_POST['sala_id'] ?? $_GET['sala_id'] ?? null);
        $sala = $this->salaModel->getById($salaId);
        if (!$sala) {
            $_SESSION['error_message'] = 'Sala no encontrada.';
            header('Location: ' . $this->url('admin_training_dashboard'));
            exit;
        }
        if ($sala['estado_sala'] !== 'activa') {
            $_SESSION['error_message'] = 'Solo se pueden abrir rondas en salas activas.';
            header('Location: ' . $this->url('admin_training_view_sala', ['id' => $salaId]));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $this->url('admin_training_view_sala', ['id' => $salaId]));
            exit;
        }

        $open = $this->rondaModel->getOpenBySalaId($salaId);
        if ($open) {
            $open = $this->rondaService->syncEstado($open);
            if (in_array($open['estado'], ['programada', 'en_curso'], true)) {
                $_SESSION['error_message'] = 'Ya existe una ronda abierta en esta sala.';
                header('Location: ' . $this->url('admin_training_view_sala', ['id' => $salaId]));
                exit;
            }
        }

        $duracion = (int)($_POST['duracion_minutos'] ?? $sala['duracion_minutos']);
        if (!in_array($duracion, [5, 10, 15], true)) {
            $duracion = (int)$sala['duracion_minutos'];
        }
        $zona = trim($_POST['zona_horaria'] ?? $sala['zona_horaria'] ?: 'America/Guayaquil');
        $inicioLocal = trim($_POST['hora_inicio_local'] ?? '');
        if ($inicioLocal === '') {
            $_SESSION['error_message'] = 'Debe indicar la hora de inicio.';
            header('Location: ' . $this->url('admin_training_view_sala', ['id' => $salaId]));
            exit;
        }

        try {
            $localTz = new DateTimeZone($zona);
            $startLocal = DateTime::createFromFormat('Y-m-d\TH:i', $inicioLocal, $localTz)
                ?: DateTime::createFromFormat('Y-m-d H:i:s', $inicioLocal, $localTz)
                ?: DateTime::createFromFormat('Y-m-d H:i', $inicioLocal, $localTz);
            if (!$startLocal) {
                throw new Exception('Formato de fecha inválido');
            }
            $startUtc = clone $startLocal;
            $startUtc->setTimezone(new DateTimeZone('UTC'));
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Fecha/hora de inicio inválida.';
            header('Location: ' . $this->url('admin_training_view_sala', ['id' => $salaId]));
            exit;
        }

        $rondaId = $this->rondaModel->create([
            'sala_id' => $salaId,
            'numero' => $this->rondaModel->getNextNumero($salaId),
            'hora_inicio' => $startUtc->format('Y-m-d H:i:s'),
            'duracion_minutos' => $duracion,
            'zona_horaria' => $zona,
            'estado' => 'programada',
            'created_by' => $_SESSION['user_id']
        ]);

        if ($rondaId) {
            $ronda = $this->rondaModel->getById($rondaId);
            $this->rondaService->syncEstado($ronda);
            $_SESSION['success_message'] = 'Ronda creada.';
            header('Location: ' . $this->url('admin_training_ronda_detail', ['id' => $rondaId]));
        } else {
            $_SESSION['error_message'] = 'No se pudo crear la ronda.';
            header('Location: ' . $this->url('admin_training_view_sala', ['id' => $salaId]));
        }
        exit;
    }

    public function cancelRonda($id = null) {
        $id = $id ?: ($_POST['id'] ?? $_GET['id'] ?? null);
        $ronda = $this->rondaModel->getById($id);
        if (!$ronda) {
            $_SESSION['error_message'] = 'Ronda no encontrada.';
            header('Location: ' . $this->url('admin_training_dashboard'));
            exit;
        }
        $result = $this->rondaService->cancelRonda($ronda);
        $_SESSION[$result['success'] ? 'success_message' : 'error_message'] = $result['message'];
        header('Location: ' . $this->url('admin_training_view_sala', ['id' => $ronda['sala_id']]));
        exit;
    }

    public function closeRonda($id = null) {
        $id = $id ?: ($_POST['id'] ?? $_GET['id'] ?? null);
        $ronda = $this->rondaModel->getById($id);
        if (!$ronda) {
            $_SESSION['error_message'] = 'Ronda no encontrada.';
            header('Location: ' . $this->url('admin_training_dashboard'));
            exit;
        }
        $ronda = $this->rondaService->syncEstado($ronda);
        if ($ronda['estado'] === 'finalizada') {
            $_SESSION['success_message'] = 'La ronda ya estaba finalizada.';
        } elseif ($ronda['estado'] === 'cancelada') {
            $_SESSION['error_message'] = 'La ronda está cancelada.';
        } else {
            $this->rondaService->finalizeRonda($ronda, true);
            $_SESSION['success_message'] = 'Ronda cerrada.';
        }
        header('Location: ' . $this->url('admin_training_ronda_detail', ['id' => $id]));
        exit;
    }

    public function rondaDetail($id = null) {
        $id = $id ?: ($_GET['id'] ?? null);
        $ronda = $this->rondaModel->getById($id);
        if (!$ronda) {
            $_SESSION['error_message'] = 'Ronda no encontrada.';
            header('Location: ' . $this->url('admin_training_dashboard'));
            exit;
        }
        $ronda = $this->rondaService->syncEstado($ronda);
        // refresh after sync
        $ronda = $this->rondaModel->getById($id);
        $schedule = $this->rondaService->getSchedule($ronda);
        $inscritos = $this->inscripcionModel->listByRonda($id);
        $summary = null;
        if ($ronda['estado'] === 'finalizada') {
            $summary = $this->rondaService->buildSummary($ronda);
        }
        $lowestBid = $this->bidModel->getLowestBid($id);
        require BASE_PATH . '/views/admin/training/ronda_detail.php';
    }

    public function toggleInscripcion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(false, 'Método no permitido');
            return;
        }
        $inscripcionId = $_POST['inscripcion_id'] ?? null;
        $activo = isset($_POST['activo']) ? (int)$_POST['activo'] : 0;
        if (!$inscripcionId) {
            $this->sendJsonResponse(false, 'Inscripción requerida');
            return;
        }
        $ok = $this->inscripcionModel->setActivo($inscripcionId, $activo === 1);
        $this->sendJsonResponse($ok, $ok ? 'Inscripción actualizada.' : 'No se pudo actualizar.');
    }

    private function validateSalaInput($input) {
        $titulo = trim($input['titulo'] ?? '');
        $presupuesto = ReverseAuctionEngine::parseMoneyValue($input['presupuesto_referencial'] ?? '');
        if ($presupuesto === null) {
            $presupuestoRaw = str_replace(',', '.', trim($input['presupuesto_referencial'] ?? ''));
            $presupuesto = is_numeric($presupuestoRaw) ? (float)$presupuestoRaw : null;
        }
        $variacion = str_replace(',', '.', trim($input['variacion_minima'] ?? ''));
        $duracion = (int)($input['duracion_minutos'] ?? 10);
        $zona = trim($input['zona_horaria'] ?? 'America/Guayaquil') ?: 'America/Guayaquil';

        if ($titulo === '') {
            return ['ok' => false, 'message' => 'El título es obligatorio.'];
        }
        if ($presupuesto === null || $presupuesto <= 0) {
            return ['ok' => false, 'message' => 'Presupuesto referencial inválido.'];
        }
        if ($variacion === '' || !is_numeric($variacion) || (float)$variacion < 0) {
            return ['ok' => false, 'message' => 'Variación mínima inválida.'];
        }
        if (!in_array($duracion, [5, 10, 15], true)) {
            return ['ok' => false, 'message' => 'Duración debe ser 5, 10 o 15 minutos.'];
        }

        return [
            'ok' => true,
            'data' => [
                'titulo' => $titulo,
                'descripcion' => trim($input['descripcion'] ?? '') ?: null,
                'presupuesto_referencial' => $presupuesto,
                'variacion_minima' => (float)$variacion,
                'duracion_minutos' => $duracion,
                'zona_horaria' => $zona
            ]
        ];
    }

    private function url($action, $params = []) {
        $url = BASE_URL . 'index.php?action=' . $action;
        if (!empty($params)) {
            $url .= '&' . http_build_query($params);
        }
        return $url;
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
