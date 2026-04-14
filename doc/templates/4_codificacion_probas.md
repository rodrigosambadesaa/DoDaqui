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
- Base de xestión de carriño para fluxo a checkout.
- Realización de pedido
- Realización de valoracións de produtos
- Seguridade da información

### Observacións:
- Todo o contorno está dockerizado e arranca cun só comando: `docker compose up --build`.
- A base de datos SQL inicialízase automaticamente con `docker/mysql/init.sql`.
- Usuario de proba permanente para avaliación: `demo@tenda.gal` / `Demo1234!`.
- O rexistro esixe contrasinal forte: 10+ caracteres, maiúscula, minúscula, número e símbolo.

### Innovación:
- Contorno reproducible en calquera equipo con Docker sen instalación manual de dependencias.
- Interface visual de tenda aliñada co mockup e preparada para evolución por prototipos.

## Prototipo 2
### Data de entrega: Pendente de peche
### Funcionalidades implementadas:
- Filtrado e busca de produtos no catálogo.
- Consulta do historial de pedidos para usuarios rexistrados.
- Xestión do perfil de usuario.

### Observacións:
- Este prototipo engade máis dos requirimentos funcionais que quedan pendentes no documento de análise.
- A base técnica segue sendo PHP + Docker + MySQL, reutilizando o contorno do primeiro prototipo.

### Innovación:
- Navegación máis orientada á experiencia de compra real, con busca e filtros para atopar produtos antes de engadilos ao carriño.
- Separación clara entre catálogo público, conta de usuario e seguimento dos pedidos.
- Persistencia de información clave para dar continuidade ao uso da aplicación entre sesións.

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




