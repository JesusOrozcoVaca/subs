<?php
/**
 * Motor compartido de puja inversa electrónica.
 * Usado por el proceso completo y por el módulo de prácticas.
 */
class ReverseAuctionEngine {
    /**
     * Parsea valor monetario a float.
     * Acepta: "1.234,56" (ES), "1234,56", "1234.56" (decimal punto) y enteros.
     */
    public static function parseMoneyValue($valorRaw) {
        if (is_int($valorRaw) || is_float($valorRaw)) {
            return (float)$valorRaw;
        }

        $valorRaw = trim((string)$valorRaw);
        if ($valorRaw === '') {
            return null;
        }

        $hasComma = strpos($valorRaw, ',') !== false;
        $hasDot = strpos($valorRaw, '.') !== false;

        if ($hasComma && $hasDot) {
            // Formato ES: puntos de miles + coma decimal → 1.234,56
            $valorSanitized = str_replace('.', '', $valorRaw);
            $valorSanitized = str_replace(',', '.', $valorSanitized);
        } elseif ($hasComma) {
            // Solo coma decimal → 1234,56
            $valorSanitized = str_replace(',', '.', $valorRaw);
        } elseif ($hasDot) {
            // Solo puntos: si hay más de uno son miles; si hay uno es decimal PHP/US.
            if (substr_count($valorRaw, '.') > 1) {
                $valorSanitized = str_replace('.', '', $valorRaw);
            } else {
                $valorSanitized = $valorRaw;
            }
        } else {
            $valorSanitized = $valorRaw;
        }

        if ($valorSanitized === '' || !is_numeric($valorSanitized)) {
            return null;
        }
        return (float)$valorSanitized;
    }

    /**
     * Construye schedule local/UTC a partir de hora_inicio UTC.
     */
    public static function buildSchedule($horaInicioUtc, $duracionMinutos, $zonaHoraria) {
        $zonaHoraria = trim((string)$zonaHoraria);
        $tzName = $zonaHoraria !== '' ? $zonaHoraria : 'UTC';

        try {
            $localTz = new DateTimeZone($tzName);
        } catch (Exception $e) {
            $localTz = new DateTimeZone('UTC');
            $tzName = 'UTC';
        }

        try {
            $startUtc = new DateTime($horaInicioUtc, new DateTimeZone('UTC'));
        } catch (Exception $e) {
            return null;
        }

        $startLocal = clone $startUtc;
        $startLocal->setTimezone($localTz);

        $endLocal = clone $startLocal;
        $endLocal->modify('+' . (int)$duracionMinutos . ' minutes');

        return [
            'timezone' => $tzName,
            'start' => $startLocal->format('d/m/Y H:i:s'),
            'end' => $endLocal->format('d/m/Y H:i:s'),
            'duration' => (int)$duracionMinutos,
            'start_ts_ms' => $startLocal->getTimestamp() * 1000,
            'end_ts_ms' => $endLocal->getTimestamp() * 1000,
            'start_ts' => $startLocal->getTimestamp(),
            'end_ts' => $endLocal->getTimestamp()
        ];
    }

    /**
     * Evalúa ventana horaria. Si no hay schedule, retorna open=true.
     * @return array{open:bool,message:string,phase:string}
     */
    public static function evaluateWindow($schedule) {
        if (!$schedule || empty($schedule['start_ts']) || empty($schedule['end_ts'])) {
            return ['open' => true, 'message' => '', 'phase' => 'open'];
        }
        $nowTs = time();
        if ($nowTs < (int)$schedule['start_ts']) {
            return ['open' => false, 'message' => 'La puja aún no ha iniciado.', 'phase' => 'before'];
        }
        if ($nowTs > (int)$schedule['end_ts']) {
            return ['open' => false, 'message' => 'La puja ha finalizado.', 'phase' => 'after'];
        }
        return ['open' => true, 'message' => '', 'phase' => 'open'];
    }

