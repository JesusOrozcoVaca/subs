<h2>Detalles del Producto: <?php echo htmlspecialchars($product['codigo']); ?></h2>

<div class="product-details">
    <p><strong>Entidad:</strong> <?php echo htmlspecialchars($product['entidad']); ?></p>
    <p><strong>Objeto del Proceso:</strong> <?php echo htmlspecialchars($product['objeto_proceso']); ?></p>
    <p><strong>CPC:</strong> <?php echo htmlspecialchars($product['cpc_id']); ?></p>
    <p><strong>Código:</strong> <?php echo htmlspecialchars($product['codigo']); ?></p>
    <p><strong>Tipo de compra:</strong> <?php echo htmlspecialchars($product['tipo_compra']); ?></p>
    <p><strong>Presupuesto referencial:</strong> $<?php echo number_format($product['presupuesto_referencial'], 2); ?></p>
    <p><strong>Tipo de contratación:</strong> <?php echo htmlspecialchars($product['tipo_contratacion']); ?></p>
    <p><strong>Estado del proceso:</strong> <?php echo htmlspecialchars($product['estado_proceso']); ?></p>
    <p><strong>Estado de Invitación para Proveedor:</strong> <?php echo htmlspecialchars($userStatus); ?></p>
</div>

<h3>Fechas del Proceso</h3>
<ul>
<?php foreach ($dates as $key => $date): ?>
    <li><strong><?php echo $key; ?>:</strong> <?php echo $date->format('d/m/Y H:i:s'); ?></li>
<?php endforeach; ?>
</ul>

<?php if ($product['estado_proceso'] === 'Preguntas y Respuestas'): ?>
<h3>Hacer Pregunta</h3>
<form action="<?= BASE_URL ?>participant/view-product/<?php echo $product['id']; ?>" method="POST">
    <input type="hidden" name="action" value="ask_question">
    <textarea name="question" required></textarea>
    <button type="submit" class="btn">Enviar Pregunta</button>
</form>
<?php endif; ?>

<?php if ($product['estado_proceso'] === 'Oferta Inicial' && $userStatus === 'Cumple'): ?>
<h3>Enviar Oferta Inicial</h3>
<form action="<?= BASE_URL ?>participant/view-product/<?php echo $product['id']; ?>" method="POST">
    <input type="hidden" name="action" value="submit_offer">
    <input type="number" name="offer" step="0.01" required>
    <button type="submit" class="btn">Enviar Oferta</button>
</form>
<?php endif; ?>

<?php if ($product['estado_proceso'] === 'Puja' && $userStatus === 'Cumple'): ?>
<h3>Enviar Puja</h3>
<form action="<?= BASE_URL ?>participant/view-product/<?php echo $product['id']; ?>" method="POST">
    <input type="hidden" name="action" value="submit_bid">
    <input type="number" name="bid" step="0.01" required>
    <button type="submit" class="btn">Enviar Puja</button>
</form>
<?php endif; ?>

<h3>Preguntas y Respuestas</h3>
<?php if (!empty($questions)): ?>
    <?php foreach ($questions as $question): ?>
        <div class="question">
            <p><strong>Pregunta:</strong> <?php echo htmlspecialchars($question['pregunta']); ?></p>
            <?php if (!empty($question['respuesta'])): ?>
                <p><strong>Respuesta:</strong> <?php echo htmlspecialchars($question['respuesta']); ?></p>
            <?php else: ?>
                <p><em>Aún sin respuesta</em></p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No hay preguntas para este producto.</p>
<?php endif; ?>

<?php if (!empty($bids)): ?>
<h3>Historial de Pujas</h3>
<table class="data-table">
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Valor</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($bids as $bid): ?>
        <tr>
            <td><?php echo $bid['fecha_puja']; ?></td>
            <td>$<?php echo number_format($bid['valor'], 2); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>