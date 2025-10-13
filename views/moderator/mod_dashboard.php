<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Moderador</title>
    <link rel="stylesheet" href="/subs/public/css/styles.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Moderador</h2>
            <ul class="sidebar-menu">
                <li><a href="/subs/moderator/dashboard" data-target="dashboard">Dashboard</a></li>
                <li><a href="/subs/moderator/manage-cpcs" data-target="manage-cpcs">Gestionar CPCs</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <header class="dashboard-header">
                <h1>Dashboard de Moderador</h1>
                <form action="/subs/logout" method="POST">
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

    <script src="/subs/public/js/moderator-dashboard.js"></script>
</body>
</html>