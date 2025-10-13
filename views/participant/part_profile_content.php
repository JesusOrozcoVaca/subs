<h2>Información Personal</h2>
<form action="<?= BASE_URL ?>participant/profile" method="POST">
    <input type="hidden" name="action" value="update_profile">
    <div class="form-group">
        <label for="cedula">Cédula:</label>
        <input type="text" id="cedula" name="cedula" value="<?php echo htmlspecialchars($user['cedula']); ?>" readonly>
    </div>
    <div class="form-group">
        <label for="nombre_completo">Nombre Completo:</label>
        <input type="text" id="nombre_completo" name="nombre_completo" value="<?php echo htmlspecialchars($user['nombre_completo']); ?>" required>
    </div>
    <div class="form-group">
        <label for="correo_electronico">Correo Electrónico:</label>
        <input type="email" id="correo_electronico" name="correo_electronico" value="<?php echo htmlspecialchars($user['correo_electronico']); ?>" required>
    </div>
    <div class="form-group">
        <label for="telefono">Número de Teléfono:</label>
        <input type="tel" id="telefono" name="telefono" value="<?php echo htmlspecialchars($user['telefono']); ?>" required>
    </div>
    <button type="submit" class="btn">Actualizar Perfil</button>
</form>

<h2>Mis CPCs</h2>
<ul>
    <?php foreach ($userCPCs as $cpc): ?>
        <li>
            <?php echo htmlspecialchars($cpc['codigo'] . ' - ' . $cpc['descripcion']); ?>
            <form action="<?= BASE_URL ?>participant/profile" method="POST" style="display: inline;">
                <input type="hidden" name="action" value="remove_cpc">
                <input type="hidden" name="cpc_id" value="<?php echo $cpc['id']; ?>">
                <button type="submit" class="btn btn-small btn-danger">Eliminar</button>
            </form>
        </li>
    <?php endforeach; ?>
</ul>

<h3>Agregar CPC</h3>
<form action="<?= BASE_URL ?>participant/profile" method="POST">
    <input type="hidden" name="action" value="add_cpc">
    <select name="cpc_id" required>
        <?php foreach ($allCPCs as $cpc): ?>
            <?php if (!in_array($cpc, $userCPCs)): ?>
                <option value="<?php echo $cpc['id']; ?>"><?php echo htmlspecialchars($cpc['codigo'] . ' - ' . $cpc['descripcion']); ?></option>
            <?php endif; ?>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn">Agregar CPC</button>
</form>