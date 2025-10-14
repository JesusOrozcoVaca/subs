<?php
// Script de diagnóstico para verificar sesiones
session_start();

echo "<h2>Diagnóstico de Sesiones</h2>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Status:</strong> " . session_status() . "</p>";
echo "<p><strong>Session Name:</strong> " . session_name() . "</p>";

echo "<h3>Contenido de \$_SESSION:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>Headers de Sesión:</h3>";
echo "<pre>";
print_r(getallheaders());
echo "</pre>";

echo "<h3>Cookies:</h3>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";

echo "<h3>Variables de Servidor:</h3>";
echo "<pre>";
echo "HTTP_USER_AGENT: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'No definido') . "\n";
echo "REMOTE_ADDR: " . ($_SERVER['REMOTE_ADDR'] ?? 'No definido') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'No definido') . "\n";
echo "</pre>";
?>
