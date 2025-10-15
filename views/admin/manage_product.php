<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Producto - Administrador</title>
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
                <h1>Gestionar Producto</h1>
                <form action="<?php echo logout_url(); ?>" method="POST">
                    <button type="submit" class="logout-btn">Cerrar sesión</button>
                </form>
            </header>

            <div id="dynamic-content">
                <?php 
                // Obtener datos del producto
                $product = $this->productModel->getProductById($id);
                if ($product) {
                    echo "<div class='card'>";
                    echo "<h2>Producto: " . htmlspecialchars($product['nombre']) . "</h2>";
                    echo "<p><strong>Descripción:</strong> " . htmlspecialchars($product['descripcion']) . "</p>";
                    echo "<p><strong>Estado:</strong> " . htmlspecialchars($product['estado']) . "</p>";
                    echo "<p><strong>Creado:</strong> " . date('d/m/Y H:i', strtotime($product['fecha_creacion'])) . "</p>";
                    echo "<div class='actions'>";
                    echo "<a href='" . url('admin/edit-product/' . $product['id']) . "' class='btn btn-edit'>Editar Producto</a>";
                    echo "<a href='" . url('admin/dashboard') . "' class='btn btn-secondary'>Volver al Dashboard</a>";
                    echo "</div>";
                    echo "</div>";
                } else {
                    echo "<div class='error'>Producto no encontrado.</div>";
                    echo "<a href='" . url('admin/dashboard') . "' class='btn btn-secondary'>Volver al Dashboard</a>";
                }
                ?>
            </div>
        </main>
    </div>

    <script src="<?php echo js('url-helper.js'); ?>"></script>
    <script src="<?php echo js('admin-dashboard.js'); ?>"></script>
</body>
</html>
