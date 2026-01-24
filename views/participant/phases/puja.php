<?php
$blockForNoOffer = isset($blockWithoutOffer) ? (bool)$blockWithoutOffer : false;
if ($blockForNoOffer) {
    echo '<div class="read-only-message"><p>Usted no cargo oferta para este proceso por lo tanto, no puede participar en el mismo.</p></div>';
    return;
}
?>
<h2>Puja</h2>
<div id="puja-container">
    <div id="tiempo-restante">
        <!-- Aquí se mostrará el tiempo restante de la puja -->
    </div>
    <div id="mejor-oferta-actual">
        <!-- Aquí se mostrará la mejor oferta actual -->
    </div>
    <form id="puja-form">
        <input type="number" name="valor_puja" placeholder="Ingrese el valor de su puja" step="0.01">
        <button type="submit">Enviar Puja</button>
    </form>
    <div id="historial-pujas">
        <!-- Aquí se mostrará el historial de pujas -->
    </div>
</div>