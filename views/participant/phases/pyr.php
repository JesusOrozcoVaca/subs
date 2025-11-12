<?php
// Verificar que el producto esté disponible
if (!isset($product) || !$product) {
    echo '<div class="error">Producto no encontrado</div>';
    return;
}

// Obtener el estado actual del producto
$currentStateCode = $product['estado_id'] ?? 1;
$isReadOnly = false;

// Verificar si la fase está en modo solo lectura
if ($currentStateCode != 1) { // Si no es "Preguntas y Respuestas"
    $isReadOnly = true;
}

$actaRelativePath = 'uploads/pyr_actas/acta_pyr_producto_' . (int)$product['id'] . '.pdf';
$actaFullPath = BASE_PATH . '/' . $actaRelativePath;
$actaExists = file_exists($actaFullPath);
$actaUrl = $actaExists ? BASE_URL . 'index.php?action=view_file&path=' . urlencode($actaRelativePath) : '';
?>

<div class="preguntas-respuestas">
    <div class="pyr-header">
        <h3>Preguntas y Respuestas</h3>
        <?php if ($actaExists): ?>
            <a href="<?php echo $actaUrl; ?>" class="btn-acta-pyr" target="_blank" rel="noopener">
                Descargar acta PyR
            </a>
        <?php endif; ?>
    </div>
    
    <div class="pyr-layout">
        <!-- Lista de preguntas realizadas (izquierda) -->
        <div class="preguntas-list">
            <div id="preguntas-container">
                Cargando preguntas...
            </div>
            <div id="pagination-container" class="pagination-container">
                <!-- Pagination will be loaded here -->
            </div>
        </div>
        
        <!-- Formulario para nueva pregunta (derecha) -->
        <?php if (!$isReadOnly): ?>
        <div class="nueva-pregunta-section">
            <h4>Hacer una Nueva Pregunta</h4>
            <form id="pregunta-form" class="pregunta-form">
                <div class="form-group">
                    <label for="pregunta">Escriba su pregunta:</label>
                    <textarea id="pregunta" name="pregunta" maxlength="500" placeholder="Escriba su pregunta aquí (máximo 500 caracteres)" required></textarea>
                    <div class="char-counter">
                        <span id="char-count">0</span>/500 caracteres
                    </div>
                </div>
                <button type="submit" class="btn-submit">Enviar Pregunta</button>
            </form>
        </div>
        <?php else: ?>
        <div class="read-only-message">
            <p>Esta fase ha terminado. No puede enviar nuevas preguntas.</p>
        </div>
        <?php endif; ?>
    </div>
</div>