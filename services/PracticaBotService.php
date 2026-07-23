<?php
require_once BASE_PATH . '/models/User.php';
require_once BASE_PATH . '/models/PracticaSala.php';
require_once BASE_PATH . '/models/PracticaRonda.php';
require_once BASE_PATH . '/models/PracticaInscripcion.php';
require_once BASE_PATH . '/models/PracticaBid.php';
require_once BASE_PATH . '/services/PracticaRondaService.php';
require_once BASE_PATH . '/services/ReverseAuctionEngine.php';

/**
 * Simulación de rivales (bots) para Prácticas de Puja.
 * Reutiliza ReverseAuctionEngine; no usa sesiones HTTP.
 */
class PracticaBotService {
    /** Piso absoluto (~70% del presupuesto). Por debajo no pujan. */
    const PRICE_FLOOR_RATIO = 0.70;
    const POOL_SIZE = 5;
    const TICK_INTERVAL_MS = 1000;

    private $userModel;
    private $salaModel;
    private $rondaModel;
    private $inscripcionModel;
    private $bidModel;
    private $rondaService;

    public function __construct() {
        $this->userModel = new User();
        $this->salaModel = new PracticaSala();
        $this->rondaModel = new PracticaRonda();
        $this->inscripcionModel = new PracticaInscripcion();
        $this->bidModel = new PracticaBid();
        $this->rondaService = new PracticaRondaService();
    }

    public function ensureBotPool($count = self::POOL_SIZE) {
        $count = max(1, min(10, (int)$count));
        $bots = [];
        for ($i = 1; $i <= $count; $i++) {
            $bot = $this->userModel->ensureBotUser($i);
            if ($bot) {
                $bots[] = $bot;
            }
        }
        return $bots;
    }

    /**
     * Inscribe N bots en la ronda según config de la sala.
     */
    public function enrollBotsForRonda(array $ronda) {
        $enabled = !empty($ronda['bots_enabled']);
        if (!$enabled && isset($ronda['sala_id'])) {
            $sala = $this->salaModel->getById($ronda['sala_id']);
            if ($sala) {
                $ronda = array_merge($ronda, [
                    'bots_enabled' => $sala['bots_enabled'] ?? 0,
                    'bots_count' => $sala['bots_count'] ?? 2,
                    'bots_profile' => $sala['bots_profile'] ?? 'equilibrado',
                    'presupuesto_referencial' => $sala['presupuesto_referencial'] ?? ($ronda['presupuesto_referencial'] ?? 0)
                ]);
            }
        }

        if (empty($ronda['bots_enabled'])) {
            return ['enrolled' => 0, 'bots' => []];
        }

        $count = (int)($ronda['bots_count'] ?? 2);
        $count = max(1, min(5, $count));
        $presupuesto = (float)($ronda['presupuesto_referencial'] ?? 0);
        if ($presupuesto <= 0) {
            return ['enrolled' => 0, 'bots' => []];
        }

        $pool = $this->ensureBotPool(max($count, self::POOL_SIZE));
        $enrolled = [];
        foreach (array_slice($pool, 0, $count) as $bot) {
            $ratio = 0.95 + (mt_rand(0, 500) / 10000); // 95.00% – 100.00%
            $oferta = round($presupuesto * min(1.0, $ratio), 2);
            if ($oferta <= 0) {
                $oferta = $presupuesto;
            }
            $result = $this->inscripcionModel->join((int)$ronda['id'], (int)$bot['id'], $oferta);
            if (!empty($result['success'])) {
                $enrolled[] = $bot;
            }
        }

        return ['enrolled' => count($enrolled), 'bots' => $enrolled];
    }

