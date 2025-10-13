<h2>Buscar Proceso</h2>

<form action="/subs/participant/search-process" method="POST">
    <div class="form-group">
        <label for="codigo">Código del Proceso:</label>
        <input type="text" id="codigo" name="codigo" required>
    </div>
    <button type="submit" class="btn">Buscar</button>
</form>

<?php if (isset($_SESSION['error_message'])): ?>
    <p class="error-message"><?php echo $_SESSION['error_message']; ?></p>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success_message'])): ?>
    <p class="success-message"><?php echo $_SESSION['success_message']; ?></p>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>