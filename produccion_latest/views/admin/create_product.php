<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>
<h2>Crear Nuevo Producto</h2>
<form action="<?php echo url('admin/create-product'); ?>" method="POST" id="createProductForm">
    <div class="form-group">
        <label for="entidad">Entidad:</label>
        <input type="text" id="entidad" name="entidad" required>
    </div>
    <div class="form-group">
        <label for="objeto_proceso">Objeto del Proceso:</label>
        <textarea id="objeto_proceso" name="objeto_proceso" required></textarea>
    </div>
    <div class="form-group">
        <label for="cpc_id">CPC:</label>
        <select id="cpc_id" name="cpc_id" required>
            <?php foreach ($cpcs as $cpc): ?>
                <option value="<?php echo $cpc['id']; ?>"><?php echo $cpc['codigo'] . ' - ' . $cpc['descripcion']; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="tipo_compra">Tipo de Compra:</label>
        <select id="tipo_compra" name="tipo_compra" required>
            <option value="SIE">Subasta Inversa Electrónica</option>
            <option value="MC">Menor Cuantía</option>
            <option value="CCD">Consultoría por Contratación Directa</option>
            <option value="LIC">Licitación</option>
            <option value="FI">Ferias Inclusivas</option>
        </select>
    </div>
    <div class="form-group">
        <label for="presupuesto_referencial">Presupuesto Referencial:</label>
        <input type="number" id="presupuesto_referencial" name="presupuesto_referencial" step="0.01" required>
    </div>
    <div class="form-group">
        <label for="tipo_contratacion">Tipo de Contratación:</label>
        <select id="tipo_contratacion" name="tipo_contratacion">
            <option value="Total">Total</option>
            <option value="Parcial">Parcial</option>
        </select>
    </div>
    <div class="form-group">
        <label for="forma_pago">Forma de Pago:</label>
        <select id="forma_pago" name="forma_pago" required>
            <option value="0">0%</option>
            <option value="25">25%</option>
            <option value="50">50%</option>
            <option value="70">70%</option>
        </select>
    </div>
    <div class="form-group">
        <label for="plazo_entrega">Plazo de Entrega (días):</label>
        <input type="number" id="plazo_entrega" name="plazo_entrega" required>
    </div>
    <div class="form-group">
        <label for="vigencia_oferta">Vigencia de Oferta (días):</label>
        <input type="number" id="vigencia_oferta" name="vigencia_oferta" required>
    </div>
    <div class="form-group">
        <label for="funcionario_encargado">Funcionario Encargado:</label>
        <input type="text" id="funcionario_encargado" name="funcionario_encargado" required>
    </div>
    <div class="form-group">
        <label for="descripcion">Descripción:</label>
        <textarea id="descripcion" name="descripcion" required></textarea>
    </div>
    <div class="form-group">
        <label for="variacion_minima">Variación Mínima de la Oferta (%):</label>
        <input type="number" id="variacion_minima" name="variacion_minima" step="0.01" required>
    </div>
    <button type="submit" class="btn">Crear Producto</button>
</form>