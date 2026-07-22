<?php
require_once BASE_PATH . '/utils/url_helpers.php';

$blockForNoOffer = isset($blockWithoutOffer) ? (bool)$blockWithoutOffer : false;
$blockPuja = isset($blockPuja) ? (bool)$blockPuja : false;
$pujaBlockMessage = $pujaBlockMessage ?? '';

$pujaSchedule = $pujaSchedule ?? null;
$pujaUrl = isset($product['id']) ? url('participant/puja/' . $product['id']) : '#';
$nowMs = (int)round(microtime(true) * 1000);
$startMs = isset($pujaSchedule['start_ts_ms']) ? (int)$pujaSchedule['start_ts_ms'] : null;
$endMs = isset($pujaSchedule['end_ts_ms']) ? (int)$pujaSchedule['end_ts_ms'] : null;
$isActive = $startMs !== null && $endMs !== null ? ($nowMs >= $startMs && $nowMs <= $endMs) : true;
$isEnded = $endMs !== null ? ($nowMs > $endMs) : false;
$isBefore = $startMs !== null ? ($nowMs < $startMs) : false;
$canParticipate = !$blockPuja && !$blockForNoOffer;

// Si no puede participar y todavía no ha terminado, solo mostrar el mensaje de bloqueo.
if (!$canParticipate && !$isEnded) {
    $message = $blockPuja
        ? $pujaBlockMessage
        : 'Usted no cargo oferta para este proceso por lo tanto, no puede participar en el mismo.';
    echo '<div class="read-only-message"><p>' . htmlspecialchars($message) . '</p></div>';
    return;
}
?>
<h2>Puja</h2>
<div id="puja-container">
    <div class="puja-details">
        <?php if ($pujaSchedule): ?>
            <p><strong>Hora de inicio:</strong> <?php echo htmlspecialchars($pujaSchedule['start']); ?> (<?php echo htmlspecialchars($pujaSchedule['timezone']); ?>)</p>
            <p><strong>Hora de finalizacion:</strong> <?php echo htmlspecialchars($pujaSchedule['end']); ?> (<?php echo htmlspecialchars($pujaSchedule['timezone']); ?>)</p>
        <?php else: ?>
            <p><strong>Hora de inicio:</strong> Pendiente</p>
            <p><strong>Hora de finalizacion:</strong> Pendiente</p>
        <?php endif; ?>
    </div>

    <?php if ($isActive && $canParticipate): ?>
        <p>La puja para el proceso ha sido habilitada, para participar en la puja debe de dar clic en el boton.</p>
    <?php endif; ?>
    <div id="puja-status-message" class="read-only-message" style="<?php echo $isActive ? 'display:none;' : ''; ?>">
        <p>
            <?php if ($isEnded): ?>
                La puja ha finalizado, puedes ver los resultados en la tabla inferior.
            <?php elseif ($isBefore): ?>
                La puja aún no ha iniciado.
            <?php endif; ?>
        </p>
    </div>
    <a href="<?php echo $pujaUrl; ?>" id="puja-action-btn" class="btn btn-primary" target="_blank" rel="noopener" style="<?php echo ($isActive && $canParticipate) ? '' : 'display:none;'; ?>">Puja</a>
</div>

<script>
    (function() {
        const startMs = <?php echo $startMs ? json_encode($startMs) : 'null'; ?>;
        const endMs = <?php echo $endMs ? json_encode($endMs) : 'null'; ?>;
        const btn = document.getElementById('puja-action-btn');
        const message = document.getElementById('puja-status-message');

        if (!startMs || !endMs || !btn || !message) {
            return;
        }

        const updateState = () => {
            const now = Date.now();
            const isActive = now >= startMs && now <= endMs;
            const isEnded = now > endMs;
            const isBefore = now < startMs;

            if (isActive) {
                btn.style.display = <?php echo $canParticipate ? "''" : "'none'"; ?>;
                message.style.display = 'none';
            } else {
                btn.style.display = 'none';
                message.style.display = '';
                message.querySelector('p').textContent = isEnded
                    ? 'La puja ha finalizado, puedes ver los resultados en la tabla inferior'
                    : 'La puja aún no ha iniciado.';
            }
        };

        updateState();
        setInterval(updateState, 1000);
    })();
</script>

<?php if ($isEnded && !empty($pujaSummary) && !empty($pujaSummary['columns'])): ?>
    <h3 style="margin-top: 18px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
        <span>Resumen de Puja</span>
        <?php if (!empty($pujaSummary['winner_name'])): ?>
            <span class="puja-winner-badge" title="Menor valor ofertado en la subasta inversa<?php echo isset($pujaSummary['winner_value']) ? ': $ ' . number_format((float)$pujaSummary['winner_value'], 2, ',', '.') : ''; ?>">
                Ganador: <?php echo htmlspecialchars($pujaSummary['winner_name']); ?>
            </span>
        <?php endif; ?>
    </h3>
    <div style="overflow-x:auto; max-width: 100%;">
        <table style="border-collapse: collapse; width: max-content; min-width: 520px;">
            <thead>
                <tr>
                    <?php foreach ($pujaSummary['columns'] as $col): ?>
                        <th style="border:1px solid #2a2a2a; background:#2f94c7; color:#fff; padding:8px 10px; font-size:12px; text-transform: uppercase;">
                            <?php echo htmlspecialchars($col['name']); ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                    $max = (int)($pujaSummary['max_entries'] ?? 0);
                    $max = max($max, 1);
                    for ($i = 0; $i < $max; $i++):
                ?>
                    <tr>
                        <?php foreach ($pujaSummary['columns'] as $col): ?>
                            <?php $row = $col['rows'][$i] ?? null; ?>
                            <td style="border:1px solid #2a2a2a; background:#d3e1f1; padding:6px 10px; text-align:center; font-weight:700; font-size:12px;">
                                <?php echo $row ? htmlspecialchars($row['value']) : ''; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <?php foreach ($pujaSummary['columns'] as $col): ?>
                            <?php $row = $col['rows'][$i] ?? null; ?>
                            <td style="border:1px solid #2a2a2a; background:#b8c9dd; padding:4px 10px; text-align:center; font-size:12px;">
                                <?php echo $row ? htmlspecialchars($row['time']) : ''; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>