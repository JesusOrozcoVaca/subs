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
    const PRICE_FLOOR_RATIO = 0.80;
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
            $valor = $this->computeBidValue($ronda, $bot, $lowestBid);
            if ($valor === null) {
                continue;
            }

            $userId = (int)$bot['usuario_id'];
            $bidModel = $this->bidModel;
            $result = ReverseAuctionEngine::submitBid((string)$valor, [
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

        switch ($profile) {
            case 'pasivo':
                $minMs = 25000;
                $maxMs = 45000;
                $baseChance = $nearEnd ? 0.55 : 0.12;
                break;
            case 'agresivo':
                $minMs = 3000;
                $maxMs = 8000;
                $baseChance = 0.55;
                if ($bestInfo && (int)$bestInfo['usuario_id'] !== $userId) {
                    $baseChance = 0.85;
                    $minMs = 2000;
                }
                break;
            case 'equilibrado':
            default:
                $minMs = 8000;
                $maxMs = 20000;
                $baseChance = $nearEnd ? 0.50 : 0.35;
                break;
        }

        $threshold = mt_rand($minMs, $maxMs);
        if ($elapsed < $threshold) {
            return false;
        }

        // Primera puja: más probabilidad una vez pasado el mínimo.
        if ($lastMs === 0) {
            $baseChance = min(0.9, $baseChance + 0.25);
        }

        return (mt_rand(1, 1000) / 1000) <= $baseChance;
    }

    /**
     * Calcula un valor válido bajo el floor pedagógico y las reglas del engine.
     */
    private function computeBidValue(array $ronda, array $bot, $lowestBid) {
        $presupuesto = (float)$ronda['presupuesto_referencial'];
        $floor = round($presupuesto * self::PRICE_FLOOR_RATIO, 2);
        $variationPercent = (float)$ronda['variacion_minima'];
        $ofertaInicial = (float)$bot['oferta_inicial'];
        $variationAmount = round($ofertaInicial * ($variationPercent / 100), 2);

        $userId = (int)$bot['usuario_id'];
        $last = $this->bidModel->getUserLastBid((int)$bot['ronda_id'], $userId);
        $base = $last ? (float)$last['valor'] : $ofertaInicial;

        $maxAllowed = $base;
        if ($base > 0 && $variationAmount > 0) {
            $maxAllowed = $base - $variationAmount;
        }

        // Debe mejorar el mejor global.
        $ceiling = $maxAllowed;
        if ($lowestBid !== null) {
            $ceiling = min($ceiling, (float)$lowestBid - 0.01);
        }

        $ceiling = round($ceiling, 2);
        if ($ceiling < $floor) {
            return null;
        }
        if ($ceiling <= 0) {
            return null;
        }

        // No siempre el mínimo: bajar un poco desde ceiling hacia el floor.
        $span = $ceiling - $floor;
        $dropRatio = mt_rand(5, 35) / 100; // 5%–35% del tramo disponible
        $valor = round($ceiling - ($span * $dropRatio), 2);
        if ($valor < $floor) {
            $valor = $floor;
        }
        if ($valor > $ceiling) {
            $valor = $ceiling;
        }

        // Garantizar al menos un tick de variación si quedó igual al base.
        if ($valor >= $base && $variationAmount > 0) {
            $valor = $ceiling;
        }

        if ($valor <= 0 || $valor < $floor || $valor > $ceiling) {
            return null;
        }
        if ($lowestBid !== null && $valor >= (float)$lowestBid) {
            return null;
        }

        return $valor;
    }
}
