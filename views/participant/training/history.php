<?php if (!empty($_SESSION['error_message'])): ?>
    <p class="error-message"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></p>
<?php endif; ?>

<div class="training-history-head">
    <p>Revise sus prácticas anteriores y la secuencia de pujas para analizar decisiones y resultados.</p>
    <a class="btn btn-secondary" href="<?php echo BASE_URL; ?>index.php?action=participant_training_list">Volver a prácticas abiertas</a>
</div>

<table class="data-table">
    <thead>
    <tr>
        <th>Sala</th>
        <th>Ronda</th>
        <th>Horario</th>
        <th>Estado</th>
        <th>Oferta inicial</th>
        <th>Mi mejor puja</th>
        <th>Pujas</th>
        <th>Resultado</th>
        <th>Acción</th>
    </tr>
    </thead>
    <tbody>
    <?php if (empty($historial)): ?>
        <tr><td colspan="9">Aún no tiene prácticas registradas.</td></tr>
    <?php else: ?>
        <?php foreach ($historial as $row):
            $schedule = $row['schedule'] ?? null;
            ?>
            <tr>
                <td>
                    <strong><?php echo htmlspecialchars($row['sala_codigo']); ?></strong><br>
                    <?php echo htmlspecialchars($row['sala_titulo']); ?>
                </td>
                <td>#<?php echo (int)$row['numero']; ?></td>
                <td>
                    <?php if ($schedule): ?>
                        <?php echo htmlspecialchars($schedule['start']); ?><br>
                        <small><?php echo htmlspecialchars($schedule['timezone']); ?></small>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($row['estado']); ?></td>
                <td>$ <?php echo number_format((float)$row['oferta_inicial'], 2, ',', '.'); ?></td>
                <td>
                    <?php echo $row['mi_mejor_puja'] !== null
                        ? '$ ' . number_format((float)$row['mi_mejor_puja'], 2, ',', '.')
                        : '—'; ?>
                </td>
                <td><?php echo (int)$row['total_mis_pujas']; ?></td>
                <td>
                    <?php if ($row['estado'] === 'finalizada'): ?>
                        <?php if (!empty($row['fue_ganador'])): ?>
                            <span class="puja-winner-badge">Ganó</span>
                        <?php elseif (!empty($row['ganador_nombre'])): ?>
                            <small>Ganador: <?php echo htmlspecialchars($row['ganador_nombre']); ?></small>
                            <?php if ($row['ganador_valor'] !== null): ?>
                                <br><small>$ <?php echo number_format((float)$row['ganador_valor'], 2, ',', '.'); ?></small>
                            <?php endif; ?>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (in_array($row['estado'], ['programada', 'en_curso'], true) && (int)$row['activo'] === 1): ?>
                        <a class="btn btn-small"
                           href="<?php echo BASE_URL; ?>index.php?action=participant_training_puja&id=<?php echo (int)$row['ronda_id']; ?>">
                            Entrar
                        </a>
                    <?php endif; ?>
                    <a class="btn btn-small btn-secondary"
                       href="<?php echo BASE_URL; ?>index.php?action=participant_training_history_detail&id=<?php echo (int)$row['ronda_id']; ?>">
                        Ver pujas
                    </a>
                    <?php if ($row['estado'] === 'finalizada'): ?>
                        <a class="btn btn-small"
                           href="<?php echo BASE_URL; ?>index.php?action=participant_training_summary&id=<?php echo (int)$row['ronda_id']; ?>">
                            Resumen
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
