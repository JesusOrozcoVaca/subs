<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>
<section id="usuarios">
    <h2>Usuarios del Sistema</h2>
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
</section>

<section id="productos">
    <h2>Productos</h2>
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
</section>

<section id="cpcs">
    <h2>CPCs</h2>
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
</section>