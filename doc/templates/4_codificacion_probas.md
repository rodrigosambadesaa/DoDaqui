# URL da páxina web

- Frontend: `http://localhost:3000`
- Backend (API): `http://localhost:8080/api`
- Estado API: `http://localhost:8080/api/health`

# Deseño dos prototipos 

## Prototipo 1
### Data de entrega: 16/03/2026
### Funcionalidades implementadas:
- RF-01 Rexistro de usuario (`POST /api/register`).
- RF-02 Inicio de sesion (`POST /api/login`).
- RF-03 Consulta do catalogo (`GET /api/products`).
- RF-05 Detalle de produto (`GET /api/products/{id}`).
- RF-06 Xestion basica do carriño (`GET /api/cart`, `POST /api/cart/items`, `DELETE /api/cart/items`).
- RF-07 Realizacion de pedido (`POST /api/orders`).
- RF-15 Integracion frontend-backend mediante API REST.

### Observacións:
- Arquitectura completamente separada entre frontend e backend.
- Todo o contorno esta dockerizado e arranca cun so comando: `docker compose up --build`.
- Base de datos SQL (MySQL 8) inicializada automaticamente con `docker/mysql/init.sql`.
- Credenciais de proba para avaliacion: `demo@tenda.gal` / `demo123`.
- Tamén se permite rexistro de usuario novo desde a interface.

### Innovación:
- Contorno reproducible en calquera equipo con Docker sen instalacion manual de dependencias.
- Estrutura preparada para evolucion por prototipos mantendo desacoplamento entre capas.

## Prototipo 2
### Data de entrega: 
### Funcionalidades implementadas:
### Observacións: 
### Innovación: 

## Prototipo 3
### Data de entrega: 
### Funcionalidades implementadas:
### Observacións:
### Innovación: 

## Prototipo 4
### Data de entrega: 
### Funcionalidades implementadas:
### Observacións: 
### Innovación: 

## Prototipo Final
### Data de entrega: 
### Funcionalidades implementadas:
### Observacións: 
### Innovación: 