    /**
     * Valida y registra una puja.
     *
     * Context keys:
     * - presupuesto_referencial (float)
     * - variacion_minima (float %)
     * - oferta_inicial (float)
     * - user_last_bid (array|null) with valor
     * - lowest_bid (float|null)
     * - create_callback (callable) fn(float $valor, int $fechaMs): bool
     */
    public static function submitBid($valorRaw, array $context) {
        $valor = self::parseMoneyValue($valorRaw);
        if ($valor === null) {
            return ['success' => false, 'message' => 'El valor de la puja es inválido.'];
        }
        if ($valor <= 0) {
            return ['success' => false, 'message' => 'El valor de la puja debe ser mayor a 0.'];
        }

        $presupuesto = isset($context['presupuesto_referencial']) ? (float)$context['presupuesto_referencial'] : 0.0;
        $variationPercent = isset($context['variacion_minima']) ? (float)$context['variacion_minima'] : 0.0;
        $initialOfferValue = isset($context['oferta_inicial']) ? (float)$context['oferta_inicial'] : 0.0;
        $variationAmount = round($initialOfferValue * ($variationPercent / 100), 2);

        if ($presupuesto > 0 && $valor > $presupuesto) {
            return ['success' => false, 'message' => 'El valor ingresado es mayor al presupuesto referencial.'];
        }

        $userLastBid = $context['user_last_bid'] ?? null;
        $baseBidValue = $userLastBid ? (float)$userLastBid['valor'] : $initialOfferValue;
        if ($baseBidValue > 0 && $variationAmount > 0) {
            $maxAllowedForUser = $baseBidValue - $variationAmount;
            if ($valor > $maxAllowedForUser) {
                return ['success' => false, 'message' => 'El valor ingresado no cumple la variación mínima permitida.'];
            }
        }

        $lowestBid = $context['lowest_bid'] ?? null;
        if ($lowestBid !== null && $valor >= (float)$lowestBid) {
            return ['success' => false, 'message' => 'Ya existe una oferta con mejor valor ingresado.'];
        }

        $fechaMs = (int)round(microtime(true) * 1000);
        $createCb = $context['create_callback'] ?? null;
        if (!is_callable($createCb)) {
            return ['success' => false, 'message' => 'No se pudo registrar la puja.'];
        }

        $ok = (bool)call_user_func($createCb, $valor, $fechaMs);
        if (!$ok) {
            return ['success' => false, 'message' => 'No se pudo registrar la puja.'];
        }

        return [
            'success' => true,
            'message' => 'Puja registrada exitosamente.',
            'valor' => $valor,
            'fecha_puja_ms' => $fechaMs
        ];
    }

    /**
     * Status para polling de ventana.
     */
    public static function buildStatusData($userLastBid, $lowestBid) {
        $isUserBest = false;
        if ($userLastBid && $lowestBid !== null) {
            $isUserBest = abs((float)$userLastBid['valor'] - (float)$lowestBid) < 0.00001;
        } elseif ($userLastBid && $lowestBid === null) {
            $isUserBest = true;
        }

        return [
            'user_last_bid' => $userLastBid ? (float)$userLastBid['valor'] : null,
            'is_user_best' => $isUserBest,
            'lowest_bid' => $lowestBid !== null ? (float)$lowestBid : null
        ];
    }

    /**
     * Determina ganador (menor mejor valor; empate por timestamp más temprano).
     * $participants: [['id'=>, 'nombre_completo'=>, 'oferta_inicial'=>?], ...]
     * $getUserBids: callable(userId) => list of bids with valor, fecha_puja_ms
     */
    public static function determineWinner(array $participants, callable $getUserBids) {
        $winnerName = null;
        $winnerUserId = null;
        $winnerValue = null;
        $winnerTimeMs = null;

        foreach ($participants as $participant) {
            $userId = (int)$participant['id'];
            $userBids = call_user_func($getUserBids, $userId);
            $bestValue = null;
            $bestTimeMs = null;

            if (!empty($userBids)) {
                foreach ($userBids as $bid) {
                    $bidValue = (float)$bid['valor'];
                    $bidTimeMs = isset($bid['fecha_puja_ms']) ? (int)$bid['fecha_puja_ms'] : 0;
                    if ($bestValue === null || $bidValue < $bestValue || ($bidValue === $bestValue && ($bestTimeMs === null || $bidTimeMs < $bestTimeMs))) {
                        $bestValue = $bidValue;
                        $bestTimeMs = $bidTimeMs;
                    }
                }
            }

            if ($bestValue === null && isset($participant['oferta_inicial'])) {
                $bestValue = (float)$participant['oferta_inicial'];
                $bestTimeMs = 0;
            }

            if ($bestValue === null) {
                continue;
            }

            if (
                $winnerValue === null
                || $bestValue < $winnerValue
                || ($bestValue === $winnerValue && ($winnerTimeMs === null || $bestTimeMs < $winnerTimeMs))
            ) {
                $winnerValue = $bestValue;
                $winnerTimeMs = $bestTimeMs;
                $winnerName = $participant['nombre_completo'] ?? null;
                $winnerUserId = $userId;
            }
        }

        return [
            'winner_user_id' => $winnerUserId,
            'winner_name' => $winnerName,
            'winner_value' => $winnerValue
        ];
    }

    /**
     * Formatea timestamp de puja para resumen.
     */
    public static function formatPujaTimestamp($timestampMs, $fallbackDateTime, $timezone) {
        $timezone = $timezone ?: 'UTC';
        try {
            $tz = new DateTimeZone($timezone);
        } catch (Exception $e) {
            $tz = new DateTimeZone('UTC');
        }

        if ($timestampMs) {
            $seconds = (int)floor($timestampMs / 1000);
            $msPart = (int)($timestampMs % 1000);
            $msTwo = str_pad((string)floor($msPart / 10), 2, '0', STR_PAD_LEFT);
            $dt = new DateTime('@' . $seconds);
            $dt->setTimezone($tz);
            return 'hora:' . $dt->format('H:i:s') . '.' . $msTwo;
        }

        if (!empty($fallbackDateTime)) {
            try {
                $dt = new DateTime($fallbackDateTime);
                $dt->setTimezone($tz);
                return 'hora:' . $dt->format('H:i:s') . '.00';
            } catch (Exception $e) {
                return '';
            }
        }

        return '';
    }
}
