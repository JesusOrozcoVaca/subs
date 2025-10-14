<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar CPCs - Moderador</title>
    <link rel="stylesheet" href="<?php echo css('styles.css'); ?>">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Moderación</h2>
            <ul class="sidebar-menu">
                <li><a href="<?php echo url('moderator/dashboard'); ?>" data-target="dashboard">Dashboard</a></li>
                <li><a href="<?php echo url('moderator/manage-cpcs'); ?>" data-target="manage-cpcs">Gestionar CPCs</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <header class="dashboard-header">
                <h1>Gestionar CPCs</h1>
                <form action="<?php echo logout_url(); ?>" method="POST">
                    <button type="submit" class="logout-btn">Cerrar sesión</button>
                </form>
            </header>

            <div id="dynamic-content">
                <?php include __DIR__ . '/mod_manage_cpcs_content.php'; ?>
            </div>
        </main>
    </div>

    <script src="<?php echo js('moderator-dashboard.js'); ?>"></script>
</body>
</html>