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
<h2>Detalles del Producto: <?php echo htmlspecialchars($product['codigo']); ?></h2>

<p>Entidad: <?php echo htmlspecialchars($product['entidad']); ?></p>
<p>Objeto del Proceso: <?php echo htmlspecialchars($product['objeto_proceso']); ?></p>
<p>Estado Actual: <?php echo htmlspecialchars($product['estado_descripcion']); ?></p>

<h3>Actualizar Estado</h3>
<form id="change-status-form" action="<?php echo url('moderator/manage-product/' . $product['id']); ?>" method="POST">
    <input type="hidden" name="action" value="change_status">
    <select name="estado_id" required>
        <?php foreach ($estados as $estado): ?>
            <option value="<?php echo $estado['id']; ?>" <?php echo $estado['id'] == $product['estado_id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($estado['descripcion']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn">Actualizar Estado</button>
</form>

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