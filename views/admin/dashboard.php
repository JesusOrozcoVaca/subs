<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Administrador</title>
    <link rel="stylesheet" href="<?php echo css('styles.css'); ?>">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Administración</h2>
            <ul class="sidebar-menu">
                <li><a href="<?php echo url('admin/dashboard'); ?>" data-target="dashboard">Dashboard</a></li>
                <li><a href="<?php echo url('admin/create-product'); ?>" data-target="create-product">Crear Producto</a></li>
                <li><a href="<?php echo url('admin/create-user'); ?>" data-target="create-user">Crear Usuario</a></li>
                <li><a href="<?php echo url('admin/create-cpc'); ?>" data-target="create-cpc">Crear CPC</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <header class="dashboard-header">
                <h1>Dashboard de Administrador</h1>
                <form action="<?php echo logout_url(); ?>" method="POST">
                    <button type="submit" class="logout-btn">Cerrar sesión</button>
                </form>
            </header>

            <div id="dynamic-content">
                <?php
                // Si estamos gestionando un producto, cargar ese contenido
                if (isset($product) && !empty($product)) {
                    include BASE_PATH . '/views/admin/manage_product_content.php';
                } else {
                    // Cargar el contenido normal del dashboard
                    include BASE_PATH . '/views/admin/dashboard_content.php';
                }
                ?>
            </div>
        </main>
    </div>

    <script src="<?php echo js('url-helper.js'); ?>"></script>
    <script src="<?php echo js('admin-dashboard.js'); ?>"></script>
</body>
</html>