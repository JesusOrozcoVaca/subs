<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>
<section id="productos">
    <h2>Productos Activos</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Código</th>
                <th>Entidad</th>
                <th>Objeto del Proceso</th>
                <th>Estado del Proceso</th>
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
                    <a href="<?php echo url('moderator/manage-product/' . $product['id']); ?>" class="btn btn-small btn-manage">Gestionar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>