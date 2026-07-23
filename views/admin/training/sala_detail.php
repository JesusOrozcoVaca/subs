<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($sala['codigo']); ?> - Prácticas</title>
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
            <h1><?php echo htmlspecialchars($sala['titulo']); ?></h1>
            <a class="btn-return" href="<?php echo BASE_URL; ?>index.php?action=admin_training_dashboard">Regresar</a>
        </header>

        <?php if (!empty($_SESSION['success_message'])): ?>
            <p class="success-message"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></p>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error_message'])): ?>
            <p class="error-message"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></p>
        <?php endif; ?>

        <section class="form-card" style="margin-bottom: 20px;">
            <p><strong>Código:</strong> <?php echo htmlspecialchars($sala['codigo']); ?></p>
            <p><strong>Presupuesto referencial:</strong> $ <?php echo number_format((float)$sala['presupuesto_referencial'], 2, ',', '.'); ?></p>
            <p><strong>Variación mínima:</strong> <?php echo htmlspecialchars($sala['variacion_minima']); ?>%</p>
            <p><strong>Estado:</strong> <?php echo htmlspecialchars($sala['estado_sala']); ?></p>
            <p><strong>Zona:</strong> <?php echo htmlspecialchars($sala['zona_horaria']); ?></p>
            <p><strong>Bots rivales:</strong>
                <?php if (!empty($sala['bots_enabled'])): ?>
                    Sí — <?php echo (int)($sala['bots_count'] ?? 0); ?> bot(s), perfil
                    <?php echo htmlspecialchars($sala['bots_profile'] ?? 'equilibrado'); ?>
                <?php else: ?>
                    No
                <?php endif; ?>
            </p>
            <?php if (!empty($sala['descripcion'])): ?>
                <p><?php echo nl2br(htmlspecialchars($sala['descripcion'])); ?></p>
            <?php endif; ?>
            <a class="btn btn-small btn-edit" href="<?php echo BASE_URL; ?>index.php?action=admin_training_edit_sala&id=<?php echo (int)$sala['id']; ?>">Editar sala</a>
        </section>

        <?php if ($sala['estado_sala'] === 'activa' && empty($rondaAbierta)): ?>
            <section class="form-card" style="margin-bottom: 20px;">
                <h3>Nueva ronda</h3>
                <form method="POST" action="<?php echo BASE_URL; ?>index.php?action=admin_training_create_ronda">
                    <input type="hidden" name="sala_id" value="<?php echo (int)$sala['id']; ?>">
                    <div class="form-group">
                        <label for="hora_inicio_local">Inicio (hora local de la zona)</label>
                        <input type="datetime-local" id="hora_inicio_local" name="hora_inicio_local" required>
                    </div>
                    <div class="form-group">
                        <label for="duracion_minutos">Duración (minutos)</label>
                        <select name="duracion_minutos" id="duracion_minutos">
                            <?php foreach ([5, 10, 15] as $d): ?>
                                <option value="<?php echo $d; ?>" <?php echo ((int)$sala['duracion_minutos'] === $d) ? 'selected' : ''; ?>><?php echo $d; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <input type="hidden" name="zona_horaria" value="<?php echo htmlspecialchars($sala['zona_horaria']); ?>">
                    <button type="submit" class="btn btn-primary">Abrir ronda</button>
                </form>
            </section>
        <?php elseif (!empty($rondaAbierta)): ?>
            <p>Hay una ronda abierta (#<?php echo (int)$rondaAbierta['numero']; ?> -
                <a href="<?php echo BASE_URL; ?>index.php?action=admin_training_ronda_detail&id=<?php echo (int)$rondaAbierta['id']; ?>">ver detalle</a>).
            </p>
        <?php endif; ?>

        <h3>Historial de rondas</h3>
        <table class="data-table">
            <thead>
            <tr>
                <th>#</th>
                <th>Estado</th>
                <th>Inicio UTC</th>
                <th>Duración</th>
                <th>Ganador</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($rondas)): ?>
                <tr><td colspan="6">Sin rondas.</td></tr>
            <?php else: ?>
                <?php foreach ($rondas as $ronda): ?>
                    <tr>
                        <td><?php echo (int)$ronda['numero']; ?></td>
                        <td><?php echo htmlspecialchars($ronda['estado']); ?></td>
                        <td><?php echo htmlspecialchars($ronda['hora_inicio']); ?></td>
                        <td><?php echo (int)$ronda['duracion_minutos']; ?> min</td>
                        <td>
                            <?php
                            if (!empty($ronda['ganador_nombre'])) {
                                echo htmlspecialchars($ronda['ganador_nombre']);
                                if (isset($ronda['ganador_valor'])) {
                                    echo ' ($ ' . number_format((float)$ronda['ganador_valor'], 2, ',', '.') . ')';
                                }
                            } else {
                                echo '—';
                            }
                            ?>
                        </td>
                        <td>
                            <a class="btn btn-small" href="<?php echo BASE_URL; ?>index.php?action=admin_training_ronda_detail&id=<?php echo (int)$ronda['id']; ?>">Detalle</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </main>
</div>
</body>
</html>
