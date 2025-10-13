<h2>Crear Nuevo Usuario</h2>
<form action="/subs/admin/create-user" method="POST">
    <div class="form-group">
        <label for="cedula">Cédula:</label>
        <input type="text" id="cedula" name="cedula" required>
    </div>
    <div class="form-group">
        <label for="nombre_completo">Nombre Completo:</label>
        <input type="text" id="nombre_completo" name="nombre_completo" required>
    </div>
    <div class="form-group">
        <label for="correo_electronico">Correo Electrónico:</label>
        <input type="email" id="correo_electronico" name="correo_electronico" required>
    </div>
    <div class="form-group">
        <label for="telefono">Número de Teléfono:</label>
        <input type="tel" id="telefono" name="telefono" required>
    </div>
    <div class="form-group">
        <label for="contrasena">Contraseña:</label>
        <input type="password" id="contrasena" name="contrasena" required>
    </div>
    <div class="form-group">
        <label for="nivel_acceso">Nivel de Acceso:</label>
        <select id="nivel_acceso" name="nivel_acceso" required>
            <option value="1">Administrador</option>
            <option value="2">Moderador</option>
            <option value="3">Participante</option>
        </select>
    </div>
    <button type="submit" class="btn">Crear Usuario</button>
</form>