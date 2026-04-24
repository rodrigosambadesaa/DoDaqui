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
- Busca por texto e filtrado combinado por categoría no catálogo.
- Ordenación de produtos por prezo e nome no catálogo.
- Consulta do historial de pedidos para usuarios rexistrados con detalle de liñas por pedido.
- Xestión do perfil de usuario (actualización de nome/teléfono e cambio de contrasinal).
- Visualización da base de datos con phpMyAdmin no contorno local Docker.

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
- DigitalOcean App Platform + MySQL xestionado.

### Xustificación técnica
- O proxecto xa parte dun `Dockerfile` para PHP+Apache, polo que o encaixe con App Platform é directo.
- Evita administrar máquina virtual completa (IaaS) para esta fase.
- Permite publicar versións novas de forma máis rápida desde o repositorio.

### Estado da publicación
- Configuración lista: `.do/app.yaml`.
- Guía de despregamento: `doc/despregamento_digitalocean.md`.
- Proba de CLI realizada: `doctl` operativo.
- Bloqueo actual: falta token de conta para executar `doctl auth init` e crear a app no provedor.

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
### Data de entrega: 
### Funcionalidades implementadas:
### Observacións: 
### Innovación: 

## Prototipo Final
### Data de entrega: 
### Funcionalidades implementadas:
### Observacións: 
### Innovación: 




