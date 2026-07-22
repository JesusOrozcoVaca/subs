<?php if (!empty($_SESSION['error_message'])): ?>
    <p class="error-message"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></p>
<?php endif; ?>

<section class="form-card">
    <h2><?php echo htmlspecialchars($ronda['sala_titulo']); ?> — Ronda #<?php echo (int)$ronda['numero']; ?></h2>
    <p><?php echo htmlspecialchars($ronda['sala_codigo']); ?></p>
    <?php if (!empty($ronda['sala_descripcion'])): ?>
        <p><?php echo nl2br(htmlspecialchars($ronda['sala_descripcion'])); ?></p>
    <?php endif; ?>

    <p><strong>Presupuesto referencial:</strong> $ <?php echo number_format((float)$ronda['presupuesto_referencial'], 2, ',', '.'); ?></p>
    <p><strong>Variación mínima:</strong> <?php echo htmlspecialchars($ronda['variacion_minima']); ?>%</p>
    <?php if ($schedule): ?>
        <p><strong>Inicio:</strong> <?php echo htmlspecialchars($schedule['start']); ?> (<?php echo htmlspecialchars($schedule['timezone']); ?>)</p>
        <p><strong>Fin:</strong> <?php echo htmlspecialchars($schedule['end']); ?></p>
    <?php endif; ?>

    <form method="POST" action="<?php echo BASE_URL; ?>index.php?action=participant_training_join&id=<?php echo (int)$ronda['id']; ?>">
        <input type="hidden" name="ronda_id" value="<?php echo (int)$ronda['id']; ?>">
        <div class="form-group">
            <label for="oferta_inicial">Su oferta inicial</label>
            <input type="text" id="oferta_inicial" name="oferta_inicial" required
                   placeholder="Ej: 100000 o 100000,00"
                   title="Debe ser mayor a 0 y no superar el presupuesto referencial">
            <small>Debe ser mayor a 0 y menor o igual al presupuesto referencial. Esta base se usa para calcular la variación mínima de sus pujas.</small>
        </div>
        <button type="submit" class="btn btn-primary">Confirmar e ingresar</button>
        <a class="btn" href="<?php echo BASE_URL; ?>index.php?action=participant_training_list">Cancelar</a>
    </form>
</section>
