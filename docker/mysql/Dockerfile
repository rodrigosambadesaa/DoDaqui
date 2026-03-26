FROM mysql:8.0

# Incluye el script de inicialización dentro de la imagen para entornos sin bind mounts.
COPY init.sql /docker-entrypoint-initdb.d/init.sql
