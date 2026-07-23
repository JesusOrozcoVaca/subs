<?php if (!empty($_SESSION['error_message'])): ?>
    <p class="error-message"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></p>
<?php endif; ?>

<section class="training-join form-card">
    <header class="training-join-header">
        <p class="training-join-code"><?php echo htmlspecialchars($ronda['sala_codigo']); ?></p>
        <h2 class="training-join-title">
            <?php echo htmlspecialchars($ronda['sala_titulo']); ?>
            <span class="training-join-round">Ronda #<?php echo (int)$ronda['numero']; ?></span>
        </h2>
        <?php if (!empty($ronda['sala_descripcion'])): ?>
            <p class="training-join-desc"><?php echo nl2br(htmlspecialchars($ronda['sala_descripcion'])); ?></p>
        <?php endif; ?>
    </header>

    <div class="training-join-meta">
        <div class="training-join-meta-item">
            <span class="training-join-meta-label">Presupuesto referencial</span>
            <strong class="training-join-meta-value">
                $ <?php echo number_format((float)$ronda['presupuesto_referencial'], 2, ',', '.'); ?>
            </strong>
        </div>
        <div class="training-join-meta-item">
            <span class="training-join-meta-label">Variación mínima</span>
            <strong class="training-join-meta-value"><?php echo htmlspecialchars($ronda['variacion_minima']); ?>%</strong>
        </div>
        <?php if ($schedule): ?>
            <div class="training-join-meta-item">
                <span class="training-join-meta-label">Inicio</span>
                <strong class="training-join-meta-value">
                    <?php echo htmlspecialchars($schedule['start']); ?>
                    <small><?php echo htmlspecialchars($schedule['timezone']); ?></small>
                </strong>
            </div>
            <div class="training-join-meta-item">
                <span class="training-join-meta-label">Fin</span>
                <strong class="training-join-meta-value"><?php echo htmlspecialchars($schedule['end']); ?></strong>
            </div>
        <?php endif; ?>
    </div>

    <form class="training-join-form" method="POST"
          action="<?php echo BASE_URL; ?>index.php?action=participant_training_join&id=<?php echo (int)$ronda['id']; ?>">
        <input type="hidden" name="ronda_id" value="<?php echo (int)$ronda['id']; ?>">

        <div class="form-group training-join-offer">
            <label for="oferta_inicial">Su oferta inicial</label>
            <div class="training-money-input">
                <span class="training-money-prefix" aria-hidden="true">$</span>
                <input type="text" id="oferta_inicial" name="oferta_inicial" required
                       inputmode="decimal"
                       placeholder="Ej: 95000 o 95.000,00"
                       value="<?php echo htmlspecialchars(number_format((float)$ronda['presupuesto_referencial'], 0, '', '')); ?>"
                       title="Debe ser mayor a 0 y no superar el presupuesto referencial">
            </div>
            <p class="training-join-help">
                Debe ser mayor a 0 y menor o igual al presupuesto referencial.
                Esta base se usa para calcular la variación mínima de sus pujas.
            </p>
        </div>

        <div class="training-join-actions">
            <button type="submit" class="btn btn-primary">Confirmar e ingresar</button>
            <a class="btn btn-secondary" href="<?php echo BASE_URL; ?>index.php?action=participant_training_list">Cancelar</a>
        </div>
    </form>
</section>
