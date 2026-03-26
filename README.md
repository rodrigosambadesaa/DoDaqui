# Proxecto de fin de ciclo DAW

**Tenda DoDaqui** e unha aplicacion web de comercio electronico enfocada na venda de produtos econaturais galegos de proximidade. O obxectivo do proxecto e ofrecer unha plataforma sinxela para conectar persoas consumidoras con pequenos produtores locais, garantindo transparencia na orixe dos produtos e fomentando a economia local.

Nesta primeira iteracion (Prototipo 1), a solucion xa inclue separacion completa entre frontend e backend, comunicacion por API REST e base de datos SQL. As funcionalidades iniciais dispoñibles son rexistro de usuario, inicio de sesion, consulta de catalogo, xestion basica de carriño e creacion de pedidos. Toda a infraestrutura esta contedorizada con Docker para poder arrancar o sistema completo cun so comando, facilitando tanto o desenvolvemento como a avaliacion.

## Instalación/Posta en marcha

### Requisitos

- Docker Desktop instalado e en execucion.

### Arranque cun so comando

Desde a raiz do repositorio:

```bash
docker compose up --build
```

### Arranque alternativo para equipos con restricions (instituto)

Se nun equipo hai restricions de permisos (especialmente para montaxe de carpetas do host), usar:

```bash
docker compose -f docker-compose.restricted.yml up --build
```

Esta configuracion alternativa evita montaxes bind locais e empaqueta a inicializacion SQL dentro da imaxe de MySQL.
Por iso adoita funcionar mellor en equipos con politicas restritivas.

Este comando levanta automaticamente:

- `frontend` (Node + Express) en `http://localhost:3000`
- `backend` (PHP 8.2 + Apache) en `http://localhost:8080`
- `db` (MySQL 8) con inicializacion SQL automatica desde `docker/mysql/init.sql`

Se queres reconstruir e reiniciar totalmente os datos:

```bash
docker compose down -v
docker compose up --build
```

Para o modo restrinxido:

```bash
docker compose -f docker-compose.restricted.yml down -v
docker compose -f docker-compose.restricted.yml up --build
```

### Datos de proba

- Podes crear unha conta desde a propia interface web (recomendado).
- Usuario demo (base de datos inicial): `demo@tenda.gal` / `demo123`.

### API REST (referencia rapida)

Referencia completa: [API reference](doc/api_reference.md)

- `GET /api/health`
- `POST /api/register`
- `POST /api/login`
- `GET /api/products`
- `GET /api/products/{id}`
- `GET /api/cart?user_id={id}`
- `POST /api/cart/items`
- `DELETE /api/cart/items`
- `POST /api/orders`
- `GET /api/orders?user_id={id}`

## Uso

- Frontend web: `http://localhost:3000`
- Checkout independente: `http://localhost:3000/checkout.html`
- Backend API: `http://localhost:8080/api`

Se no equipo do instituto hai conflito de portos, podes cambialos temporalmente:

```bash
set FRONTEND_PORT=3001
set BACKEND_PORT=8081
docker compose -f docker-compose.restricted.yml up --build
```

## Acceso para profesorado (checklist rapido)

1. Executar na raiz do proxecto: `docker compose up --build`.
2. Abrir no navegador: `http://localhost:3000`.
3. Iniciar sesion con usuario demo: `demo@tenda.gal` / `demo123`.
4. Verificar estado da API en: `http://localhost:8080/api/health`.
5. Probar fluxo base:
	- Cargar catalogo.
	- Engadir produto ao carriño.
	- Ir a `http://localhost:3000/checkout.html` e crear pedido.
	- Consultar pedidos.

## Persistencia de datos tras reinicios/caidas

- O proxecto conserva datos en volumes Docker nomeados (`mysql_data` no modo normal e `mysql_data_restricted` no modo restrinxido).
- Isto implica que usuarios, carriños e pedidos seguen dispoñibles tras reiniciar contedores.

Proba rapida recomendada:

1. Arrancar contorno e crear un pedido desde o checkout.
2. Executar: `docker compose down` (ou `docker compose -f docker-compose.restricted.yml down`).
3. Arrancar de novo: `docker compose up --build` (ou o comando restrinxido).
4. Iniciar sesion co mesmo usuario e verificar que o historial de pedidos segue presente.

Nota: so se perde información cando se usa `down -v`, porque ese comando elimina tamén os volumes persistentes.

Se se quere parar o contorno:

```bash
docker compose down
```

Se se quere reiniciar con base de datos limpa:

```bash
docker compose down -v
docker compose up --build
```

## Arquitectura (separacion frontend/backend)

- Frontend desacoplado en `frontend/` (Node + Express, HTML, CSS, JavaScript).
- Backend desacoplado en `backend/` (PHP 8.2 + Apache con API REST).
- Base de datos SQL en contedor independente (`mysql:8.0`) con script de inicializacion en `docker/mysql/init.sql`.
- Orquestracion con `docker-compose.yml` para executar todo cun so comando.

Fluxo recomendado para probar o Prototipo 1:

1. Rexistrar un usuario novo na pantalla principal.
2. Iniciar sesion con ese usuario.
3. Cargar o catalogo e engadir produtos ao carriño.
4. Crear un pedido e revisar o listado de pedidos.

## Sobre a persoa autora

> *Tarefa*: Realiza unha breve descrición de quen es desde unha perspectiva profesional, os teus puntos fortes, tecnoloxías que máis dominas e o motivo de por que te decantaches por este proxecto. **Non máis de 200 palabras**. Indica unha forma fiable de contactar contigo no presente e no futuro.

## Licencia

> *Tarefa*: É requisito INDISPENSABLE licenciar explicitamente o proxecto. Crea un ficheiro `LICENSE` na raíz do repositorio.

## Guía de contribución

> *Tarefa*: Se o teu proxecto se trata de software libre, é importante que expoñas como se pode contribuír a el. Algúns exemplos disto son realizar novas funcionalidades, corrección e/ou optimización de código, realización de tests automatizados, novas interfaces de integración, desenvolvemento de plugins, etc. Intenta dar unha mensaxe concisa.

## Memoria

> *Tarefa*: Indexa de forma ordenada a memoria do teu proxecto.
> Durante a redacción da memoria, debes ir completando progresivamente o anexo de Referencias.

1. [Estudo preliminar](doc/templates/1_estudo_preliminar.md)
2. [Análise](doc/templates/2_analise.md)
3. [Deseño](doc/templates/3_deseno.md)
4. [Planificación e Orzamento](doc/templates/a3_orzamento.md)
5. [Codificación e Probas](doc/templates/4_codificacion_probas.md)
6. [Futuro e comercialización](doc/templates/5_manuais.md)
