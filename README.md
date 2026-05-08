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
- phpmyadmin: cliente SQL web en http://localhost:8081 (ou o porto definido en `PHPMYADMIN_PORT`)

## Acceso á aplicación

- Portada da tenda: `http://localhost:8080`
- Acceso e rexistro: `http://localhost:8080/auth.php`
- Panel de administración (usuario admin): `http://localhost:8080/admin.php`

Usuario de proba (sempre dispoñible):

- Correo: `demo@tenda.gal`
- Contrasinal: `Demo1234!`

Usuario administrador local (sempre dispoñible):

- Correo: `admin@tenda.gal`
- Contrasinal: `Admin1234!`

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

Exemplo recomendado para evitar conflitos de porto en local:

```bash
export WEB_PORT=8080
export DB_PORT=3307
export PHPMYADMIN_PORT=8082
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

Son Rodrigo Sambade Saa, desenvolvedor de aplicacións multiplataforma con experiencia en desenvolvemento web, móbil e investigación en tecnoloxías da linguaxe. Traballei no Concello de Santiago creando aplicacións móbiles e anteriormente no CiTIUS (USC), combinando tarefas de investigación, administración web e refactorización de software. Tamén teño experiencia profesional con Java e contornas empresariais en prácticas en Coremain.

Os meus puntos fortes son a capacidade de entrega rápida sen perder calidade, o enfoque no detalle e o traballo en equipo. As tecnoloxías que máis emprego son PHP, Java, JavaScript, HTML, CSS, SQL, Docker e Git, ademais de experiencia con frameworks e ferramentas modernas de front-end.

Decanteime por este proxecto porque une comercio local, sustentabilidade e tecnoloxía aplicada a un problema real: achegar produtores galegos ao cliente final cunha plataforma clara e útil.

Contacto actual e futuro:

- Correos:
  - rodrigosambadesaa@gmail.com
  - rodrigosambadesaa1@gmail.com
  - rodrigosambadesaa2@gmail.com
  - rodrigosambadesaa@outlook.com
  - rodrigosambadesaa@outlook.es
  - rodrigo.sambade.saa@protonmail.com
  - rodrigosambadesaa@yahoo.com
  - rodrigosambadesaa@yahoo.es
  - rodrigosambadesaa@aol.com
- Teléfono: 608030077
- LinkedIn: https://es.linkedin.com/in/rodrigo-sambade-5675bb186/es

## Licencia

Este proxecto distribúese baixo unha licenza propietaria con todos os dereitos reservados. Consulta as condicións completas en [LICENSE](LICENSE).

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

## Futuro e comercialización

### Promoción

- Redes sociais (Instagram/Facebook) con campañas segmentadas en ámbito galego.
- SEO con palabras clave de produto local e consumo responsable.
- Colaboracións con produtores e participación en feiras/eventos.
- Contido multimedia curto para difusión da proposta de valor.

### Modelo de negocio

- Comisión por venda como modelo principal.
- Plan premium opcional para produtores con funcionalidades avanzadas.
- Promoción destacada de produtos/produtores como ingreso adicional.

### Melloras futuras

- Integración de pasarela de pagamento real.
- Xestión avanzada de loxística e notificacións.
- Analítica para produtores e funcionalidades de fidelización.
- Melloras de accesibilidade, internacionalización e observabilidade técnica.

Ligazón ao proxecto en GitLab (entrega):

- https://gitlab.iessanclemente.net/dawo/a23rodrigoss

## Despregamento en Internet

- Plataforma seleccionada para este proxecto: **Vercel**.
- Configuración principal de despregamento: `vercel.json`.
- Guía paso a paso: [doc/despregamento_vercel.md](doc/despregamento_vercel.md).

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
