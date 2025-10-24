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
?>

<div class="preguntas-respuestas">
    <h3>Preguntas y Respuestas</h3>
    
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