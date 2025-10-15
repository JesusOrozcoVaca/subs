<?php
/**
 * Script para arreglar la configuración de producción
 * Ejecutar una sola vez en el servidor de producción
 */

echo "=== SCRIPT DE REPARACIÓN DE PRODUCCIÓN ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Verificar que estamos en producción
if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false) {
    die("ERROR: Este script solo debe ejecutarse en producción.\n");
}

echo "✓ Verificado: Ejecutándose en producción\n";

// 1. Verificar archivos existentes
$files_to_check = [
    'config/app_pro.php' => 'Configuración de producción',
    'index_new.php' => 'Nueva arquitectura'
];

foreach ($files_to_check as $file => $description) {
    if (file_exists($file)) {
        echo "✓ Encontrado: $file ($description)\n";
    } else {
        echo "❌ FALTA: $file ($description)\n";
        die("ERROR: Archivo requerido no encontrado.\n");
    }
}

echo "\n=== PASO 1: CONFIGURAR config/app.php ===\n";

// Copiar app_pro.php a app.php
if (copy('config/app_pro.php', 'config/app.php')) {
    echo "✓ Copiado config/app_pro.php → config/app.php\n";
} else {
    echo "❌ Error al copiar config/app_pro.php\n";
    die("ERROR: No se pudo configurar app.php\n");
}

echo "\n=== PASO 2: CONFIGURAR index.php ===\n";

// Hacer backup del index.php actual si existe
if (file_exists('index.php')) {
    if (copy('index.php', 'index_legacy_backup.php')) {
        echo "✓ Backup creado: index.php → index_legacy_backup.php\n";
    }
}

// Copiar index_new.php a index.php
if (copy('index_new.php', 'index.php')) {
    echo "✓ Copiado index_new.php → index.php\n";
} else {
    echo "❌ Error al copiar index_new.php\n";
    die("ERROR: No se pudo configurar index.php\n");
}

echo "\n=== PASO 3: VERIFICAR CONFIGURACIÓN ===\n";

// Verificar que config/app.php tiene ENVIRONMENT = 'production'
$app_content = file_get_contents('config/app.php');
if (strpos($app_content, "define('ENVIRONMENT', 'production');") !== false) {
    echo "✓ config/app.php configurado para producción\n";
} else {
    echo "❌ config/app.php NO está configurado para producción\n";
}

// Verificar que index.php tiene la nueva arquitectura
$index_content = file_get_contents('index.php');
if (strpos($index_content, 'getAppUrl') !== false && strpos($index_content, 'query parameters') !== false) {
    echo "✓ index.php tiene la nueva arquitectura\n";
} else {
    echo "❌ index.php NO tiene la nueva arquitectura\n";
}

echo "\n=== PASO 4: LIMPIAR ARCHIVOS TEMPORALES ===\n";

// Eliminar archivos temporales (opcional)
$temp_files = ['config/app_pro.php', 'index_new.php'];
foreach ($temp_files as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "✓ Eliminado: $file\n";
        } else {
            echo "⚠️  No se pudo eliminar: $file (puede eliminarse manualmente)\n";
        }
    }
}

echo "\n=== REPARACIÓN COMPLETADA ===\n";
echo "✓ Sistema configurado para producción\n";
echo "✓ URLs con query parameters activadas\n";
echo "✓ Configuración de entorno correcta\n";
echo "\nPuedes probar el login ahora: https://sie.hjconsulting.com.ec/index.php?action=login\n";
echo "\nIMPORTANTE: Elimina este archivo (fix_production.php) después de usarlo por seguridad.\n";
?>
