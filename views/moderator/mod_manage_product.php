<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Producto - Moderador</title>
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
                <h1>Gestionar Producto</h1>
                <form action="<?php echo logout_url(); ?>" method="POST">
                    <button type="submit" class="logout-btn">Cerrar sesión</button>
                </form>
            </header>

            <div id="dynamic-content">
                <?php include __DIR__ . '/mod_manage_product_content.php'; ?>
            </div>
        </main>
    </div>

    <!-- Modal para responder preguntas -->
    <div id="answer-questions-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-title">Responder Preguntas</h3>
                <span class="close" onclick="closeAnswerQuestionsModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="questions-container">
                    <!-- Las preguntas se cargarán aquí dinámicamente -->
                </div>
                <div class="modal-actions" style="margin-top: 20px; text-align: right;">
                    <button id="save-answers" class="btn btn-primary" style="display: none;">Enviar respuestas</button>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo js('url-helper.js'); ?>"></script>
    <script src="<?php echo js('moderator-dashboard.js'); ?>"></script>
</body>
</html>