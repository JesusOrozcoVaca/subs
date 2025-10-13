<h2>Editar Producto</h2>
<form action="/subs/admin/edit-product/<?php echo $product['id']; ?>" method="POST">
    <div class="form-group">
        <label for="entidad">Entidad:</label>
        <input type="text" id="entidad" name="entidad" value="<?php echo htmlspecialchars($product['entidad']); ?>" required>
    </div>
    <div class="form-group">
        <label for="objeto_proceso">Objeto del Proceso:</label>
        <textarea id="objeto_proceso" name="objeto_proceso" required><?php echo htmlspecialchars($product['objeto_proceso']); ?></textarea>
    </div>
    <div class="form-group">
        <label for="cpc_id">CPC:</label>
        <select id="cpc_id" name="cpc_id" required>
            <?php foreach ($cpcs as $cpc): ?>
                <option value="<?php echo $cpc['id']; ?>" <?php echo $cpc['id'] == $product['cpc_id'] ? 'selected' : ''; ?>>
                    <?php echo $cpc['codigo'] . ' - ' . $cpc['descripcion']; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="tipo_compra">Tipo de Compra:</label>
        <select id="tipo_compra" name="tipo_compra" required>
            <option value="SIE" <?php echo $product['tipo_compra'] == 'SIE' ? 'selected' : ''; ?>>Subasta Inversa Electrónica</option>
            <option value="MC" <?php echo $product['tipo_compra'] == 'MC' ? 'selected' : ''; ?>>Menor Cuantía</option>
            <option value="CCD" <?php echo $product['tipo_compra'] == 'CCD' ? 'selected' : ''; ?>>Consultoría por Contratación Directa</option>
            <option value="LIC" <?php echo $product['tipo_compra'] == 'LIC' ? 'selected' : ''; ?>>Licitación</option>
            <option value="FI" <?php echo $product['tipo_compra'] == 'FI' ? 'selected' : ''; ?>>Ferias Inclusivas</option>
        </select>
    </div>
    <div class="form-group">
        <label for="presupuesto_referencial">Presupuesto Referencial:</label>
        <input type="number" id="presupuesto_referencial" name="presupuesto_referencial" step="0.01" value="<?php echo htmlspecialchars($product['presupuesto_referencial']); ?>" required>
    </div>
    <div class="form-group">
        <label for="tipo_contratacion">Tipo de Contratación:</label>
        <select id="tipo_contratacion" name="tipo_contratacion">
            <option value="Total" <?php echo $product['tipo_contratacion'] == 'Total' ? 'selected' : ''; ?>>Total</option>
            <option value="Parcial" <?php echo $product['tipo_contratacion'] == 'Parcial' ? 'selected' : ''; ?>>Parcial</option>
        </select>
    </div>
    <div class="form-group">
        <label for="forma_pago">Forma de Pago:</label>
        <select id="forma_pago" name="forma_pago" required>
            <option value="0" <?php echo $product['forma_pago'] == '0' ? 'selected' : ''; ?>>0%</option>
            <option value="25" <?php echo $product['forma_pago'] == '25' ? 'selected' : ''; ?>>25%</option>
            <option value="50" <?php echo $product['forma_pago'] == '50' ? 'selected' : ''; ?>>50%</option>
            <option value="70" <?php echo $product['forma_pago'] == '70' ? 'selected' : ''; ?>>70%</option>
        </select>
    </div>
    <div class="form-group">
        <label for="plazo_entrega">Plazo de Entrega (días):</label>
        <input type="number" id="plazo_entrega" name="plazo_entrega" value="<?php echo htmlspecialchars($product['plazo_entrega']); ?>" required>
    </div>
    <div class="form-group">
        <label for="vigencia_oferta">Vigencia de Oferta (días):</label>
        <input type="number" id="vigencia_oferta" name="vigencia_oferta" value="<?php echo htmlspecialchars($product['vigencia_oferta']); ?>" required>
    </div>
    <div class="form-group">
        <label for="funcionario_encargado">Funcionario Encargado:</label>
        <input type="text" id="funcionario_encargado" name="funcionario_encargado" value="<?php echo htmlspecialchars($product['funcionario_encargado']); ?>" required>
    </div>
    <div class="form-group">
        <label for="descripcion">Descripción:</label>
        <textarea id="descripcion" name="descripcion" required><?php echo htmlspecialchars($product['descripcion']); ?></textarea>
    </div>
    <div class="form-group">
        <label for="variacion_minima">Variación Mínima de la Oferta (%):</label>
        <input type="number" id="variacion_minima" name="variacion_minima" step="0.01" value="<?php echo htmlspecialchars($product['variacion_minima']); ?>" required>
    </div>
    <button type="submit" class="btn">Actualizar Producto</button>
</form>