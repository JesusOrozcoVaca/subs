<h2>Editar Usuario</h2>
<form action="/subs/admin/edit-user/<?php echo $user['id']; ?>" method="POST">
    <div class="form-group">
        <label for="cedula">Cédula:</label>
        <input type="text" id="cedula" name="cedula" value="<?php echo htmlspecialchars($user['cedula']); ?>" required>
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
    <div class="form-group">
        <label for="nivel_acceso">Nivel de Acceso:</label>
        <select id="nivel_acceso" name="nivel_acceso" required>
            <option value="1" <?php echo $user['nivel_acceso'] == 1 ? 'selected' : ''; ?>>Administrador</option>
            <option value="2" <?php echo $user['nivel_acceso'] == 2 ? 'selected' : ''; ?>>Moderador</option>
            <option value="3" <?php echo $user['nivel_acceso'] == 3 ? 'selected' : ''; ?>>Participante</option>
        </select>
    </div>
    <button type="submit" class="btn">Actualizar Usuario</button>
</form>