    /**
     * Avanza la simulación si corresponde (llamado desde polling).
     */
    public function maybeTick($rondaId) {
        $rondaId = (int)$rondaId;
        if ($rondaId <= 0) {
            return ['ticked' => false, 'bids' => 0];
        }

        $ronda = $this->rondaModel->getById($rondaId);
        if (!$ronda || empty($ronda['bots_enabled'])) {
            return ['ticked' => false, 'bids' => 0];
        }

        $ronda = $this->rondaService->syncEstado($ronda);
        $ronda = $this->rondaModel->getById($rondaId);
        if (!$ronda || $ronda['estado'] !== 'en_curso') {
            return ['ticked' => false, 'bids' => 0];
        }

        $schedule = $this->rondaService->getSchedule($ronda);
        $window = ReverseAuctionEngine::evaluateWindow($schedule);
        if (!$window['open']) {
            return ['ticked' => false, 'bids' => 0];
        }

        // Asegurar inscripción por si la ronda se abrió antes del enroll.
        $this->enrollBotsForRonda($ronda);

        $nowMs = (int)round(microtime(true) * 1000);
        if (!$this->rondaModel->tryAcquireBotTick($rondaId, $nowMs, self::TICK_INTERVAL_MS)) {
            return ['ticked' => false, 'bids' => 0, 'throttled' => true];
        }

        $profile = $ronda['bots_profile'] ?? 'equilibrado';
        $inscritos = $this->inscripcionModel->listActiveByRonda($rondaId);
        $bots = array_values(array_filter($inscritos, function ($row) {
            return !empty($row['es_bot']);
        }));
        if (empty($bots)) {
            return ['ticked' => true, 'bids' => 0];
        }

        // Shuffle para no favorecer siempre al mismo bot.
        shuffle($bots);

        $endTsMs = $schedule ? (int)$schedule['end_ts_ms'] : null;
        $startTsMs = $schedule ? (int)$schedule['start_ts_ms'] : null;
        $remainingRatio = 1.0;
        if ($endTsMs && $startTsMs && $endTsMs > $startTsMs) {
            $remainingRatio = max(0.0, min(1.0, ($endTsMs - $nowMs) / ($endTsMs - $startTsMs)));
        }
        $nearEnd = $remainingRatio <= 0.20;

        $bidsPlaced = 0;
        $lowestBid = $this->bidModel->getLowestBid($rondaId);
        $bestInfo = $this->bidModel->getBestBidInfo($rondaId);

        foreach ($bots as $bot) {
            if (!$this->shouldBidNow($bot, $profile, $nearEnd, $bestInfo, $nowMs)) {
                continue;
            }
            $valor = $this->computeBidValue($ronda, $bot, $lowestBid, $profile);
            if ($valor === null) {
                continue;
            }

            $userId = (int)$bot['usuario_id'];
            $bidModel = $this->bidModel;
            // Formato ES para el parser (evitar que "75430.48" se lea como 7543048).
            $valorRaw = number_format((float)$valor, 2, ',', '');
            $result = ReverseAuctionEngine::submitBid($valorRaw, [
                'presupuesto_referencial' => (float)$ronda['presupuesto_referencial'],
                'variacion_minima' => (float)$ronda['variacion_minima'],
                'oferta_inicial' => (float)$bot['oferta_inicial'],
                'user_last_bid' => $bidModel->getUserLastBid($rondaId, $userId),
                'lowest_bid' => $lowestBid,
                'create_callback' => function ($v, $fechaMs) use ($bidModel, $rondaId, $userId) {
                    return $bidModel->create([
                        'ronda_id' => $rondaId,
                        'usuario_id' => $userId,
                        'valor' => $v,
                        'fecha_puja_ms' => $fechaMs
                    ]);
                }
            ]);

            if (!empty($result['success'])) {
                $bidsPlaced++;
                $lowestBid = (float)$result['valor'];
                $bestInfo = [
                    'valor' => $lowestBid,
                    'usuario_id' => $userId,
                    'nombre_completo' => $bot['nombre_completo'] ?? ''
                ];
                // Un bot por tick para ritmo natural.
                break;
            }
        }

        return ['ticked' => true, 'bids' => $bidsPlaced];
    }

    private function shouldBidNow(array $bot, $profile, $nearEnd, $bestInfo, $nowMs) {
        $userId = (int)$bot['usuario_id'];
        $last = $this->bidModel->getUserLastBid((int)$bot['ronda_id'], $userId);
        $lastMs = $last && isset($last['fecha_puja_ms']) ? (int)$last['fecha_puja_ms'] : 0;
        $elapsed = $lastMs > 0 ? ($nowMs - $lastMs) : PHP_INT_MAX;

        $minMs = 12000;
        $maxMs = 20000;
        $baseChance = 0.35;

        $losing = $bestInfo && (int)$bestInfo['usuario_id'] !== $userId;

        switch ($profile) {
            case 'pasivo':
                $minMs = 25000;
                $maxMs = 45000;
                $baseChance = $nearEnd ? 0.55 : 0.12;
                // Si van perdiendo, reaccionan (antes se quedaban quietos al tocar el piso).
                if ($losing) {
                    $minMs = $nearEnd ? 6000 : 14000;
                    $maxMs = $nearEnd ? 14000 : 28000;
                    $baseChance = $nearEnd ? 0.75 : 0.40;
                }
                break;
            case 'agresivo':
                $minMs = 3000;
                $maxMs = 8000;
                $baseChance = 0.55;
                if ($losing) {
                    $baseChance = 0.85;
                    $minMs = 2000;
                }
                break;
            case 'equilibrado':
            default:
                $minMs = 8000;
                $maxMs = 20000;
                $baseChance = $nearEnd ? 0.50 : 0.35;
                if ($losing) {
                    $minMs = $nearEnd ? 4000 : 7000;
                    $maxMs = $nearEnd ? 10000 : 16000;
                    $baseChance = $nearEnd ? 0.70 : 0.50;
                }
                break;
        }

        // Primera puja: pasivo entra tarde; agresivo/equilibrado arrancan antes.
        if ($lastMs === 0) {
            $firstChance = ($profile === 'pasivo')
                ? ($nearEnd ? 0.45 : 0.18)
                : min(0.85, $baseChance + 0.30);
            return (mt_rand(1, 1000) / 1000) <= $firstChance;
        }

        $threshold = mt_rand($minMs, $maxMs);
        if ($elapsed < $threshold) {
            return false;
        }

        return (mt_rand(1, 1000) / 1000) <= $baseChance;
    }

