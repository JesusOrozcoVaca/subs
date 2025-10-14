<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Subastas Inversas</title>
    <link rel="stylesheet" href="<?php echo css('styles.css'); ?>">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-image">
                <h2>Simulador de Subasta Inversa Electrónica</h2>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
            </div>
            <div class="login-form">
                <h2>Iniciar Sesión</h2>
                <?php if (isset($error)): ?>
                    <p class="error-message"><?php echo $error; ?></p>
                <?php endif; ?>
                <form action="<?php echo login_url(); ?>" method="POST" id="loginForm">
                    <div class="form-group">
                        <label for="username">Usuario o Correo Electrónico</label>
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
    <script src="<?php echo js('url-helper.js'); ?>"></script>
    <script src="<?php echo js('login.js'); ?>"></script>
</body>
</html>