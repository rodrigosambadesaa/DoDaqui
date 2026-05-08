# URL da páxina web

- Tenda web: `http://localhost:8080`
- Login e rexistro: `http://localhost:8080/auth.php`

# Deseño dos prototipos 

## Prototipo 1
### Data de entrega: 10/04/2026
### Funcionalidades implementadas:
- Rexistro de usuario con validación forte de contrasinal.
- Inicio e peche de sesión.
- Consulta visual de catálogo na portada.
- Vista de detalle resumida de produtos no layout.
- Visualización do carro da compra.
- Realización de pedido (neste paso almacénase o pedido na base de datos)

A información tratarase de xestionar de forma segura.

### Observacións:
- Todo o contorno está dockerizado e arranca cun só comando: `docker compose up --build`.
- A base de datos SQL inicialízase automaticamente con `docker/mysql/init.sql`.
- Usuario de proba permanente para avaliación: `demo@tenda.gal` / `Demo1234!`.
- O rexistro esixe contrasinal forte: 10+ caracteres, maiúscula, minúscula, número e símbolo.

### Innovación:
- Contorno reproducible en calquera equipo con Docker sen instalación manual de dependencias.
- Interface visual de tenda aliñada co mockup e preparada para evolución por prototipos.

## Prototipo 2
### Data de entrega: 24/04/2026 (iteración local en curso)
### Funcionalidades implementadas:
- Buscar produtos por texto.
- Consulta do historial de pedidos para usuarios rexistrados
- Xestión do perfil de usuario (actualización de nome/teléfono e cambio de contrasinal). (revisar)
- Visualización da base de datos con phpMyAdmin no contorno local Docker. (arreglar)

### Observacións:
- Este prototipo engade máis dos requirimentos funcionais pendentes no documento de análise.
- A base técnica segue sendo PHP + Docker + MySQL, reutilizando o contorno do primeiro prototipo.
- Non se realiza despregamento nesta fase: as probas fanse en local antes de publicar.
- A navegación principal xa incorpora accesos directos a Pedidos e Perfil.

### Innovación:
- Navegación máis orientada á experiencia de compra real, con busca e filtros para atopar produtos antes de engadilos ao carriño.
- Separación clara entre catálogo público, conta de usuario e seguimento dos pedidos.
- Persistencia de información clave para dar continuidade ao uso da aplicación entre sesións.
- Validación local máis rápida grazas á inspección directa de datos en phpMyAdmin.

## Despregamento da web na Internet

### Plataforma elixida
- Vercel + base de datos MySQL externa (configurada por variables de contorno).

### Xustificación técnica
- Permite despregamento continuo automático en cada push á rama configurada.
- Encaixa co fluxo actual do proxecto e coa configuración existente en `vercel.json`.
- Reduce carga operativa fronte a administración manual de infraestrutura.

### Estado da publicación
- Configuración lista: `vercel.json`.
- Guía de despregamento: `doc/despregamento_vercel.md`.
- Variables de contorno de base de datos dispoñibles para contorno de produción (`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
- Automatización dispoñible: script `scripts/vercel/configure-db-env.sh` para cargar variables e facilitar redeploy.

### Entrega en Issues (GitLab)
- Ao completar a publicación, crear unha issue en GitLab con:
	- URL pública da aplicación.
	- Capturas de portada, login e checkout.
	- Resultado das probas básicas de funcionamento.

## Prototipo 3
### Data de entrega: 19/05/2026
### Funcionalidades implementadas:
- Xestión de produtos para administrador (alta, modificación e baixa).
- Xestión de categorías para administrador.
- Xestión de produtores para administrador.
- Consulta e xestión de pedidos desde panel de administración.
- Control de usuarios rexistrados desde o panel de administración.

### Observacións:
- Este prototipo céntrase nos fluxos de administración pendentes definidos no estudo preliminar e no documento de análise.
- Mantense a base técnica PHP + Docker + MySQL, aproveitando a estrutura xa creada nos prototipos anteriores.
- A validación funcional deste bloque realízase con probas locais e verificación de permisos por rol (cliente/admin).

### Innovación: 
- Evolución da aplicación desde tenda de cliente a plataforma completa con operacións de backoffice.
- Separación máis clara entre funcionalidades de cliente e funcionalidades de administración.
- Mellora da mantenibilidade ao centralizar xestión de catálogo, pedidos e usuarios nunha capa administrativa.

## Prototipo 4
### Data de entrega: 19/05/2026
### Funcionalidades implementadas:
- Consolidación da API REST para operacións clave de autenticación, catálogo, carriño e pedidos.
- Estabilización da autenticación segura en contorno de Internet (sesión, CSRF e control de intentos).
- Melloras de persistencia relacional para manter coherencia entre usuarios, produtos, pedidos e opinións.
- Adaptación do despregamento a contorno cloud reproducible (Vercel + variables de contorno + validación post-deploy).
- Execución de probas funcionais de regresión dos fluxos cliente/admin para garantir que non se rompen funcionalidades previas.

### Observacións: 
- Este prototipo céntrase nos requirimentos técnicos do estudo preliminar, completando a capa de integración entre frontend e backend.
- Mantense o desenvolvemento con PHP sen framework e base de datos SQL relacional, coherente coa arquitectura definida no anteproxecto.
- O traballo inclúe validación en local con Docker e validación en produción tras despregamento.

### Innovación: 
- Paso de prototipo funcional local a versión operativa en Internet mantendo trazabilidade técnica dos cambios.
- Estratexia de fallback controlado para manter accesibles os fluxos críticos en escenarios de indispoñibilidade parcial da base de datos.
- Reforzo da calidade mediante probas de regresión sobre funcionalidades xa entregadas en prototipos anteriores.

## Prototipo Final
### Data de entrega: 02/06/2026 (prevista)
### Funcionalidades implementadas:
- Peche integral de todos os requirimentos funcionais do estudo preliminar para cliente e administrador.
- Hardening final de seguridade e operación (control de sesión, validación de entrada, control de permisos e tratamento de erros).
- Revisión final da API e da persistencia de datos para garantir consistencia funcional extremo a extremo.
- Validación final de despregamento reproducible en produción e revisión da configuración de contorno.
- Preparación da evidencia final de probas funcionais e técnicas para a entrega da memoria.

### Observacións: 
- Este bloque corresponde ao peche do proxecto, priorizando estabilidade, calidade e trazabilidade da entrega.
- Inclúe revisión de documentación técnica e contraste cos obxectivos definidos no anteproxecto e na análise.
- O resultado esperado é unha versión lista para avaliación, con funcionalidades completas e comportamento consistente.

### Innovación: 
- Integración final de requisitos funcionais e técnicos nun ciclo iterativo por prototipos, reducindo risco de regresión.
- Enfoque DevOps aplicado a un proxecto académico: contorno local reproducible, despregamento cloud e validación continua.
- Produto final orientado a uso real, con separación clara entre operacións de cliente e backoffice administrativo.




