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
    <script src="<?php echo js('admin-dashboard.js'); ?>"></script>
    
    <script>
        console.log('=== DASHBOARD HTML SCRIPT ===');
        console.log('Script loaded in dashboard.php');
        
        // Popup de prueba eliminado - ya no es necesario
        
        // Test de botones después de 3 segundos
        setTimeout(() => {
            console.log('=== TESTING EDIT BUTTONS ===');
            const editButtons = document.querySelectorAll('.btn-edit');
            console.log('Edit buttons found in test:', editButtons.length);
            
            if (editButtons.length > 0) {
                console.log('First edit button:', editButtons[0]);
                console.log('First edit button href:', editButtons[0].getAttribute('href'));
            } else {
                console.log('NO EDIT BUTTONS FOUND IN TEST');
                console.log('All buttons:', document.querySelectorAll('button, a'));
                console.log('All links:', document.querySelectorAll('a'));
            }
        }, 3000);
    </script>
</body>
</html>