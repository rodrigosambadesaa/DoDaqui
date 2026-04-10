@echo off
REM Script para iniciar DoDaquí v1.0.0 en Windows
REM Uso: Ejecuta este archivo desde la carpeta del proyecto

echo ========================================
echo   DoDaqui v1.0.0 - Inicio Rapido
echo ========================================
echo.

REM Verifica PHP
echo [1/3] Verificando PHP...
php --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: PHP no esta instalado
    echo Visita: https://www.php.net/downloads
    pause
    exit /b 1
)
echo OK - PHP disponible
echo.

REM Navega a backend/public
echo [2/3] Navegando a backend/public...
cd backend\public
if errorlevel 1 (
    echo ERROR: No se encuentra la carpeta backend\public
    pause
    exit /b 1
)
echo OK - Posicion correcta
echo.

REM Inicia el servidor
echo [3/3] Iniciando servidor PHP...
echo.
echo ========================================
echo SERVIDOR INICIADO EN:
echo http://localhost:8080/home.php
echo ========================================
echo.
echo Presiona Ctrl+C para detener el servidor
echo.

php -S localhost:8080

pause
