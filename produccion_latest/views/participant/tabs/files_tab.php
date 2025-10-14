<!-- En el archivo /views/participant/tabs/files_tab.php -->

<h3>Documentos Anexos del Proceso</h3>
<?php if (!empty($documents)): ?>
    <ul>
    <?php foreach ($documents as $document): ?>
        <li><a href="<?php echo htmlspecialchars($document['ruta_archivo']); ?>" target="_blank"><?php echo htmlspecialchars($document['nombre_archivo']); ?></a></li>
    <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>No hay documentos asociados a este proceso.</p>
<?php endif; ?>