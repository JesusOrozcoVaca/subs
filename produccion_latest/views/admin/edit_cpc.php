<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>
<h2>Editar CPC</h2>
<form action="<?php echo url('admin/edit-cpc/' . $cpc['id']); ?>" method="POST">
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