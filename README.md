# Base PHP con Docker

O repositorio queda reducido a unha base mínima para desenvolver a aplicación con arquivos PHP servidos por Apache dentro de Docker. O JavaScript irá cargado directamente desde as páxinas PHP mediante etiquetas script no head ou antes do peche de body usando defer ou async.

## Estrutura actual

- backend/: contedor web con PHP 8.2 + Apache.
- backend/public/: document root onde crear as páxinas .php.
- docker/mysql/: imaxe opcional de MySQL para entornos restrinxidos.
- docker-compose.yml: arranque normal con web + MySQL.
- docker-compose.restricted.yml: arranque alternativo sen bind mounts para MySQL.

## Arranque

Desde a raíz do proxecto:

```bash
docker compose up --build
```

Modo alternativo para equipos con restricións:

```bash
docker compose -f docker-compose.restricted.yml up --build
```

## Servizos

- web: PHP 8.2 + Apache en http://localhost:8080
- db: MySQL 8.0 en localhost:3306

## Acceso á aplicación

- Portada da tenda: `http://localhost:8080`
- Acceso e rexistro: `http://localhost:8080/auth.php`

Usuario de proba (sempre dispoñible):

- Correo: `demo@tenda.gal`
- Contrasinal: `Demo1234!`

Requisitos de contrasinal para rexistro:

- Mínimo 10 caracteres.
- Polo menos unha maiúscula.
- Polo menos unha minúscula.
- Polo menos un número.
- Polo menos un símbolo.

Podes cambiar os portos temporalmente:

```bash
set WEB_PORT=8081
set DB_PORT=3307
docker compose up --build
```

## Onde empezar

- Crea as túas páxinas PHP en backend/public.
- Usa etiquetas script nas páxinas PHP para cargar JavaScript propio.
- Se necesitas esquema inicial, engádeo en docker/mysql/init.sql.

## Reinicio limpo

```bash
docker compose down -v
docker compose up --build
```

Para o modo restrinxido:

```bash
docker compose -f docker-compose.restricted.yml down -v
docker compose -f docker-compose.restricted.yml up --build
```

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

## Despregamento en Internet

- Plataforma seleccionada para este proxecto: **DigitalOcean App Platform**.
- Especificación da app: `.do/app.yaml`.
- Guía paso a paso: [doc/despregamento_digitalocean.md](doc/despregamento_digitalocean.md).

## Vercel: base de datos y redeploy

Para habilitar login e rexistro reais en Vercel (sen modo demo), configura estas variables en **Production**:

- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

Tamén se admite `DATABASE_URL`/`MYSQL_URL`/`JAWSDB_URL` se prefires unha URL completa.

Configuración automática desde terminal:

```bash
export DB_HOST='...'
export DB_PORT='3306'
export DB_DATABASE='...'
export DB_USERNAME='...'
export DB_PASSWORD='...'
./scripts/vercel/configure-db-env.sh
```

Redeploy desde ambos repositorios:

- **GitHub**: Vercel desprega automaticamente cos pushes na rama configurada.
- **GitLab**: existe unha pipeline en `.gitlab-ci.yml` que dispara un deploy hook de Vercel en cada push a `master`.
