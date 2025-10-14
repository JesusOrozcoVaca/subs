<?php
/**
 * Script para cambiar al nuevo sistema de routing
 * 
 * Este script:
 * 1. Hace backup del index.php actual
 * 2. Renombra index_new.php a index.php
 * 3. Actualiza config/app.php para usar nuevas URLs
 */

echo "=== CAMBIANDO AL NUEVO SISTEMA DE ROUTING ===\n";

// Hacer backup del index.php actual
if (file_exists('index.php')) {
    if (copy('index.php', 'index_legacy_backup.php')) {
        echo "✓ Backup creado: index_legacy_backup.php\n";
    } else {
        echo "✗ Error al crear backup\n";
        exit(1);
    }
}

// Renombrar index_new.php a index.php
if (file_exists('index_new.php')) {
    if (rename('index_new.php', 'index.php')) {
        echo "✓ index_new.php renombrado a index.php\n";
    } else {
        echo "✗ Error al renombrar index_new.php\n";
        exit(1);
    }
} else {
    echo "✗ index_new.php no encontrado\n";
    exit(1);
}

// Hacer backup de config/app.php
if (file_exists('config/app.php')) {
    if (copy('config/app.php', 'config/app_backup.php')) {
        echo "✓ Backup creado: config/app_backup.php\n";
    }
}

echo "\n=== SISTEMA CAMBIADO EXITOSAMENTE ===\n";
echo "✓ Nuevo sistema de routing activado\n";
echo "✓ URLs ahora usan query parameters\n";
echo "✓ Sin dependencia de .htaccess\n";
echo "✓ Sistema más robusto contra interferencias externas\n";

echo "\nPara volver al sistema anterior:\n";
echo "1. mv index_legacy_backup.php index.php\n";
echo "2. mv config/app_backup.php config/app.php\n";

echo "\n¡Listo! Prueba acceder a: index.php?action=login\n";
?>
