# Configuración básica de Docker no proxecto

## Ficheiros principais

- `docker-compose.yml`: define os servizos principais (web PHP, base de datos MySQL e phpMyAdmin), redes e volumes.
- `docker-compose.restricted.yml`: variante pensada para contornas con limitacións.
- `backend/Dockerfile`: instrucións para construír a imaxe do backend.
- `docker/mysql/Dockerfile`: instrucións para construír a imaxe de MySQL.
- `docker/mysql/init.sql`: script de inicialización da base de datos.

## Fluxo básico de traballo

1. Construír e iniciar os contedores:

   ```bash
   docker compose up --build -d
   ```

2. Comprobar que os servizos están activos:

   ```bash
   docker compose ps
   ```

   Enderezos locais por defecto:

   - Aplicación web: `http://localhost:8080`
   - phpMyAdmin: `http://localhost:8081`

3. Ver logs dun servizo (exemplo backend):

   ```bash
   docker compose logs -f web
   ```

4. Acceder a phpMyAdmin para revisar táboas (`usuarios`, `pedidos`, `pedido_linas`) coas credenciais do proxecto.

5. Parar e eliminar contedores, rede e recursos asociados:

   ```bash
   docker compose down
   ```

## Conceptos clave

- **Imaxe**: plantilla inmutable coa aplicación e dependencias.
- **Contedor**: execución dunha imaxe.
- **Servizo**: definición dun compoñente no docker-compose (nome, porto, volumes, etc.).
- **Volume**: persistencia de datos entre reinicios dos contedores.
- **Rede**: comunicación interna entre servizos por nome.

## Recomendacións

- Mantén os segredos fóra do repositorio, usando variables de contorno (`.env`).
- Usa nomes de servizo en lugar de IPs fixas para conectar aplicación e base de datos.
- Se cambias Dockerfiles ou dependencias, reconstrúe co parámetro `--build`.
