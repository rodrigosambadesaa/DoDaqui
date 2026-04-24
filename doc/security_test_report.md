# Informe de Pruebas de Seguridad y Funcionalidad

Fecha: 2026-04-14
Entorno: Docker Compose local (`web` + `db`)
URL base: `http://localhost:8080`
Script: `tests/security_smoke.ps1`

## Resultado general

- Estado: OK
- Ejecución: completada sin errores

## Cobertura ejecutada

- Acceso a página principal: OK
- Verificación de enlaces de navegación extraídos desde inicio: OK
- Registro de usuario con CSRF: OK
- Inicio de sesión con CSRF: OK
- Añadir producto al carrito con CSRF en API JSON: OK
- Rechazo de petición a carrito con token CSRF inválido: OK
- Realización de pedido con CSRF y validaciones de backend: OK
- Rate limiter de login (IP/correo): OK
- Rate limiter de registro (IP/correo): OK

## Comandos usados

```powershell
docker compose up -d --build
pwsh -ExecutionPolicy Bypass -File tests/security_smoke.ps1 -BaseUrl http://localhost:8080
```

## Notas

- El script está diseñado como smoke test E2E y valida rutas principales, flujos críticos y controles de abuso.
- Para repetir las pruebas en otro puerto o host, usar `-BaseUrl`.
