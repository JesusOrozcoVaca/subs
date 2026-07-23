<?php if (!empty($_SESSION['error_message'])): ?>
    <p class="error-message"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></p>
<?php endif; ?>

<section class="form-card training-history-detail">
    <header class="training-join-header">
        <p class="training-join-code"><?php echo htmlspecialchars($ronda['sala_codigo']); ?></p>
        <h2 class="training-join-title">
            <?php echo htmlspecialchars($ronda['sala_titulo']); ?>
            <span class="training-join-round">Ronda #<?php echo (int)$ronda['numero']; ?></span>
        </h2>
        <?php if ($schedule): ?>
            <p class="training-join-desc">
                <?php echo htmlspecialchars($schedule['start']); ?> — <?php echo htmlspecialchars($schedule['end']); ?>
                (<?php echo htmlspecialchars($schedule['timezone']); ?>)
            </p>
        <?php endif; ?>
    </header>

    <div class="training-join-meta">
        <div class="training-join-meta-item">
            <span class="training-join-meta-label">Estado</span>
            <strong class="training-join-meta-value"><?php echo htmlspecialchars($ronda['estado']); ?></strong>
        </div>
        <div class="training-join-meta-item">
            <span class="training-join-meta-label">Su oferta inicial</span>
            <strong class="training-join-meta-value">
                $ <?php echo number_format((float)$inscription['oferta_inicial'], 2, ',', '.'); ?>
            </strong>
        </div>
        <div class="training-join-meta-item">
            <span class="training-join-meta-label">Su mejor puja</span>
            <strong class="training-join-meta-value">
                <?php echo $miMejor !== null ? '$ ' . number_format((float)$miMejor, 2, ',', '.') : 'Sin pujas'; ?>
            </strong>
        </div>
        <div class="training-join-meta-item">
            <span class="training-join-meta-label">Mejor valor de la ronda</span>
            <strong class="training-join-meta-value">
                <?php echo $lowestBid !== null ? '$ ' . number_format((float)$lowestBid, 2, ',', '.') : '—'; ?>
                <?php if (!empty($bestInfo['nombre_completo'])): ?>
                    <small><?php echo htmlspecialchars($bestInfo['nombre_completo']); ?></small>
                <?php endif; ?>
            </strong>
        </div>
    </div>

    <?php if ($ronda['estado'] === 'finalizada'): ?>
        <p>
            <?php if (!empty($fueGanador)): ?>
                <span class="puja-winner-badge">Usted ganó esta ronda</span>
            <?php elseif (!empty($ronda['ganador_nombre'])): ?>
                Ganador: <strong><?php echo htmlspecialchars($ronda['ganador_nombre']); ?></strong>
                <?php if ($ronda['ganador_valor'] !== null): ?>
                    — $ <?php echo number_format((float)$ronda['ganador_valor'], 2, ',', '.'); ?>
                <?php endif; ?>
            <?php endif; ?>
        </p>
    <?php endif; ?>
</section>

<h3>Secuencia de sus pujas</h3>
<p class="training-join-help">Orden cronológico. “Δ bajada” es cuánto mejoró respecto a su puja anterior.</p>

<table class="data-table" id="mis-pujas-table">
    <thead>
    <tr>
        <th>#</th>
        <th>Valor</th>
        <th>Δ bajada</th>
        <th>Fecha / hora</th>
    </tr>
    </thead>
    <tbody>
    <?php if (empty($misPujas)): ?>
        <tr><td colspan="4">No registró pujas en esta ronda (solo oferta inicial).</td></tr>
    <?php else: ?>
        <?php foreach ($misPujas as $p): ?>
            <tr>
                <td><?php echo (int)$p['n']; ?></td>
                <td><strong>$ <?php echo htmlspecialchars($p['valor_fmt']); ?></strong></td>
                <td>
                    <?php echo $p['delta_fmt'] !== null
                        ? '$ ' . htmlspecialchars($p['delta_fmt'])
                        : '—'; ?>
                </td>
                <td><?php echo htmlspecialchars($p['fecha']); ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

<p class="training-join-actions" style="margin-top: 18px;">
    <a class="btn btn-secondary" href="<?php echo BASE_URL; ?>index.php?action=participant_training_history">Volver al historial</a>
    <?php if ($ronda['estado'] === 'finalizada'): ?>
        <a class="btn btn-primary" href="<?php echo BASE_URL; ?>index.php?action=participant_training_summary&id=<?php echo (int)$ronda['id']; ?>">Ver resumen de ronda</a>
    <?php elseif (in_array($ronda['estado'], ['programada', 'en_curso'], true) && (int)$inscription['activo'] === 1): ?>
        <a class="btn btn-primary" href="<?php echo BASE_URL; ?>index.php?action=participant_training_puja&id=<?php echo (int)$ronda['id']; ?>">Ir a la puja</a>
    <?php endif; ?>
</p>
