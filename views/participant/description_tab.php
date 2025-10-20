<h3>Descripción del Proceso</h3>
<p><strong>Entidad:</strong> <?php echo htmlspecialchars($product['entidad']); ?></p>
<p><strong>Objeto del Proceso:</strong> <?php echo htmlspecialchars($product['objeto_proceso']); ?></p>
<p><strong>CPC:</strong> <?php echo htmlspecialchars($product['cpc_descripcion']); ?></p>
<p><strong>Código:</strong> <?php echo htmlspecialchars($product['codigo']); ?></p>
<p><strong>Tipo de compra:</strong> <?php echo htmlspecialchars($product['tipo_compra']); ?></p>
<p><strong>Presupuesto referencial:</strong> $<?php echo number_format($product['presupuesto_referencial'], 2); ?></p>
<p><strong>Tipo de contratación:</strong> <?php echo htmlspecialchars($product['tipo_contratacion']); ?></p>
<p><strong>Estado de Invitación para Proveedor:</strong> <?php echo htmlspecialchars($userStatus); ?></p>
<p><strong>Observaciones:</strong> <?php echo htmlspecialchars($product['observaciones'] ?? 'Sin observaciones'); ?></p>