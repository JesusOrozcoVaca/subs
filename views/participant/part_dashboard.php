<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>
<?php
$estadoOptions = [];
foreach ($products as $product) {
    $estado = $product['estado_descripcion'] ?? 'Sin estado';
    $estadoOptions[$estado] = true;
}
$estadoNames = array_keys($estadoOptions);
sort($estadoNames, SORT_NATURAL | SORT_FLAG_CASE);
?>
<div id="dynamic-content">
    <section id="participant-products">
        <h2>Mis Productos</h2>
        <div class="table-filter">
            <label for="participant-product-search">Buscar por Objeto del Proceso:</label>
            <input type="search" id="participant-product-search" placeholder="Escribe parte del objeto del proceso..." autocomplete="off">
            <label for="participant-status-filter">Estado:</label>
            <select id="participant-status-filter">
                <option value="">Todos</option>
                <?php foreach ($estadoNames as $estado): ?>
                    <option value="<?php echo htmlspecialchars($estado); ?>"><?php echo htmlspecialchars($estado); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <table class="data-table">
        <thead>
            <tr>
                <th>Código</th>
                <th>CPC</th>
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
                <td><?php echo htmlspecialchars($product['cpc_descripcion'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($product['entidad']); ?></td>
                <td><?php echo htmlspecialchars($product['objeto_proceso']); ?></td>
                <td><?php echo htmlspecialchars($product['estado_descripcion']); ?></td>
                <td>
                    <a href="<?php echo url('participant/view-product/' . $product['id']); ?>" class="btn btn-small btn-view-details">Ver Detalles</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        </table>
    </section>
</div>