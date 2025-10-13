<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Participante</title>
    <link rel="stylesheet" href="/subs/public/css/styles.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Participante</h2>
            <ul class="sidebar-menu">
                <li><a href="/subs/participant/dashboard">Dashboard</a></li>
                <li><a href="/subs/participant/profile">Mi Perfil</a></li>
                <li><a href="/subs/participant/search-process">Buscar Proceso</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <header class="dashboard-header">
                <h1><?php echo $pageTitle; ?></h1>
                <form action="/subs/logout" method="POST">
                    <button type="submit" class="logout-btn">Cerrar sesión</button>
                </form>
            </header>

            <div id="dynamic-content">
                <?php echo $content; ?>
            </div>
        </main>
    </div>

    <script src="/subs/public/js/participant-dashboard.js"></script>
</body>
</html>