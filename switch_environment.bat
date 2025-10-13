@echo off
echo Configuracion de Entorno del Sistema de Subastas
echo ================================================
echo.
echo 1. Desarrollo Local (BASE_URL = /subs/)
echo 2. Produccion (BASE_URL = /)
echo.
set /p choice="Selecciona el entorno (1 o 2): "

if "%choice%"=="1" (
    echo.
    echo Configurando para DESARROLLO LOCAL...
    if exist "config\app.production.php" (
        copy "config\app.php" "config\app.development.php" >nul
        copy "config\app.production.php" "config\app.php" >nul
        echo ERROR: Ya existe una configuracion de desarrollo. 
        echo Por favor, verifica manualmente los archivos.
        pause
        exit /b 1
    )
    echo BASE_URL configurado para: /subs/
    echo DEBUG configurado para: false (sin mensajes de debug)
    echo.
    echo La aplicacion funcionara en: http://localhost/subs/
    echo.
) else if "%choice%"=="2" (
    echo.
    echo Configurando para PRODUCCION...
    if exist "config\app.production.php" (
        copy "config\app.php" "config\app.development.php" >nul
        copy "config\app.production.php" "config\app.php" >nul
        echo BASE_URL configurado para: /
        echo DEBUG configurado para: false (sin mensajes de debug)
        echo.
        echo La aplicacion funcionara en: https://sie.hjconsulting.com.ec/
        echo.
        echo IMPORTANTE: Agrega config\app.production.php al .gitignore
        echo.
    ) else (
        echo ERROR: No se encontro config\app.production.php
        echo Por favor, crea el archivo de configuracion de produccion.
        pause
        exit /b 1
    )
) else (
    echo.
    echo Opcion invalida. Selecciona 1 o 2.
    pause
    exit /b 1
)

echo Configuracion completada!
echo.
echo Archivos de configuracion:
echo - config\app.php (configuracion actual)
echo - config\app.development.php (backup desarrollo)
echo - config\app.production.php (plantilla produccion)
echo.
pause
