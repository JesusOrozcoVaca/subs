<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>
<h2>Detalles del Producto: <?php echo htmlspecialchars($product['codigo']); ?></h2>

<p>Entidad: <?php echo htmlspecialchars($product['entidad']); ?></p>
<p>Objeto del Proceso: <?php echo htmlspecialchars($product['objeto_proceso']); ?></p>
<p>Estado Actual: <?php echo htmlspecialchars($product['estado_proceso']); ?></p>

<h3>Actualizar Estado</h3>
<form action="<?php echo url('moderator/manage-product/' . $product['id']); ?>" method="POST">
    <input type="hidden" name="action" value="update_status">
    <select name="new_status">
        <option value="Preguntas y Respuestas">Preguntas y Respuestas</option>
        <option value="Entrega de Ofertas">Entrega de Ofertas</option>
        <option value="Convalidación de errores">Convalidación de errores</option>
        <option value="Calificación">Calificación</option>
        <option value="Oferta Inicial">Oferta Inicial</option>
        <option value="Puja">Puja</option>
        <option value="Por adjudicar">Por adjudicar</option>
        <option value="Adjudicado – Registro de Contrato">Adjudicado – Registro de Contrato</option>
        <option value="En ejecución">En ejecución</option>
        <option value="En recepción">En recepción</option>
        <option value="Finalizado">Finalizado</option>
    </select>
    <button type="submit" class="btn">Actualizar Estado</button>
</form>

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