<?php
require_once BASE_PATH . '/utils/url_helpers.php';
$isEdit = !empty($sala);
$action = $isEdit
    ? BASE_URL . 'index.php?action=admin_training_edit_sala&id=' . (int)$sala['id']
    : BASE_URL . 'index.php?action=admin_training_create_sala';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Editar' : 'Crear'; ?> sala - Prácticas</title>
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
            <h1><?php echo $isEdit ? 'Editar sala' : 'Crear sala de práctica'; ?></h1>
            <a class="btn-return" href="<?php echo BASE_URL; ?>index.php?action=admin_training_dashboard">Regresar</a>
        </header>

        <?php if (!empty($_SESSION['error_message'])): ?>
            <p class="error-message"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></p>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($action); ?>" class="form-card">
            <div class="form-group">
                <label for="titulo">Título</label>
                <input type="text" id="titulo" name="titulo" required value="<?php echo htmlspecialchars($sala['titulo'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="3"><?php echo htmlspecialchars($sala['descripcion'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="presupuesto_referencial">Presupuesto referencial</label>
                <input type="text" id="presupuesto_referencial" name="presupuesto_referencial" required
                       value="<?php echo isset($sala['presupuesto_referencial']) ? htmlspecialchars(number_format((float)$sala['presupuesto_referencial'], 2, '.', '')) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="variacion_minima">Variación mínima (%)</label>
                <input type="text" id="variacion_minima" name="variacion_minima" required
                       value="<?php echo htmlspecialchars($sala['variacion_minima'] ?? '1'); ?>">
            </div>
            <div class="form-group">
                <label for="duracion_minutos">Duración default (minutos)</label>
                <select id="duracion_minutos" name="duracion_minutos">
                    <?php foreach ([5, 10, 15] as $d): ?>
                        <option value="<?php echo $d; ?>" <?php echo ((int)($sala['duracion_minutos'] ?? 10) === $d) ? 'selected' : ''; ?>><?php echo $d; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="zona_horaria">Zona horaria</label>
                <input type="text" id="zona_horaria" name="zona_horaria"
                       value="<?php echo htmlspecialchars($sala['zona_horaria'] ?? 'America/Guayaquil'); ?>">
            </div>
            <div class="form-group">
                <label for="estado_sala">Estado</label>
                <select id="estado_sala" name="estado_sala">
                    <?php foreach (['borrador', 'activa', 'archivada'] as $est): ?>
                        <option value="<?php echo $est; ?>" <?php echo (($sala['estado_sala'] ?? 'activa') === $est) ? 'selected' : ''; ?>>
                            <?php echo $est; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <fieldset class="training-bots-fieldset">
                <legend>Bots rivales</legend>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="bots_enabled" value="1"
                            <?php echo !empty($sala['bots_enabled']) ? 'checked' : ''; ?>>
                        Incluir bots rivales en las rondas de esta sala
                    </label>
                </div>
                <div class="form-group">
                    <label for="bots_count">Cantidad de bots (1–5)</label>
                    <select id="bots_count" name="bots_count">
                        <?php for ($n = 1; $n <= 5; $n++): ?>
                            <option value="<?php echo $n; ?>" <?php echo ((int)($sala['bots_count'] ?? 2) === $n) ? 'selected' : ''; ?>>
                                <?php echo $n; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="bots_profile">Perfil de agresividad</label>
                    <select id="bots_profile" name="bots_profile">
                        <?php
                        $profiles = [
                            'pasivo' => 'Pasivo (pujan poco, más al final)',
                            'equilibrado' => 'Equilibrado',
                            'agresivo' => 'Agresivo (reaccionan rápido)'
                        ];
                        $currentProfile = $sala['bots_profile'] ?? 'equilibrado';
                        foreach ($profiles as $key => $label):
                        ?>
                            <option value="<?php echo $key; ?>" <?php echo $currentProfile === $key ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <p class="training-join-help">
                    Los bots usan las mismas reglas de puja que los participantes. No aparecen en el listado de usuarios del sistema.
                </p>
            </fieldset>

            <button type="submit" class="btn btn-primary"><?php echo $isEdit ? 'Guardar' : 'Crear sala'; ?></button>
        </form>
    </main>
</div>
</body>
</html>
