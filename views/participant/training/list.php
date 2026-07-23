<?php if (!empty($_SESSION['success_message'])): ?>
    <p class="success-message"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></p>
<?php endif; ?>
<?php if (!empty($_SESSION['error_message'])): ?>
    <p class="error-message"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></p>
<?php endif; ?>

<div class="training-history-head">
    <p>Bancos de puja habilitados para entrenamiento. Compite en vivo con otros participantes usando las mismas reglas del simulador.</p>
    <a class="btn btn-secondary" href="<?php echo BASE_URL; ?>index.php?action=participant_training_history">
        Historial de mis pujas
    </a>
</div>

<table class="data-table">
    <thead>
    <tr>
        <th>Sala</th>
        <th>Ronda</th>
        <th>Presupuesto ref.</th>
        <th>Horario</th>
        <th>Estado</th>
        <th>Acción</th>
    </tr>
    </thead>
    <tbody>
    <?php if (empty($items)): ?>
        <tr><td colspan="6">No hay prácticas abiertas en este momento.</td></tr>
    <?php else: ?>
        <?php foreach ($items as $item):
            $ronda = $item['ronda'];
            $schedule = $item['schedule'];
            ?>
            <tr>
                <td>
                    <strong><?php echo htmlspecialchars($ronda['sala_codigo']); ?></strong><br>
                    <?php echo htmlspecialchars($ronda['sala_titulo']); ?>
                </td>
                <td>#<?php echo (int)$ronda['numero']; ?></td>
                <td>$ <?php echo number_format((float)$ronda['presupuesto_referencial'], 2, ',', '.'); ?></td>
                <td>
                    <?php if ($schedule): ?>
                        <?php echo htmlspecialchars($schedule['start']); ?> — <?php echo htmlspecialchars($schedule['end']); ?>
                        <br><small><?php echo htmlspecialchars($schedule['timezone']); ?></small>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($ronda['estado']); ?></td>
                <td>
                    <?php if ($item['inscrito'] && $item['activo']): ?>
                        <a class="btn btn-small btn-primary"
                           href="<?php echo BASE_URL; ?>index.php?action=participant_training_puja&id=<?php echo (int)$ronda['id']; ?>">
                            Entrar a puja
                        </a>
                    <?php elseif ($item['inscrito'] && !$item['activo']): ?>
                        <span>Inscripción desactivada</span>
                    <?php else: ?>
                        <a class="btn btn-small"
                           href="<?php echo BASE_URL; ?>index.php?action=participant_training_join&id=<?php echo (int)$ronda['id']; ?>">
                            Ingresar
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
