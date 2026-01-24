<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>
<?php 
// Debug: verificar que la variable products esté definida
if (!isset($products)) {
    error_log("ERROR: Variable \$products not defined in mod_dashboard_content.php");
    echo "<p>Error: No se pudieron cargar los productos</p>";
    return;
}
error_log("Products count in view: " . count($products));

$estadoOptions = [];
foreach ($products as $product) {
    $estado = $product['estado_descripcion'] ?? 'Sin estado';
    $estadoOptions[$estado] = true;
}
$estadoNames = array_keys($estadoOptions);
sort($estadoNames, SORT_NATURAL | SORT_FLAG_CASE);
?>
<section id="productos">
    <h2>Productos Activos</h2>
    <div class="table-filter">
        <label for="product-search">Buscar por Objeto del Proceso:</label>
        <input type="search" id="product-search" placeholder="Escribe parte del objeto del proceso..." autocomplete="off">
        <label for="process-status-filter">Estado del Proceso:</label>
        <select id="process-status-filter">
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
                    <button class="btn btn-small btn-answer-questions" data-product-id="<?php echo $product['id']; ?>" data-product-code="<?php echo htmlspecialchars($product['codigo']); ?>">Responder Preguntas</button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('product-search');
    const statusSelect = document.getElementById('process-status-filter');
    const table = document.querySelector('#productos table');
    if (!searchInput || !statusSelect || !table) {
        return;
    }

    const rows = Array.from(table.querySelectorAll('tbody tr')).filter(row => !row.classList.contains('no-results-row'));

    const applyFilters = () => {
        const needle = searchInput.value.trim().toLowerCase();
        const estadoSeleccionado = statusSelect.value.trim();
        let visibleCount = 0;
        rows.forEach(row => {
            const objetoCell = row.cells[2];
            const estadoCell = row.cells[3];
            if (!objetoCell || !estadoCell) {
                return;
            }
            const textoObjeto = objetoCell.textContent.toLowerCase();
            const textoEstado = estadoCell.textContent.trim();
            const coincideObjeto = needle === '' || textoObjeto.includes(needle);
            const coincideEstado = estadoSeleccionado === '' || textoEstado === estadoSeleccionado;
            const visible = coincideObjeto && coincideEstado;
            row.style.display = visible ? '' : 'none';
            if (visible) {
                visibleCount++;
            }
        });

        const noResults = table.querySelector('.no-results-row');
        if ((needle !== '' || estadoSeleccionado !== '') && visibleCount === 0) {
            if (!noResults) {
                const tr = document.createElement('tr');
                tr.className = 'no-results-row';
                tr.innerHTML = '<td colspan="5" style="text-align:center; padding: 16px;">No se encontraron procesos que coincidan.</td>';
                table.tBodies[0].appendChild(tr);
            }
        } else if (noResults) {
            noResults.remove();
        }
    };

    searchInput.addEventListener('input', applyFilters);
    statusSelect.addEventListener('change', applyFilters);
});
</script>