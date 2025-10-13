<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Moderador</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/styles.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Moderador</h2>
            <ul class="sidebar-menu">
                <li><a href="<?= BASE_URL ?>moderator/dashboard" data-target="dashboard">Dashboard</a></li>
                <li><a href="<?= BASE_URL ?>moderator/manage-cpcs" data-target="manage-cpcs">Gestionar CPCs</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <header class="dashboard-header">
                <h1>Dashboard de Moderador</h1>
                <form action="<?= BASE_URL ?>logout" method="POST">
                    <button type="submit" class="logout-btn">Cerrar sesión</button>
                </form>
            </header>

            <div id="dynamic-content">
                <?php
                // Carga inicial del contenido del dashboard
                $products = $this->productModel->getAllActive();
                include __DIR__ . '/mod_dashboard_content.php';
                ?>
            </div>
        </main>
    </div>

    <script src="<?= BASE_URL ?>public/js/moderator-dashboard.js"></script>
</body>
</html>