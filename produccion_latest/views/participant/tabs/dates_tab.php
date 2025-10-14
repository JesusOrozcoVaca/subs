<!-- En el archivo /views/participant/tabs/dates_tab.php -->

<h3>Fechas del Proceso</h3>
<ul>
<?php foreach ($dates as $key => $date): ?>
    <li><strong><?php echo $key; ?>:</strong> <?php echo $date->format('d/m/Y H:i:s'); ?></li>
<?php endforeach; ?>
</ul>