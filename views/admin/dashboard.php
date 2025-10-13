<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Administrador</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/styles.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Administración</h2>
            <ul class="sidebar-menu">
                <li><a href="<?= BASE_URL ?>admin/dashboard" data-target="dashboard">Dashboard</a></li>
                <li><a href="<?= BASE_URL ?>admin/create-product" data-target="create-product">Crear Producto</a></li>
                <li><a href="<?= BASE_URL ?>admin/create-user" data-target="create-user">Crear Usuario</a></li>
                <li><a href="<?= BASE_URL ?>admin/create-cpc" data-target="create-cpc">Crear CPC</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <header class="dashboard-header">
                <h1>Dashboard de Administrador</h1>
                <form action="<?= BASE_URL ?>logout" method="POST">
                    <button type="submit" class="logout-btn">Cerrar sesión</button>
                </form>
            </header>

            <div id="dynamic-content">
                <?php
                // Carga inicial del contenido del dashboard
                $users = $this->userModel->getAllUsers();
                $products = $this->productModel->getAllProducts();
                $cpcs = $this->cpcModel->getAllCPCs();
                include BASE_PATH . '/views/admin/dashboard_content.php';
                ?>
            </div>
        </main>
    </div>

    <script src="<?= BASE_URL ?>public/js/admin-dashboard.js"></script>
</body>
</html>