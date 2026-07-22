<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prácticas de Puja - Admin</title>
    <link rel="stylesheet" href="<?php echo css('styles.css'); ?>">
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <h2>Administración</h2>
        <ul class="sidebar-menu">
            <li><a href="<?php echo url('admin/dashboard'); ?>">Dashboard</a></li>
            <li><a href="<?php echo url('admin/create-product'); ?>">Crear Producto</a></li>
            <li><a href="<?php echo url('admin/create-user'); ?>">Crear Usuario</a></li>
            <li><a href="<?php echo url('admin/create-cpc'); ?>">Crear CPC</a></li>
            <li><a href="<?php echo BASE_URL; ?>index.php?action=admin_training_dashboard" class="active">Prácticas de Puja</a></li>
        </ul>
    </aside>
    <main class="main-content">
        <header class="dashboard-header">
            <h1>Prácticas de Puja</h1>
            <form action="<?php echo logout_url(); ?>" method="POST">
                <button type="submit" class="logout-btn">Cerrar sesión</button>
            </form>
        </header>

        <?php if (!empty($_SESSION['success_message'])): ?>
            <p class="success-message"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></p>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error_message'])): ?>
            <p class="error-message"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></p>
        <?php endif; ?>

        <div style="margin-bottom: 16px;">
            <a class="btn btn-primary" href="<?php echo BASE_URL; ?>index.php?action=admin_training_create_sala">Crear sala</a>
        </div>

        <table class="data-table">
            <thead>
            <tr>
                <th>Código</th>
                <th>Título</th>
                <th>Presupuesto</th>
                <th>Variación</th>
                <th>Estado</th>
                <th>Rondas</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($salas)): ?>
                <tr><td colspan="7">No hay salas de práctica aún.</td></tr>
            <?php else: ?>
                <?php foreach ($salas as $sala): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($sala['codigo']); ?></td>
                        <td><?php echo htmlspecialchars($sala['titulo']); ?></td>
                        <td>$ <?php echo number_format((float)$sala['presupuesto_referencial'], 2, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($sala['variacion_minima']); ?>%</td>
                        <td><?php echo htmlspecialchars($sala['estado_sala']); ?></td>
                        <td><?php echo (int)$sala['total_rondas']; ?></td>
                        <td>
                            <a class="btn btn-small" href="<?php echo BASE_URL; ?>index.php?action=admin_training_view_sala&id=<?php echo (int)$sala['id']; ?>">Ver</a>
                            <a class="btn btn-small btn-edit" href="<?php echo BASE_URL; ?>index.php?action=admin_training_edit_sala&id=<?php echo (int)$sala['id']; ?>">Editar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </main>
</div>
<script src="<?php echo js('url-helper.js'); ?>"></script>
</body>
</html>
