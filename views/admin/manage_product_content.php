<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>
<div class="card">
    <h2>Gestionar Producto</h2>
    <?php 
    // El producto ya fue obtenido por el controlador
    if ($product) {
        echo "<div class='product-details'>";
        echo "<h3>Producto: " . htmlspecialchars($product['objeto_proceso']) . "</h3>";
        echo "<p><strong>Descripción:</strong> " . htmlspecialchars($product['descripcion']) . "</p>";
        echo "<p><strong>Estado:</strong> " . htmlspecialchars($product['estado_descripcion']) . "</p>";
        echo "<p><strong>Creado:</strong> " . date('d/m/Y H:i', strtotime($product['fecha_creacion'])) . "</p>";
        echo "<p><strong>Presupuesto:</strong> $" . number_format((float)($product['presupuesto_referencial'] ?? 0), 2) . "</p>";
        echo "<p><strong>Variación mínima:</strong> " . htmlspecialchars($product['variacion_minima'] ?? '') . " %</p>";
        echo "<p><strong>Plazo de entrega:</strong> " . htmlspecialchars($product['plazo_entrega'] ?? '') . "</p>";
        echo "<p><strong>Vigencia de la Oferta:</strong> " . htmlspecialchars($product['vigencia_oferta'] ?? '') . "</p>";
        echo "<p><strong>Funcionario encargado:</strong> " . htmlspecialchars($product['funcionario_encargado'] ?? '') . "</p>";
        
        // Formulario para cambiar estado
        echo "<div class='change-status-section'>";
        echo "<h4>Cambiar Estado del Proceso</h4>";
        echo "<form action='" . url('admin/manage-product/' . $product['id']) . "' method='POST'>";
        echo "<input type='hidden' name='action' value='change_status'>";
        echo "<select name='estado_id' required>";
        foreach ($estados as $estado) {
            $selected = ($estado['id'] == $product['estado_id']) ? 'selected' : '';
            echo "<option value='" . $estado['id'] . "' " . $selected . ">" . htmlspecialchars($estado['descripcion']) . "</option>";
        }
        echo "</select>";
        echo "<button type='submit' class='btn btn-primary'>Actualizar Estado</button>";
        echo "</form>";
        echo "</div>";
        
        echo "<div class='actions' style='display: flex; gap: 15px; flex-wrap: wrap; margin-top: 20px;'>";
        echo "<a href='" . url('admin/edit-product/' . $product['id']) . "' class='btn btn-edit'>Editar Producto</a>";
        echo "<button class='btn btn-info btn-answer-questions' data-product-id='" . htmlspecialchars($product['id']) . "' data-product-code='" . htmlspecialchars($product['codigo']) . "'>Responder Preguntas</button>";
        echo "<button class='btn btn-info btn-rate-offers' data-product-id='" . htmlspecialchars($product['id']) . "' data-product-code='" . htmlspecialchars($product['codigo']) . "'>Calificar Ofertas</button>";
        echo "<a href='" . url('admin/dashboard') . "' class='btn btn-secondary'>Volver al Dashboard</a>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "<div class='error'>Producto no encontrado.</div>";
        echo "<a href='" . url('admin/dashboard') . "' class='btn btn-secondary'>Volver al Dashboard</a>";
    }
    ?>
</div>
