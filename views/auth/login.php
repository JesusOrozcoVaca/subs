<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Subastas Inversas</title>
    <link rel="stylesheet" href="<?php echo css('styles.css'); ?>">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-image">
                <h2>Simulador de Subasta Inversa Electrónica</h2>
                <p>Chat con nosotros: <a href="https://wa.link/hnolju">Whatsapp</a></p>
                <center><img src="<?php echo BASE_URL . 'public/images/whatsapp_qr.png'; ?>" alt="QR de whatsapp" width="100" height="100"></center>
            </div>
            <div class="login-form">
                <h2>Iniciar Sesión</h2>
                <?php if (isset($error)): ?>
                    <p class="error-message"><?php echo $error; ?></p>
                <?php endif; ?>
                <form action="<?php echo login_url(); ?>" method="POST" id="loginForm">
                    <div class="form-group">
                        <label for="username">Correo Electrónico</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn">Iniciar sesión</button>
                </form>
            </div>
        </div>
    </div>
    <footer>
        <p>Desarrollado por: <strong>HJ Consulting Management C.Ltda.</strong></p>
    </footer>
    <script src="<?php echo js('url-helper.js'); ?>"></script>
    <script src="<?php echo js('login.js'); ?>"></script>
    <script>
        // Prevenir cualquier sidebar o dashboard-container en la página de login
        document.addEventListener('DOMContentLoaded', function() {
            // Ocultar cualquier sidebar existente
            const sidebars = document.querySelectorAll('.sidebar, .dashboard-container, aside.sidebar, .process-phases');
            sidebars.forEach(function(sidebar) {
                sidebar.style.display = 'none';
                sidebar.style.visibility = 'hidden';
                sidebar.style.width = '0';
                sidebar.style.height = '0';
            });
            
            // Observar si se crean nuevos elementos de sidebar
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) { // Element node
                            if (node.classList && (node.classList.contains('sidebar') || 
                                node.classList.contains('dashboard-container') || 
                                node.classList.contains('process-phases'))) {
                                node.style.display = 'none';
                                node.style.visibility = 'hidden';
                                node.style.width = '0';
                                node.style.height = '0';
                            }
                            // Verificar también en hijos
                            const sidebarsInNode = node.querySelectorAll && node.querySelectorAll('.sidebar, .dashboard-container, aside.sidebar, .process-phases');
                            if (sidebarsInNode) {
                                sidebarsInNode.forEach(function(sidebar) {
                                    sidebar.style.display = 'none';
                                    sidebar.style.visibility = 'hidden';
                                    sidebar.style.width = '0';
                                    sidebar.style.height = '0';
                                });
                            }
                        }
                    });
                });
            });
            
            // Observar cambios en el body
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        });
    </script>
</body>
</html>