    /**
     * Calcula un valor válido con pasos según perfil (no saltos al piso).
     * El perfil controla frecuencia Y tamaño de bajada.
     */
    private function computeBidValue(array $ronda, array $bot, $lowestBid, $profile = 'equilibrado') {
        $presupuesto = (float)$ronda['presupuesto_referencial'];
        $floor = round($presupuesto * self::PRICE_FLOOR_RATIO, 2);
        $variationPercent = (float)$ronda['variacion_minima'];
        $ofertaInicial = (float)$bot['oferta_inicial'];
        $variationAmount = round($ofertaInicial * ($variationPercent / 100), 2);
        if ($variationAmount <= 0) {
            $variationAmount = round($presupuesto * 0.01, 2);
        }

        $userId = (int)$bot['usuario_id'];
        $last = $this->bidModel->getUserLastBid((int)$bot['ronda_id'], $userId);
        $base = $last ? (float)$last['valor'] : $ofertaInicial;
        $isFirstBid = !$last;
        $losing = $lowestBid !== null
            && (!$last || (float)$last['valor'] > (float)$lowestBid + 0.00001);

        $maxAllowed = round($base - $variationAmount, 2);

        // Debe mejorar el mejor global.
        $ceiling = $maxAllowed;
        if ($lowestBid !== null) {
            $ceiling = min($ceiling, round((float)$lowestBid - 0.01, 2));
        }
        $ceiling = round($ceiling, 2);

        // Sin margen legal o por debajo del piso absoluto → no puede pujar.
        if ($ceiling <= 0 || $ceiling < $floor || $maxAllowed < $floor) {
            return null;
        }

        // Pasos en múltiplos de la variación mínima.
        switch ($profile) {
            case 'pasivo':
                $minSteps = 1.0;
                $maxSteps = $isFirstBid ? 1.2 : ($losing ? 1.8 : 1.5);
                break;
            case 'agresivo':
                $minSteps = $isFirstBid ? 1.2 : 1.5;
                $maxSteps = $isFirstBid ? 2.0 : ($losing ? 4.0 : 3.5);
                break;
            case 'equilibrado':
            default:
                $minSteps = 1.0;
                $maxSteps = $isFirstBid ? 1.5 : ($losing ? 2.8 : 2.5);
                break;
        }

        $steps = $minSteps + (mt_rand(0, 1000) / 1000) * ($maxSteps - $minSteps);
        $drop = round($variationAmount * $steps, 2);
        $valor = round($base - $drop, 2);

        // Si van perdiendo, priorizar quedar apenas por debajo del mejor (sin desplome).
        if ($losing) {
            $undercut = max(0.01, round($variationAmount * ($profile === 'pasivo' ? 0.15 : 0.35), 2));
            $valor = round((float)$lowestBid - $undercut, 2);
            // Aun así debe respetar variación mínima desde su base.
            if ($valor > $maxAllowed) {
                $valor = $maxAllowed;
            }
        }

        if ($valor > $ceiling) {
            $valor = $ceiling;
        }
        if ($valor < $floor) {
            // Si el mejor rival ya está bajo el piso, el bot se detiene (límite pedagógico).
            return null;
        }

        if ($valor <= 0 || $valor > $ceiling || $valor > $maxAllowed) {
            return null;
        }
        if ($lowestBid !== null && $valor >= (float)$lowestBid) {
            return null;
        }

        return $valor;
    }
}
