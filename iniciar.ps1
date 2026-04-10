# Script para iniciar DoDaquí v1.0.0 en Windows
# Uso: .\iniciar.ps1

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  DoDaqui v1.0.0 - Inicio Rapido" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Verifica PHP
Write-Host "[1/3] Verificando PHP..." -ForegroundColor Yellow
$phpVersion = php --version 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR: PHP no esta instalado" -ForegroundColor Red
    Write-Host "Visita: https://www.php.net/downloads" -ForegroundColor Gray
    Read-Host "Presiona Enter para cerrar"
    exit 1
}
Write-Host "OK - PHP disponible" -ForegroundColor Green
Write-Host ""

# Navega a backend/public
Write-Host "[2/3] Navegando a backend/public..." -ForegroundColor Yellow
if (-Not (Test-Path "backend/public")) {
    Write-Host "ERROR: No se encuentra la carpeta backend/public" -ForegroundColor Red
    Read-Host "Presiona Enter para cerrar"
    exit 1
}
Set-Location "backend/public"
Write-Host "OK - Posicion correcta" -ForegroundColor Green
Write-Host ""

# Inicia el servidor
Write-Host "[3/3] Iniciando servidor PHP..." -ForegroundColor Yellow
Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "SERVIDOR INICIADO EN:" -ForegroundColor Green
Write-Host "http://localhost:8080/home.php" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Presiona Ctrl+C para detener el servidor" -ForegroundColor Gray
Write-Host ""

php -S localhost:8080
