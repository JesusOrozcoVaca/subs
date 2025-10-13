<?php if ($searchResult): ?>
<h3>Resultado de la búsqueda:</h3>
<table class="data-table">
    <thead>
        <tr>
            <th>Código</th>
            <th>Entidad</th>
            <th>Objeto del Proceso</th>
            <th>Estado</th>
            <th>Acción</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><?php echo htmlspecialchars($searchResult['codigo']); ?></td>
            <td><?php echo htmlspecialchars($searchResult['entidad']); ?></td>
            <td><?php echo htmlspecialchars($searchResult['objeto_proceso']); ?></td>
            <td><?php echo htmlspecialchars($searchResult['estado_proceso']); ?></td>
            <td>
                <a href="<?php echo url('participant/view-product/' . $searchResult['id']); ?>" class="btn btn-small">Ver Detalles</a>
            </td>
        </tr>
    </tbody>
</table>
<?php else: ?>
<p>No se encontraron resultados.</p>
<?php endif; ?>