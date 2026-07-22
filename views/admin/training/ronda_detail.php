<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ronda #<?php echo (int)$ronda['numero']; ?> - Prácticas</title>
    <link rel="stylesheet" href="<?php echo css('styles.css'); ?>">
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <h2>Administración</h2>
        <ul class="sidebar-menu">
            <li><a href="<?php echo url('admin/dashboard'); ?>">Dashboard</a></li>
            <li><a href="<?php echo BASE_URL; ?>index.php?action=admin_training_dashboard">Prácticas de Puja</a></li>
        </ul>
    </aside>
    <main class="main-content">
        <header class="dashboard-header">
            <h1><?php echo htmlspecialchars($ronda['sala_titulo']); ?> — Ronda #<?php echo (int)$ronda['numero']; ?></h1>
            <a class="btn-return" href="<?php echo BASE_URL; ?>index.php?action=admin_training_view_sala&id=<?php echo (int)$ronda['sala_id']; ?>">Regresar</a>
        </header>

        <?php if (!empty($_SESSION['success_message'])): ?>
            <p class="success-message"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></p>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error_message'])): ?>
            <p class="error-message"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></p>
        <?php endif; ?>

        <section class="form-card" style="margin-bottom: 16px;">
            <p><strong>Estado:</strong> <?php echo htmlspecialchars($ronda['estado']); ?></p>
            <?php if ($schedule): ?>
                <p><strong>Inicio:</strong> <?php echo htmlspecialchars($schedule['start']); ?> (<?php echo htmlspecialchars($schedule['timezone']); ?>)</p>
                <p><strong>Fin:</strong> <?php echo htmlspecialchars($schedule['end']); ?></p>
            <?php endif; ?>
            <p><strong>Mejor valor actual:</strong>
                <?php echo $lowestBid !== null ? '$ ' . number_format((float)$lowestBid, 2, ',', '.') : 'Sin pujas'; ?>
            </p>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <?php if ($ronda['estado'] === 'programada'): ?>
                    <form method="POST" action="<?php echo BASE_URL; ?>index.php?action=admin_training_cancel_ronda">
                        <input type="hidden" name="id" value="<?php echo (int)$ronda['id']; ?>">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('¿Cancelar esta ronda?');">Cancelar ronda</button>
                    </form>
                <?php endif; ?>
                <?php if (in_array($ronda['estado'], ['programada', 'en_curso'], true)): ?>
                    <form method="POST" action="<?php echo BASE_URL; ?>index.php?action=admin_training_close_ronda">
                        <input type="hidden" name="id" value="<?php echo (int)$ronda['id']; ?>">
                        <button type="submit" class="btn btn-primary" onclick="return confirm('¿Cerrar la ronda ahora?');">Cerrar ronda</button>
                    </form>
                <?php endif; ?>
            </div>
        </section>

        <h3>Inscritos</h3>
        <table class="data-table" id="inscritos-table">
            <thead>
            <tr>
                <th>Participante</th>
                <th>Oferta inicial</th>
                <th>Estado</th>
                <th>Acción</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($inscritos)): ?>
                <tr><td colspan="4">Aún no hay inscritos.</td></tr>
            <?php else: ?>
                <?php foreach ($inscritos as $ins): ?>
                    <tr data-inscripcion-id="<?php echo (int)$ins['id']; ?>">
                        <td><?php echo htmlspecialchars($ins['nombre_completo']); ?></td>
                        <td>$ <?php echo number_format((float)$ins['oferta_inicial'], 2, ',', '.'); ?></td>
                        <td class="ins-estado"><?php echo ((int)$ins['activo'] === 1) ? 'Activo' : 'Inactivo'; ?></td>
                        <td>
                            <button type="button" class="btn btn-small btn-toggle-ins"
                                    data-id="<?php echo (int)$ins['id']; ?>"
                                    data-activo="<?php echo (int)$ins['activo'] === 1 ? 0 : 1; ?>">
                                <?php echo ((int)$ins['activo'] === 1) ? 'Desactivar' : 'Activar'; ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>

        <?php if ($ronda['estado'] === 'finalizada' && !empty($summary)): ?>
            <h3 style="margin-top: 18px; display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <span>Resumen de Puja</span>
                <?php if (!empty($summary['winner_name'])): ?>
                    <span class="puja-winner-badge">Ganador: <?php echo htmlspecialchars($summary['winner_name']); ?></span>
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
        <?php endif; ?>
    </main>
</div>
<script src="<?php echo js('url-helper.js'); ?>?v=20260722t"></script>
<script src="<?php echo js('admin-training.js'); ?>?v=20260722t"></script>
</body>
</html>
