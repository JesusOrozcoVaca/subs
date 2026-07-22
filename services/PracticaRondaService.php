<?php
require_once BASE_PATH . '/models/PracticaRonda.php';
require_once BASE_PATH . '/models/PracticaInscripcion.php';
require_once BASE_PATH . '/models/PracticaBid.php';
require_once BASE_PATH . '/services/ReverseAuctionEngine.php';

/**
 * Transiciones de estado y resumen para rondas de práctica.
 */
class PracticaRondaService {
    private $rondaModel;
    private $inscripcionModel;
    private $bidModel;

    public function __construct() {
        $this->rondaModel = new PracticaRonda();
        $this->inscripcionModel = new PracticaInscripcion();
        $this->bidModel = new PracticaBid();
    }

    public function getSchedule(array $ronda) {
        return ReverseAuctionEngine::buildSchedule(
            $ronda['hora_inicio'],
            $ronda['duracion_minutos'],
            $ronda['zona_horaria'] ?? 'America/Guayaquil'
        );
    }

    /**
     * Sincroniza programada/en_curso/finalizada según reloj. Idempotente.
     */
    public function syncEstado(array $ronda) {
        if (in_array($ronda['estado'], ['finalizada', 'cancelada'], true)) {
            return $ronda;
        }

        $schedule = $this->getSchedule($ronda);
        if (!$schedule) {
            return $ronda;
        }

        $now = time();
        $startTs = (int)$schedule['start_ts'];
        $endTs = (int)$schedule['end_ts'];

        if ($now < $startTs) {
            if ($ronda['estado'] !== 'programada') {
                $this->rondaModel->updateEstado($ronda['id'], 'programada');
                $ronda['estado'] = 'programada';
            }
            return $ronda;
        }

        if ($now > $endTs) {
            return $this->finalizeRonda($ronda);
        }

        // Dentro de ventana
        if ($ronda['estado'] !== 'en_curso') {
            $this->rondaModel->updateEstado($ronda['id'], 'en_curso', [
                'started_at' => $ronda['started_at'] ?: date('Y-m-d H:i:s')
            ]);
            $ronda['estado'] = 'en_curso';
            if (empty($ronda['started_at'])) {
                $ronda['started_at'] = date('Y-m-d H:i:s');
            }
        }
        return $ronda;
    }

    public function finalizeRonda(array $ronda, $force = false) {
        if ($ronda['estado'] === 'finalizada') {
            return $ronda;
        }
        if ($ronda['estado'] === 'cancelada' && !$force) {
            return $ronda;
        }

        $inscritos = $this->inscripcionModel->listActiveByRonda($ronda['id']);
        $participants = array_map(function ($row) {
            return [
                'id' => (int)$row['usuario_id'],
                'nombre_completo' => $row['nombre_completo'],
                'oferta_inicial' => (float)$row['oferta_inicial']
            ];
        }, $inscritos);

        $bidModel = $this->bidModel;
        $rondaId = (int)$ronda['id'];
        $winner = ReverseAuctionEngine::determineWinner($participants, function ($userId) use ($bidModel, $rondaId) {
            return $bidModel->getUserBids($rondaId, $userId);
        });

        $this->rondaModel->updateEstado($ronda['id'], 'finalizada', [
            'ended_at' => date('Y-m-d H:i:s'),
            'ganador_usuario_id' => $winner['winner_user_id'],
            'ganador_valor' => $winner['winner_value']
        ]);

        $ronda['estado'] = 'finalizada';
        $ronda['ended_at'] = date('Y-m-d H:i:s');
        $ronda['ganador_usuario_id'] = $winner['winner_user_id'];
        $ronda['ganador_valor'] = $winner['winner_value'];
        $ronda['ganador_nombre'] = $winner['winner_name'];
        return $ronda;
    }

    public function cancelRonda(array $ronda) {
        if (in_array($ronda['estado'], ['finalizada', 'cancelada'], true)) {
            return ['success' => false, 'message' => 'La ronda ya está cerrada.'];
        }
        if ($ronda['estado'] === 'en_curso') {
            return ['success' => false, 'message' => 'No se puede cancelar una ronda en curso. Use cerrar.'];
        }
        $this->rondaModel->updateEstado($ronda['id'], 'cancelada', [
            'ended_at' => date('Y-m-d H:i:s')
        ]);
        return ['success' => true, 'message' => 'Ronda cancelada.'];
    }

    public function buildSummary(array $ronda) {
        $timezone = $ronda['zona_horaria'] ?? 'America/Guayaquil';
        $inscritos = $this->inscripcionModel->listActiveByRonda($ronda['id']);
        $columns = [];
        $maxEntries = 0;

        foreach ($inscritos as $inscrito) {
            $userBids = $this->bidModel->getUserBids($ronda['id'], $inscrito['usuario_id']);
            $rows = [];
            foreach ($userBids as $bid) {
                $rows[] = [
                    'value' => '$ ' . number_format((float)$bid['valor'], 2, ',', '.'),
                    'time' => ReverseAuctionEngine::formatPujaTimestamp(
                        $bid['fecha_puja_ms'] ?? 0,
                        $bid['fecha_puja'] ?? null,
                        $timezone
                    )
                ];
            }
            $rows[] = [
                'value' => '$ ' . number_format((float)$inscrito['oferta_inicial'], 2, ',', '.'),
                'time' => ReverseAuctionEngine::formatPujaTimestamp(0, $inscrito['joined_at'] ?? null, $timezone)
            ];
            $maxEntries = max($maxEntries, count($rows));
            $columns[] = [
                'name' => $inscrito['nombre_completo'],
                'rows' => $rows
            ];
        }

        $winnerName = $ronda['ganador_nombre'] ?? null;
        if (!$winnerName && !empty($ronda['ganador_usuario_id'])) {
            foreach ($inscritos as $inscrito) {
                if ((int)$inscrito['usuario_id'] === (int)$ronda['ganador_usuario_id']) {
                    $winnerName = $inscrito['nombre_completo'];
                    break;
                }
            }
        }

        return [
            'columns' => $columns,
            'max_entries' => $maxEntries,
            'winner_name' => $winnerName,
            'winner_value' => isset($ronda['ganador_valor']) ? (float)$ronda['ganador_valor'] : null
        ];
    }
}
