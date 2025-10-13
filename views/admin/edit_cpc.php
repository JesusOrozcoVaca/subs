<h2>Editar CPC</h2>
<form action="<?= BASE_URL ?>admin/edit-cpc/<?php echo $cpc['id']; ?>" method="POST">
    <div class="form-group">
        <label for="codigo">Código CPC:</label>
        <input type="text" id="codigo" name="codigo" value="<?php echo htmlspecialchars($cpc['codigo']); ?>" required maxlength="5" pattern="\d{5}" title="El código debe ser de 5 dígitos">
    </div>
    <div class="form-group">
        <label for="descripcion">Descripción:</label>
        <textarea id="descripcion" name="descripcion" required><?php echo htmlspecialchars($cpc['descripcion']); ?></textarea>
    </div>
    <button type="submit" class="btn">Actualizar CPC</button>
</form>