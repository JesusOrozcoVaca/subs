<?php
$blockForNoOffer = isset($blockWithoutOffer) ? (bool)$blockWithoutOffer : false;
if ($blockForNoOffer) {
    echo '<div class="read-only-message"><p>Usted no cargo oferta para este proceso por lo tanto, no puede participar en el mismo.</p></div>';
    return;
}
?>
<h2>Resumen de Puja</h2>
<div id="resumen-puja-container">
    <div id="ganador-puja">
        <!-- Aquí se mostrará el ganador de la puja -->
    </div>
    <div id="tabla-resultados">
        <!-- Aquí se mostrará una tabla con los resultados de la puja -->
    </div>
    <div id="grafico-pujas">
        <!-- Aquí se podría incluir un gráfico de las pujas realizadas -->
    </div>
</div>