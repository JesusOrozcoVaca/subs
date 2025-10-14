<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>
<h2>Crear Nuevo CPC</h2>
<form action="<?php echo url('admin/create-cpc'); ?>" method="POST">
    <div class="form-group">
        <label for="codigo">Código CPC:</label>
        <input type="text" id="codigo" name="codigo" required maxlength="5" pattern="\d{5}" title="El código debe ser de 5 dígitos">
    </div>
    <div class="form-group">
        <label for="descripcion">Descripción:</label>
        <textarea id="descripcion" name="descripcion" required></textarea>
    </div>
    <button type="submit" class="btn">Crear CPC</button>
</form>