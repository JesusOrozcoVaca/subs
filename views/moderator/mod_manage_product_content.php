<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>
<?php 
// Debug: verificar que las variables estén definidas
if (!isset($product)) {
    error_log("ERROR: Variable \$product not defined in mod_manage_product_content.php");
    echo "<p>Error: Producto no encontrado</p>";
    return;
}
if (!isset($estados)) {
    error_log("ERROR: Variable \$estados not defined in mod_manage_product_content.php");
    echo "<p>Error: Estados no encontrados</p>";
    return;
}
?>
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
        
        // Formulario para cambiar estado
        echo "<div class='change-status-section'>";
        echo "<h4>Cambiar Estado del Proceso</h4>";
        echo "<form id='change-status-form' action='" . url('moderator/manage-product/' . $product['id']) . "' method='POST'>";
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
        echo "<a href='" . url('moderator/edit-product/' . $product['id']) . "' class='btn btn-edit'>Editar Producto</a>";
        echo "<button class='btn btn-info btn-answer-questions' data-product-id='" . htmlspecialchars($product['id']) . "' data-product-code='" . htmlspecialchars($product['codigo']) . "'>Responder Preguntas</button>";
        echo "<a href='" . url('moderator/dashboard') . "' class='btn btn-secondary'>Volver al Dashboard</a>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "<div class='error'>Producto no encontrado.</div>";
        echo "<a href='" . url('moderator/dashboard') . "' class='btn btn-secondary'>Volver al Dashboard</a>";
    }
    ?>
</div>

<script>
console.log('Product management content loaded');
console.log('Form found:', document.querySelector('form'));

// Interceptar el formulario directamente
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('change-status-form');
    if (form) {
        console.log('Form found and setting up listener');
        form.addEventListener('submit', function(e) {
            console.log('Form submit intercepted!');
            e.preventDefault();
            e.stopPropagation();
            
            const formData = new FormData(this);
            const formAction = this.getAttribute('action');
            console.log('Form action URL:', formAction);
            console.log('Form data:', Array.from(formData.entries()));
            
            // Mostrar mensaje de confirmación
            if (confirm('¿Está seguro de que desea cambiar el estado del producto?')) {
                // Enviar petición AJAX
                fetch(formAction, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success) {
                        alert(data.message);
                        // Redirigir al dashboard del moderador
                        console.log('Redirecting to moderator dashboard...');
                        window.location.href = '<?php echo url('moderator/dashboard'); ?>';
                    } else {
                        alert(data.message || 'Error al procesar la solicitud');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud: ' + error.message);
                });
            }
        });
    } else {
        console.log('Form not found!');
    }
});
</script>

<h3>Participantes</h3>
<table class="data-table">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($participants as $participant): ?>
        <tr>
            <td><?php echo htmlspecialchars($participant['nombre_completo']); ?></td>
            <td><?php echo htmlspecialchars($participant['estado']); ?></td>
            <td>
                <form action="<?php echo url('moderator/manage-product/' . $product['id']); ?>" method="POST">
                    <input type="hidden" name="action" value="evaluate_participant">
                    <input type="hidden" name="participant_id" value="<?php echo $participant['id']; ?>">
                    <select name="status">
                        <option value="Cumple" <?php echo $participant['estado'] == 'Cumple' ? 'selected' : ''; ?>>Cumple</option>
                        <option value="No Cumple" <?php echo $participant['estado'] == 'No Cumple' ? 'selected' : ''; ?>>No Cumple</option>
                    </select>
                    <button type="submit" class="btn btn-small">Actualizar</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>