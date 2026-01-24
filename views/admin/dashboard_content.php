<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>
<?php
function buildDashboardUrl($baseUrl, $params) {
    $separator = (strpos($baseUrl, '?') === false) ? '?' : '&';
    return $baseUrl . $separator . http_build_query($params);
}

$baseDashboardUrl = url('admin/dashboard');
$usersPage = $usersPagination['page'] ?? 1;
$usersTotalPages = $usersPagination['total_pages'] ?? 1;
$productsPage = $productsPagination['page'] ?? 1;
$productsTotalPages = $productsPagination['total_pages'] ?? 1;
$cpcsPage = $cpcsPagination['page'] ?? 1;
$cpcsTotalPages = $cpcsPagination['total_pages'] ?? 1;

$userStates = [];
foreach ($users as $user) {
    $estado = $user['estado'] ?? 'Sin estado';
    $userStates[$estado] = true;
}
$userStateNames = array_keys($userStates);
sort($userStateNames, SORT_NATURAL | SORT_FLAG_CASE);

$estadoOptions = [];
foreach ($products as $product) {
    $estado = $product['estado_descripcion'] ?? 'Sin estado';
    $estadoOptions[$estado] = true;
}
$estadoNames = array_keys($estadoOptions);
sort($estadoNames, SORT_NATURAL | SORT_FLAG_CASE);
?>
<section id="usuarios">
    <h2>Usuarios del Sistema</h2>
    <div class="table-filter">
        <label for="admin-user-search">Buscar por Cédula:</label>
        <input type="search" id="admin-user-search" placeholder="Escribe parte de la cédula..." autocomplete="off">
        <label for="admin-user-status-filter">Estado:</label>
        <select id="admin-user-status-filter">
            <option value="">Todos</option>
            <?php foreach ($userStateNames as $estado): ?>
                <option value="<?php echo htmlspecialchars($estado); ?>"><?php echo htmlspecialchars($estado); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cédula</th>
                <th>Nombre Completo</th>
                <th>Correo Electrónico</th>
                <th>Nivel de Acceso</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['id']); ?></td>
                <td><?php echo htmlspecialchars($user['cedula']); ?></td>
                <td><?php echo htmlspecialchars($user['nombre_completo']); ?></td>
                <td><?php echo htmlspecialchars($user['correo_electronico']); ?></td>
                <td><?php echo htmlspecialchars($user['nivel_acceso']); ?></td>
                <td><?php echo htmlspecialchars($user['estado']); ?></td>
                <td>
                    <a href="<?php echo url('admin/edit-user/' . $user['id']); ?>" class="btn btn-small btn-edit">Editar</a>
                    <?php if ($user['estado'] === 'activo'): ?>
                        <button class="btn btn-small btn-danger btn-deactivate" data-user-id="<?php echo htmlspecialchars($user['id']); ?>">Desactivar</button>
                    <?php else: ?>
                        <button class="btn btn-small btn-success btn-activate" data-user-id="<?php echo htmlspecialchars($user['id']); ?>">Activar</button>
                    <?php endif; ?>
                    <button class="btn btn-small btn-danger btn-delete" data-type="user" data-id="<?php echo htmlspecialchars($user['id']); ?>">Eliminar</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if ($usersTotalPages > 1): ?>
        <div class="pagination">
            <?php if ($usersPage > 1): ?>
                <a class="pagination-link" href="<?php echo htmlspecialchars(buildDashboardUrl($baseDashboardUrl, [
                    'users_page' => $usersPage - 1,
                    'products_page' => $productsPage,
                    'cpcs_page' => $cpcsPage
                ])); ?>">Anterior</a>
            <?php endif; ?>
            <span class="pagination-info">Página <?php echo $usersPage; ?> de <?php echo $usersTotalPages; ?></span>
            <?php if ($usersPage < $usersTotalPages): ?>
                <a class="pagination-link" href="<?php echo htmlspecialchars(buildDashboardUrl($baseDashboardUrl, [
                    'users_page' => $usersPage + 1,
                    'products_page' => $productsPage,
                    'cpcs_page' => $cpcsPage
                ])); ?>">Siguiente</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('admin-user-search');
    const statusSelect = document.getElementById('admin-user-status-filter');
    const table = document.querySelector('#usuarios table');
    if (!searchInput || !statusSelect || !table) {
        return;
    }

    const rows = Array.from(table.querySelectorAll('tbody tr')).filter(row => !row.classList.contains('no-results-row'));

    const applyFilters = () => {
        const needle = searchInput.value.trim().toLowerCase();
        const estadoSeleccionado = statusSelect.value.trim();
        let visibleCount = 0;
        rows.forEach(row => {
            const cedulaCell = row.cells[1];
            const estadoCell = row.cells[5];
            if (!cedulaCell || !estadoCell) {
                return;
            }
            const textoCedula = cedulaCell.textContent.toLowerCase();
            const textoEstado = estadoCell.textContent.trim();
            const coincideCedula = needle === '' || textoCedula.includes(needle);
            const coincideEstado = estadoSeleccionado === '' || textoEstado === estadoSeleccionado;
            const visible = coincideCedula && coincideEstado;
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
                tr.innerHTML = '<td colspan="7" style="text-align:center; padding: 16px;">No se encontraron usuarios que coincidan.</td>';
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

<section id="productos">
    <h2>Productos</h2>
    <div class="table-filter">
        <label for="admin-product-search">Buscar por Objeto del Proceso:</label>
        <input type="search" id="admin-product-search" placeholder="Escribe parte del objeto del proceso..." autocomplete="off">
        <label for="admin-process-status-filter">Estado del Proceso:</label>
        <select id="admin-process-status-filter">
            <option value="">Todos</option>
            <?php foreach ($estadoNames as $estado): ?>
                <option value="<?php echo htmlspecialchars($estado); ?>"><?php echo htmlspecialchars($estado); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Entidad</th>
                <th>Objeto del Proceso</th>
                <th>Código</th>
                <th>Tipo de Compra</th>
                <th>Estado del Proceso</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
            <tr>
                <td><?php echo htmlspecialchars($product['id']); ?></td>
                <td><?php echo htmlspecialchars($product['entidad']); ?></td>
                <td><?php echo htmlspecialchars($product['objeto_proceso']); ?></td>
                <td><?php echo htmlspecialchars($product['codigo']); ?></td>
                <td><?php echo htmlspecialchars($product['tipo_compra']); ?></td>
                <td><?php echo htmlspecialchars($product['estado_descripcion']); ?></td>
                <td>
                    <a href="<?php echo url('admin/manage-product/' . $product['id']); ?>" class="btn btn-small btn-manage">Gestionar</a>
                    <button class="btn btn-small btn-danger btn-delete" data-type="product" data-id="<?php echo htmlspecialchars($product['id']); ?>">Eliminar</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if ($productsTotalPages > 1): ?>
        <div class="pagination">
            <?php if ($productsPage > 1): ?>
                <a class="pagination-link" href="<?php echo htmlspecialchars(buildDashboardUrl($baseDashboardUrl, [
                    'users_page' => $usersPage,
                    'products_page' => $productsPage - 1,
                    'cpcs_page' => $cpcsPage
                ])); ?>">Anterior</a>
            <?php endif; ?>
            <span class="pagination-info">Página <?php echo $productsPage; ?> de <?php echo $productsTotalPages; ?></span>
            <?php if ($productsPage < $productsTotalPages): ?>
                <a class="pagination-link" href="<?php echo htmlspecialchars(buildDashboardUrl($baseDashboardUrl, [
                    'users_page' => $usersPage,
                    'products_page' => $productsPage + 1,
                    'cpcs_page' => $cpcsPage
                ])); ?>">Siguiente</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('admin-product-search');
    const statusSelect = document.getElementById('admin-process-status-filter');
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
            const estadoCell = row.cells[5];
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
                tr.innerHTML = '<td colspan="7" style="text-align:center; padding: 16px;">No se encontraron procesos que coincidan.</td>';
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

