<h2>Editar CPC</h2>
<form id="edit-cpc-form" action="/subs/moderator/edit-cpc/<?php echo $cpc['id']; ?>" method="POST">
    <div class="form-group">
        <label for="codigo">Código:</label>
        <input type="text" id="codigo" name="codigo" required maxlength="5" pattern="\d{5}" value="<?php echo htmlspecialchars($cpc['codigo']); ?>">
    </div>
    <div class="form-group">
        <label for="descripcion">Descripción:</label>
        <textarea id="descripcion" name="descripcion" required><?php echo htmlspecialchars($cpc['descripcion']); ?></textarea>
    </div>
    <input type="hidden" name="id" value="<?php echo $cpc['id']; ?>">
    <button type="submit" class="btn">Actualizar CPC</button>
</form>