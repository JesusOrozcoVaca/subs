<h2>CPCs Existentes</h2>
<table class="data-table">
    <thead>
        <tr>
            <th>Código</th>
            <th>Descripción</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($cpcs as $cpc): ?>
        <tr>
            <td><?php echo htmlspecialchars($cpc['codigo']); ?></td>
            <td><?php echo htmlspecialchars($cpc['descripcion']); ?></td>
            <td>
                <a href="/subs/moderator/edit-cpc/<?php echo $cpc['id']; ?>" class="btn btn-small btn-edit">Editar</a>
                <form action="/subs/moderator/delete-cpc" method="POST" style="display:inline;">
                    <input type="hidden" name="id" value="<?php echo $cpc['id']; ?>">
                    <button class="btn btn-small btn-danger btn-delete" data-id="<?php echo $cpc['id']; ?>">Eliminar</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h3>Agregar Nuevo CPC</h3>
<form id="add-cpc-form" action="/subs/moderator/manage-cpcs" method="POST">
    <input type="hidden" name="action" value="add">
    <div class="form-group">
        <label for="codigo">Código:</label>
        <input type="text" id="codigo" name="codigo" required maxlength="5" pattern="\d{5}">
    </div>
    <div class="form-group">
        <label for="descripcion">Descripción:</label>
        <textarea id="descripcion" name="descripcion" required></textarea>
    </div>
    <button type="submit" class="btn">Agregar CPC</button>
</form>