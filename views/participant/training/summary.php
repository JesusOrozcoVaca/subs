<?php if (!empty($_SESSION['error_message'])): ?>
    <p class="error-message"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></p>
<?php endif; ?>

<section class="form-card">
    <h2><?php echo htmlspecialchars($ronda['sala_titulo']); ?> — Ronda #<?php echo (int)$ronda['numero']; ?></h2>
    <p><strong>Código:</strong> <?php echo htmlspecialchars($ronda['sala_codigo']); ?></p>
    <?php if ($schedule): ?>
        <p><strong>Horario:</strong> <?php echo htmlspecialchars($schedule['start']); ?> — <?php echo htmlspecialchars($schedule['end']); ?>
            (<?php echo htmlspecialchars($schedule['timezone']); ?>)</p>
    <?php endif; ?>
</section>

<?php if (!empty($summary) && !empty($summary['columns'])): ?>
    <h3 style="margin-top: 18px; display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
        <span>Resumen de Puja</span>
        <?php if (!empty($summary['winner_name'])): ?>
            <span class="puja-winner-badge" title="Menor valor ofertado">
                Ganador: <?php echo htmlspecialchars($summary['winner_name']); ?>
            </span>
        <?php endif; ?>
    </h3>
    <div style="overflow-x:auto; max-width:100%;">
        <table style="border-collapse:collapse; width:max-content; min-width:520px;">
            <thead>
            <tr>
                <?php foreach ($summary['columns'] as $col): ?>
                    <th style="border:1px solid #2a2a2a; background:#2f94c7; color:#fff; padding:8px 10px; font-size:12px; text-transform:uppercase;">
                        <?php echo htmlspecialchars($col['name']); ?>
                    </th>
                <?php endforeach; ?>
            </tr>
            </thead>
            <tbody>
            <?php
            $max = max((int)($summary['max_entries'] ?? 0), 1);
            for ($i = 0; $i < $max; $i++):
            ?>
                <tr>
                    <?php foreach ($summary['columns'] as $col): $row = $col['rows'][$i] ?? null; ?>
                        <td style="border:1px solid #2a2a2a; background:#d3e1f1; padding:6px 10px; text-align:center; font-weight:700; font-size:12px;">
                            <?php echo $row ? htmlspecialchars($row['value']) : ''; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <?php foreach ($summary['columns'] as $col): $row = $col['rows'][$i] ?? null; ?>
                        <td style="border:1px solid #2a2a2a; background:#b8c9dd; padding:4px 10px; text-align:center; font-size:12px;">
                            <?php echo $row ? htmlspecialchars($row['time']) : ''; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endfor; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <p>No hay datos de resumen para esta ronda.</p>
<?php endif; ?>

<p style="margin-top: 20px;">
    <a class="btn btn-primary" href="<?php echo BASE_URL; ?>index.php?action=participant_training_list">Volver a prácticas</a>
</p>
