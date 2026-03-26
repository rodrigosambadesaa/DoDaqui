# API Reference - Tenda DoDaqui (Prototipo 1)

Base URL: `http://localhost:8080/api`

## 1) Health check

### GET `/health`

Resposta 200:

```json
{
  "status": "ok",
  "service": "backend"
}
```

## 2) Rexistro de usuario

### POST `/register`

Body JSON:

```json
{
  "nome": "Usuario Proba",
  "email": "usuario@tenda.gal",
  "contrasinal": "demo123"
}
```

Resposta 201:

```json
{
  "message": "Usuario rexistrado",
  "user_id": 5
}
```

## 3) Login

### POST `/login`

Body JSON:

```json
{
  "email": "demo@tenda.gal",
  "contrasinal": "demo123"
}
```

Resposta 200:

```json
{
  "message": "Login correcto",
  "user": {
    "id_usuario": 2,
    "nome": "Cliente Demo",
    "email": "demo@tenda.gal",
    "rol": "cliente"
  }
}
```

## 4) Catalogo de produtos

### GET `/products`

Resposta 200:

```json
{
  "items": [
    {
      "id_produto": 1,
      "nome": "Tomate Eco",
      "slug": "tomate-eco",
      "descricion_curta": "Tomate galego de temporada.",
      "prezo": "2.50",
      "stock": 120,
      "categoria": "Verduras",
      "produtor": "Horta da Ria"
    }
  ]
}
```

### GET `/products/{id}`

Exemplo: `GET /products/1`

## 5) Carriño

### GET `/cart?user_id={id}`

Exemplo: `GET /cart?user_id=2`

### POST `/cart/items`

Body JSON:

```json
{
  "user_id": 2,
  "product_id": 1,
  "quantity": 1
}
```

Resposta 200:

```json
{
  "message": "Carriño actualizado"
}
```

### DELETE `/cart/items`

Body JSON:

```json
{
  "user_id": 2,
  "product_id": 1
}
```

## 6) Pedidos

### POST `/orders`

Body JSON:

```json
{
  "user_id": 2
}
```

Resposta 201:

```json
{
  "message": "Pedido creado",
  "id_pedido": 10,
  "codigo_pedido": "PED-20260316220000-123",
  "importe_total": 2.5
}
```

### GET `/orders?user_id={id}`

Exemplo: `GET /orders?user_id=2`

## Codigos de erro habituais

- `401`: credenciais incorrectas.
- `404`: recurso non atopado.
- `409`: conflito de negocio (ex. stock insuficiente, carriño baleiro).
- `422`: campos obrigatorios non informados.
- `500`: erro interno do servidor.
