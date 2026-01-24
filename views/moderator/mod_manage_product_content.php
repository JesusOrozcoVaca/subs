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
        echo "<p><strong>Presupuesto:</strong> $" . number_format((float)($product['presupuesto_referencial'] ?? 0), 2) . "</p>";
        echo "<p><strong>Variación mínima:</strong> " . htmlspecialchars($product['variacion_minima'] ?? '') . " %</p>";
        echo "<p><strong>Plazo de entrega:</strong> " . htmlspecialchars($product['plazo_entrega'] ?? '') . "</p>";
        echo "<p><strong>Vigencia de la Oferta:</strong> " . htmlspecialchars($product['vigencia_oferta'] ?? '') . "</p>";
        echo "<p><strong>Funcionario encargado:</strong> " . htmlspecialchars($product['funcionario_encargado'] ?? '') . "</p>";
        
        // Formulario para cambiar estado
        echo "<div class='change-status-section'>";
        echo "<h4>Cambiar Estado del Proceso</h4>";
        echo "<form id='change-status-form' action='" . url('moderator/manage-product/' . $product['id']) . "' method='POST'>";
        echo "<input type='hidden' name='action' value='change_status'>";
        echo "<select name='estado_id' required>";
        foreach ($estados as $estado) {
            $selected = ($estado['id'] == $product['estado_id']) ? 'selected' : '';
            $codigoEstado = htmlspecialchars($estado['codigo'] ?? '', ENT_QUOTES);
            echo "<option value='" . $estado['id'] . "' data-codigo='" . $codigoEstado . "' " . $selected . ">" . htmlspecialchars($estado['descripcion']) . "</option>";
        }
        echo "</select>";
        echo "<button type='submit' class='btn btn-primary'>Actualizar Estado</button>";
        echo "</form>";
        echo "</div>";
        
        echo "<div class='actions' style='display: flex; gap: 15px; flex-wrap: wrap; margin-top: 20px;'>";
        echo "<a href='" . url('moderator/edit-product/' . $product['id']) . "' class='btn btn-edit'>Editar Producto</a>";
        echo "<button class='btn btn-info btn-answer-questions' data-product-id='" . htmlspecialchars($product['id']) . "' data-product-code='" . htmlspecialchars($product['codigo']) . "'>Responder Preguntas</button>";
        echo "<button class='btn btn-info btn-rate-offers' data-product-id='" . htmlspecialchars($product['id']) . "' data-product-code='" . htmlspecialchars($product['codigo']) . "'>Calificar Ofertas</button>";
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
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('change-status-form');
    if (!form) {
        return;
    }

    const openPujaConfigModal = () => {
        return new Promise((resolve) => {
            const overlay = document.createElement('div');
            overlay.style.cssText = `
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
            `;

            const modal = document.createElement('div');
            modal.style.cssText = `
                background: #ffffff;
                width: min(520px, 92%);
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.25);
            `;

            const browserTimeZone = (Intl && Intl.DateTimeFormat)
                ? Intl.DateTimeFormat().resolvedOptions().timeZone
                : '';

            modal.innerHTML = `
                <h3 style="margin-top: 0;">Configurar Puja</h3>
                <label for="puja-duration">Duracion (minutos)</label>
                <select id="puja-duration" style="width: 100%; margin-bottom: 12px;">
                    <option value="5">5</option>
                    <option value="10" selected>10</option>
                    <option value="15">15</option>
                </select>
                <label for="puja-start">Hora de inicio</label>
                <input id="puja-start" type="datetime-local" style="width: 100%; margin-bottom: 12px;" required>
                <label for="puja-timezone">Zona horaria</label>
                <input id="puja-timezone" type="text" list="puja-timezone-list" placeholder="America/Guayaquil" style="width: 100%; margin-bottom: 8px;" required>
                <datalist id="puja-timezone-list">
                    <option value="America/Guayaquil"></option>
                    <option value="America/Bogota"></option>
                    <option value="America/Lima"></option>
                    <option value="America/Mexico_City"></option>
                    <option value="UTC"></option>
                </datalist>
                <div class="modal-error" style="display: none; color: #b00020; margin-top: 6px;"></div>
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px;">
                    <button type="button" class="btn btn-secondary" data-action="cancel">Cancelar</button>
                    <button type="button" class="btn btn-primary" data-action="confirm">Guardar</button>
                </div>
            `;

            overlay.appendChild(modal);
            document.body.appendChild(overlay);

            const errorEl = modal.querySelector('.modal-error');
            const durationEl = modal.querySelector('#puja-duration');
            const startEl = modal.querySelector('#puja-start');
            const tzEl = modal.querySelector('#puja-timezone');

            if (browserTimeZone) {
                tzEl.value = browserTimeZone;
            }

            const closeModal = (result) => {
                document.body.removeChild(overlay);
                resolve(result);
            };

            overlay.addEventListener('click', (event) => {
                if (event.target === overlay) {
                    closeModal(null);
                }
            });

            modal.addEventListener('click', (event) => {
                event.stopPropagation();
            });

            modal.querySelector('[data-action="cancel"]').addEventListener('click', () => {
                closeModal(null);
            });

            modal.querySelector('[data-action="confirm"]').addEventListener('click', () => {
                const durationValue = durationEl.value.trim();
                const startValue = startEl.value.trim();
                const tzValue = tzEl.value.trim();

                if (!durationValue || !startValue || !tzValue) {
                    errorEl.textContent = 'Debe completar todos los campos de la puja.';
                    errorEl.style.display = 'block';
                    return;
                }

                closeModal({
                    duration: durationValue,
                    start: startValue,
                    timezone: tzValue
                });
            });
        });
    };

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        e.stopPropagation();

        const formData = new FormData(this);
        const formAction = this.getAttribute('action');
        const estadoSelect = this.querySelector('select[name="estado_id"]');
        const selectedOption = estadoSelect ? estadoSelect.options[estadoSelect.selectedIndex] : null;
        const estadoCode = selectedOption ? selectedOption.dataset.codigo : '';

        if (estadoCode !== 'puja') {
            if (!confirm('¿Está seguro de que desea cambiar el estado del producto?')) {
                return;
            }
        } else {
            const pujaConfig = await openPujaConfigModal();
            if (!pujaConfig) {
                return;
            }
            formData.set('puja_duracion_minutos', pujaConfig.duration);
            formData.set('puja_hora_inicio', pujaConfig.start);
            formData.set('puja_zona_horaria', pujaConfig.timezone);
        }

        fetch(formAction, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.href = '<?php echo url('moderator/dashboard'); ?>';
            } else {
                alert(data.message || 'Error al procesar la solicitud');
            }
        })
        .catch(error => {
            alert('Error al procesar la solicitud: ' + error.message);
        });
    });
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