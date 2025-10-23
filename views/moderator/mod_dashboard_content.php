<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>
<?php 
// Debug: verificar que la variable products esté definida
if (!isset($products)) {
    error_log("ERROR: Variable \$products not defined in mod_dashboard_content.php");
    echo "<p>Error: No se pudieron cargar los productos</p>";
    return;
}
error_log("Products count in view: " . count($products));
?>
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
            <?php if (empty($products)): ?>
            <tr>
                <td colspan="5" style="text-align: center; padding: 20px;">
                    <p>No hay productos disponibles</p>
                    <p>Total de productos en la base de datos: <?php echo count($products); ?></p>
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($products as $product): ?>
            <tr>
                <td><?php echo htmlspecialchars($product['codigo']); ?></td>
                <td><?php echo htmlspecialchars($product['entidad']); ?></td>
                <td><?php echo htmlspecialchars($product['objeto_proceso']); ?></td>
                <td><?php echo htmlspecialchars($product['estado_descripcion'] ?? 'Sin estado'); ?></td>
                <td>
                    <a href="<?php echo url('moderator/manage-product/' . $product['id']); ?>" class="btn btn-small btn-manage">Gestionar</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</section>