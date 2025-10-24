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
                <?php 
                $firstPhase = true;
                $activePhase = $currentStateCode; // La fase activa es la del estado actual
                
                foreach ($phases as $phaseKey => $phaseName): 
                    $isActive = ($phaseKey === $activePhase);
                    // Solo marcar como solo lectura si hay más de una fase disponible y esta no es la activa
                    $isReadOnly = ($phaseKey !== $activePhase && count($phases) > 1);
                ?>
                    <li>
                        <a href="#<?php echo $phaseKey; ?>" 
                           class="phase-link <?php echo $isActive ? 'active' : ''; ?> <?php echo $isReadOnly ? 'read-only' : ''; ?>"
                           data-phase="<?php echo $phaseKey; ?>"
                           <?php echo $isReadOnly ? 'title="Solo lectura - Fase anterior"' : ''; ?>>
                            <?php echo $phaseName; ?>
                            <?php if ($isReadOnly): ?>
                                <span class="read-only-indicator">(Solo lectura)</span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php 
                $firstPhase = false;
                endforeach; 
                ?>
            </ul>
        </aside>

        <main class="main-content">
            <div class="product-header">
                <h2>Producto: <?php echo htmlspecialchars($product['codigo']); ?></h2>
                <a href="<?php echo url('participant/view-product/' . $product['id']); ?>" class="btn btn-primary">
                    Detalles del Proceso
                </a>
            </div>
            
            <!-- Sistema de tabs informativos -->
            <div class="unified-tabs">
                <ul class="tab-links">
                    <li class="active"><a href="#tab-description" data-tab="description">Descripción</a></li>
                    <li><a href="#tab-dates" data-tab="dates">Fechas</a></li>
                    <li><a href="#tab-product" data-tab="product">Producto</a></li>
                    <li><a href="#tab-files" data-tab="files">Archivos</a></li>
                </ul>

                <div class="tab-content">
                    <div id="tab-description" class="tab active">
                        <?php include BASE_PATH . '/views/participant/tabs/description_tab.php'; ?>
                    </div>
                    <div id="tab-dates" class="tab">
                        <?php include BASE_PATH . '/views/participant/tabs/dates_tab.php'; ?>
                    </div>
                    <div id="tab-product" class="tab">
                        <?php include BASE_PATH . '/views/participant/tabs/product_tab.php'; ?>
                    </div>
                    <div id="tab-files" class="tab">
                        <?php include BASE_PATH . '/views/participant/tabs/files_tab.php'; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="<?php echo js('url-helper.js'); ?>"></script>
    <script src="<?php echo js('unified-tabs.js'); ?>"></script>
    
</body>
</html>