<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Moderador</title>
    <link rel="stylesheet" href="<?php echo css('styles.css'); ?>">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Moderador</h2>
            <ul class="sidebar-menu">
                <li><a href="<?php echo url('moderator/dashboard'); ?>" data-target="dashboard">Dashboard</a></li>
                <li><a href="<?php echo url('moderator/manage-cpcs'); ?>" data-target="manage-cpcs">Gestionar CPCs</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <header class="dashboard-header">
                <h1>Dashboard de Moderador</h1>
                <form action="<?php echo logout_url(); ?>" method="POST">
                    <button type="submit" class="logout-btn">Cerrar sesión</button>
                </form>
            </header>

            <div id="dynamic-content">
                <?php
                // Si estamos gestionando un producto, cargar ese contenido
                if (isset($product) && !empty($product)) {
                    error_log("=== DASHBOARD: LOADING PRODUCT MANAGEMENT CONTENT ===");
                    include __DIR__ . '/mod_manage_product_content.php';
                } else {
                    error_log("=== DASHBOARD: LOADING NORMAL DASHBOARD CONTENT ===");
                    // Cargar el contenido normal del dashboard
                    // Asegurar que la variable $products esté disponible
                    if (!isset($products)) {
                        error_log("ERROR: Variable \$products not available in dashboard");
                        echo "<p>Error: No se pudieron cargar los productos</p>";
                    } else {
                        include __DIR__ . '/mod_dashboard_content.php';
                    }
                }
                ?>
            </div>
        </main>
    </div>

    <script src="<?php echo js('url-helper.js'); ?>"></script>
    <script src="<?php echo js('moderator-dashboard.js'); ?>"></script>
    <script>
        console.log('Dashboard JavaScript loaded');
        console.log('Dynamic content:', document.getElementById('dynamic-content'));
        console.log('URLS object:', URLS);
        console.log('Moderator dashboard URL:', URLS.moderatorDashboard());
        console.log('Is new system:', isNewSystem());
        console.log('Base URL:', getBaseUrl());
        
        // Asegurar que los event listeners se adjunten después de que el contenido se cargue
        setTimeout(() => {
            console.log('Setting up form listeners after timeout...');
            const forms = document.querySelectorAll('#dynamic-content form');
            console.log('Forms found after timeout:', forms.length);
            forms.forEach((form, index) => {
                console.log(`Form ${index + 1} after timeout:`, form);
            });
        }, 1000);
    </script>
</body>
</html>