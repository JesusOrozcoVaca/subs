<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluar Participantes - Moderador</title>
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
                <h1>Evaluar Participantes</h1>
                <form action="<?php echo logout_url(); ?>" method="POST">
                    <button type="submit" class="logout-btn">Cerrar sesión</button>
                </form>
            </header>

            <div id="dynamic-content">
                <?php 
                // Obtener datos del producto
                $product = $this->productModel->getProductById($productId);
                if ($product) {
                    echo "<div class='card'>";
                    echo "<h2>Producto: " . htmlspecialchars($product['nombre']) . "</h2>";
                    echo "<p><strong>Descripción:</strong> " . htmlspecialchars($product['descripcion']) . "</p>";
                    
                    echo "<h3>Participantes Inscritos</h3>";
                    echo "<div class='participants-list'>";
                    echo "<p>Aquí se mostrarán los participantes inscritos en este producto.</p>";
                    
                    // Simular lista de participantes (en un caso real vendría de la base de datos)
                    echo "<div class='participant-item'>";
                    echo "<h4>Participante Ejemplo</h4>";
                    echo "<p>Empresa: Empresa Ejemplo S.A.</p>";
                    echo "<p>Estado: Activo</p>";
                    echo "<div class='actions'>";
                    echo "<button class='btn btn-success'>Evaluar</button>";
                    echo "<button class='btn btn-info'>Ver Detalles</button>";
                    echo "</div>";
                    echo "</div>";
                    
                    echo "</div>";
                    
                    echo "<div class='actions'>";
                    echo "<a href='" . url('moderator/manage-product/' . $product['id']) . "' class='btn btn-secondary'>Volver al Producto</a>";
                    echo "<a href='" . url('moderator/dashboard') . "' class='btn btn-secondary'>Dashboard</a>";
                    echo "</div>";
                    echo "</div>";
                } else {
                    echo "<div class='error'>Producto no encontrado.</div>";
                    echo "<a href='" . url('moderator/dashboard') . "' class='btn btn-secondary'>Volver al Dashboard</a>";
                }
                ?>
            </div>
        </main>
    </div>

    <script src="<?php echo js('moderator-dashboard.js'); ?>"></script>
</body>
</html>
