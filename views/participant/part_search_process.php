<div id="dynamic-content">
    <h2>Buscar Proceso</h2>
    <form id="search-process-form" action="/subs/participant/search-process" method="POST" class="ajax-form">
        <div class="form-group">
            <label for="codigo">Código del Proceso:</label>
            <input type="text" id="codigo" name="codigo" required>
        </div>
        <button type="submit" class="btn">Buscar</button>
    </form>
    <div id="search-results">
        <?php
        if (isset($searchResult)) {
            include 'part_search_results.php';
        }
        ?>
    </div>
</div>