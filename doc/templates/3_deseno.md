# Deseño

Este documento recolle o deseño de interface e o deseño de base de datos do proxecto.

## Deseño de interface de usuarios

![Wireframes](../img/Wireframe-Homepage%20Desktop.png)

## Identidade visual

### Paleta de cores

As cores principais da paleta son:

- `#F8F9FA`
- `#E9ECEF`
- `#DEE2E6`
- `#ADB5BD`
- `#6C757D`
- `#495057`
- `#343A40`

Uso recomendado na interface:

- `#F8F9FA`, `#E9ECEF` e `#DEE2E6` para fondos e superficies principais.
- `#ADB5BD` para liñas divisorias e elementos secundarios.
- `#6C757D` para texto secundario e estados neutros.
- `#495057` e `#343A40` para texto principal e títulos, garantindo bo contraste.

### Tipografía

Proponse unha tipografía sans-serif limpa e lexible:

- Títulos e chamadas principais: `Montserrat` (peso semibold/bold).
- Texto corrido e elementos de interface: `Open Sans` (peso regular/medium).

Esta combinación busca unha identidade visual moderna, clara e coherente en móbil e escritorio.

## Deseño de Base de Datos

Para maior comodidade especifícase en texto en vez en de diagramas.

### Modelo Entidade - Relación

#### 1. Entidades e atributos

**usuarios**
- `id_usuario` (PK)
- `nome`
- `email` (UNIQUE)
- `contrasinal` (hash)
- `enderezo` (para o envío)
- `rol` (cliente/admin)
- `telefono`

**produtos**
- `id_produto` (PK)
- `nome`
- `descripcion`
- `prezo`
- `stock`
- `categoria` (atributo de texto para filtrar)
- `imaxe_url`

**pedidos**
- `id_pedido` (PK)
- `id_usuario` (FK → usuarios)
- `data_pedido`
- `total`
- `estado` (pendente/enviado)

**pedido_liñas**
- `id_lina` (PK)
- `id_pedido` (FK → pedidos)
- `id_produto` (FK → produtos)
- `cantidade`
- `prezo_unitario` (para conxelar o prezo da venda)

**opinions_clientes**
- `id_opinion` (PK)
- `id_produto` (FK → produtos)
- `id_cliente` (FK → usuarios)
- `data`
- `valoracion`
- `opinion`

#### 2. Relacións e cardinalidades

- **Usuarios (1) — (N) Pedidos**: Un usuario pode realizar moitos pedidos; un pedido pertence a un só usuario.
- **Pedidos (1) — (N) Pedido_liñas**: Un pedido divídese en varias liñas de detalle.
- **Produtos (1) — (N) Pedido_liñas**: Un produto pode aparecer en moitas liñas de diferentes pedidos.

#### 3. Modelo relacional

```
usuarios (id_usuario, nome, email, contrasinal, enderezo, rol, telefono)

produtos (id_produto, nome, descripcion, prezo, stock, categoria, imaxe_url)

pedidos (id_pedido, id_usuario, data_pedido, total, estado)
    FK: id_usuario → usuarios

pedido_liñas (id_lina, id_pedido, id_produto, cantidade, prezo_unitario)
    FK: id_pedido → pedidos
    FK: id_produto → produtos

opinions_clientes (id_opinion, id_produto, id_cliente, data, valoracion, opinion)
    FK: id_produto → produtos
    FK: id_cliente → usuarios
```


