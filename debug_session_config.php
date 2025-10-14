<?php
// Script avanzado de diagnóstico de sesiones
session_start();

echo "<h2>Diagnóstico Avanzado de Sesiones</h2>";

echo "<h3>1. Información de Sesión Actual:</h3>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Status:</strong> " . session_status() . "</p>";
echo "<p><strong>Session Name:</strong> " . session_name() . "</p>";

echo "<h3>2. Configuración de Sesiones PHP:</h3>";
echo "<pre>";
echo "session.save_path: " . ini_get('session.save_path') . "\n";
echo "session.save_handler: " . ini_get('session.save_handler') . "\n";
echo "session.use_cookies: " . ini_get('session.use_cookies') . "\n";
echo "session.use_only_cookies: " . ini_get('session.use_only_cookies') . "\n";
echo "session.cookie_lifetime: " . ini_get('session.cookie_lifetime') . "\n";
echo "session.cookie_path: " . ini_get('session.cookie_path') . "\n";
echo "session.cookie_domain: " . ini_get('session.cookie_domain') . "\n";
echo "session.cookie_secure: " . ini_get('session.cookie_secure') . "\n";
echo "session.cookie_httponly: " . ini_get('session.cookie_httponly') . "\n";
echo "session.gc_maxlifetime: " . ini_get('session.gc_maxlifetime') . "\n";
echo "session.gc_probability: " . ini_get('session.gc_probability') . "\n";
echo "session.gc_divisor: " . ini_get('session.gc_divisor') . "\n";
echo "</pre>";

echo "<h3>3. Contenido de \$_SESSION:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>4. Test de Escritura de Sesión:</h3>";
$_SESSION['test_timestamp'] = time();
$_SESSION['test_data'] = 'Test data from ' . date('Y-m-d H:i:s');
session_write_close();

echo "<p>Se escribió data de prueba en la sesión.</p>";
echo "<p><strong>Refresca esta página</strong> para ver si los datos persisten.</p>";

echo "<h3>5. Información del Servidor:</h3>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "</pre>";

echo "<h3>6. Directorio de Sesiones:</h3>";
$sessionPath = ini_get('session.save_path');
if (empty($sessionPath)) {
    $sessionPath = sys_get_temp_dir();
}
echo "<p><strong>Ruta de sesiones:</strong> $sessionPath</p>";
echo "<p><strong>¿Existe el directorio?</strong> " . (is_dir($sessionPath) ? 'Sí' : 'No') . "</p>";
echo "<p><strong>¿Es escribible?</strong> " . (is_writable($sessionPath) ? 'Sí' : 'No') . "</p>";

if (is_dir($sessionPath)) {
    echo "<p><strong>Archivos de sesión:</strong></p>";
    $files = glob($sessionPath . '/sess_*');
    echo "<pre>";
    foreach ($files as $file) {
        echo basename($file) . " - " . date('Y-m-d H:i:s', filemtime($file)) . "\n";
    }
    echo "</pre>";
}
?>