<section id="cpcs">
    <h2>CPCs</h2>
    <div class="table-filter">
        <label for="admin-cpc-search">Buscar por Descripción:</label>
        <input type="search" id="admin-cpc-search" placeholder="Escribe parte de la descripción..." autocomplete="off">
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Código</th>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cpcs as $cpc): ?>
            <tr>
                <td><?php echo htmlspecialchars($cpc['id']); ?></td>
                <td><?php echo htmlspecialchars($cpc['codigo']); ?></td>
                <td><?php echo htmlspecialchars($cpc['descripcion']); ?></td>
                <td>
                    <a href="<?php echo url('admin/edit-cpc/' . $cpc['id']); ?>" class="btn btn-small btn-edit">Editar</a>
                    <button class="btn btn-small btn-danger btn-delete" data-type="cpc" data-id="<?php echo htmlspecialchars($cpc['id']); ?>">Eliminar</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if ($cpcsTotalPages > 1): ?>
        <div class="pagination">
            <?php if ($cpcsPage > 1): ?>
                <a class="pagination-link" href="<?php echo htmlspecialchars(buildDashboardUrl($baseDashboardUrl, [
                    'users_page' => $usersPage,
                    'products_page' => $productsPage,
                    'cpcs_page' => $cpcsPage - 1
                ])); ?>">Anterior</a>
            <?php endif; ?>
            <span class="pagination-info">Página <?php echo $cpcsPage; ?> de <?php echo $cpcsTotalPages; ?></span>
            <?php if ($cpcsPage < $cpcsTotalPages): ?>
                <a class="pagination-link" href="<?php echo htmlspecialchars(buildDashboardUrl($baseDashboardUrl, [
                    'users_page' => $usersPage,
                    'products_page' => $productsPage,
                    'cpcs_page' => $cpcsPage + 1
                ])); ?>">Siguiente</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('admin-cpc-search');
    const table = document.querySelector('#cpcs table');
    if (!searchInput || !table) {
        return;
    }

    const rows = Array.from(table.querySelectorAll('tbody tr'));
    searchInput.addEventListener('input', function() {
        const needle = this.value.trim().toLowerCase();
        let visibleCount = 0;
        rows.forEach(row => {
            const descriptionCell = row.cells[2];
            if (!descriptionCell) {
                return;
            }
            const text = descriptionCell.textContent.toLowerCase();
            const visible = needle === '' || text.includes(needle);
            row.style.display = visible ? '' : 'none';
            if (visible) {
                visibleCount++;
            }
        });

        const noResults = table.querySelector('.no-results-row');
        if (needle !== '' && visibleCount === 0) {
            if (!noResults) {
                const tr = document.createElement('tr');
                tr.className = 'no-results-row';
                tr.innerHTML = '<td colspan="4" style="text-align:center; padding: 16px;">No se encontraron CPCs que coincidan.</td>';
                table.tBodies[0].appendChild(tr);
            }
        } else if (noResults) {
            noResults.remove();
        }
    });
});
</script>