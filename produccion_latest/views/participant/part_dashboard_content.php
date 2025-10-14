<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>
<h2>Mis Productos</h2>
<table class="data-table">
    <thead>
        <tr>
            <th>Código</th>
            <th>Entidad</th>
            <th>Objeto del Proceso</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($products as $product): ?>
        <tr>
            <td><?php echo htmlspecialchars($product['codigo']); ?></td>
            <td><?php echo htmlspecialchars($product['entidad']); ?></td>
            <td><?php echo htmlspecialchars($product['objeto_proceso']); ?></td>
            <td><?php echo htmlspecialchars($product['estado_proceso']); ?></td>
            <td>
                <a href="<?php echo url('participant/view-product/' . $product['id']); ?>" class="btn btn-small">Ver Detalles</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>