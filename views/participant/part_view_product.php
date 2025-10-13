<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Producto - Participante</title>
    <link rel="stylesheet" href="<?php echo css('styles.css'); ?>">
</head>
<body>
    <header class="main-header">
        <div class="logo-container">
            <img src="<?php echo image('logo-left.png'); ?>" alt="Logo Izquierdo" class="logo">
            <h1>Sistema de Simulación de Contratación Pública</h1>
            <img src="<?php echo image('logo-right.png'); ?>" alt="Logo Derecho" class="logo">
        </div>
        <div class="user-info">
            <span><?php echo date('Y-m-d H:i:s'); ?></span>
            <span><?php echo htmlspecialchars($_SESSION['nombre_completo']); ?></span>
            <a href="<?php echo url('participant/dashboard'); ?>" class="btn-return">Regresar</a>
        </div>
    </header>

    <nav class="top-nav">
        <ul>
            <li><a href="<?php echo url('participant/dashboard'); ?>">Inicio</a></li>
            <li><a href="<?php echo url('participant/profile'); ?>">Datos Generales</a></li>
            <li><a href="<?php echo url('participant/search-process'); ?>">Consultar Procesos</a></li>
        </ul>
    </nav>

    <div class="content-wrapper">
    <aside class="process-phases">
    <h3>Fases del Proceso</h3>
    <ul>
        <?php foreach ($phases as $phaseKey => $phaseName): ?>
            <li>
                <a href="#<?php echo $phaseKey; ?>" 
                   class="phase-link <?php echo $phaseKey === 'pyr' ? 'active' : ''; ?>"
                   data-phase="<?php echo $phaseKey; ?>">
                    <?php echo $phaseName; ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</aside>

        <main class="main-content">
            <h2>Producto: <?php echo htmlspecialchars($product['codigo']); ?><br>
            <button id="showDetailsBtn" class="btn btn-small">Detalles del Proceso</button></h2>
            
            <div id="product-info">
                <div class="tabs">
                    <ul class="tab-links">
                        <li class="active"><a href="#tab1">Descripción</a></li>
                        <li><a href="#tab2">Fechas</a></li>
                        <li><a href="#tab3">Producto</a></li>
                        <li><a href="#tab4">Archivos</a></li>
                    </ul>

                    <div class="tab-content">
                        <div id="tab1" class="tab active">
                            <?php include BASE_PATH . '/views/participant/tabs/description_tab.php'; ?>
                        </div>
                        <div id="tab2" class="tab">
                            <?php include BASE_PATH . '/views/participant/tabs/dates_tab.php'; ?>
                        </div>
                        <div id="tab3" class="tab">
                            <?php include BASE_PATH . '/views/participant/tabs/product_tab.php'; ?>
                        </div>
                        <div id="tab4" class="tab">
                            <?php include BASE_PATH . '/views/participant/tabs/files_tab.php'; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div id="phase-content" style="display: none;"></div>
        </main>
    </div>

    <script src="<?php echo js('participant-dashboard.js'); ?>"></script>
</body>
</html>