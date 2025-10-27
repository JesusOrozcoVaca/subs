<!-- En el archivo /views/participant/tabs/product_tab.php -->

<h3>Detalle del producto</h3>
<table class="data-table">
    <tr>
        <th>CPC</th>
        <th>Código</th>
        <th>Objeto del proceso</th>
        <th>Valor referencial</th>
    </tr>
    <tr>
        <td><?php echo htmlspecialchars($product['cpc_codigo']); ?></td>
        <td><?php echo htmlspecialchars($product['codigo']); ?></td>
        <td><?php echo htmlspecialchars($product['objeto_proceso']); ?></td>
        <td>$<?php echo number_format($product['presupuesto_referencial'], 2); ?></td>
    </tr>
</table>