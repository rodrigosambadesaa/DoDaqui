FROM mysql:8.0

# Incluye esquema y datos semilla dentro de la imagen para evitar problemas de permisos
# cuando el host no permite bind mounts (equipos con políticas restrictivas).
COPY init.sql /docker-entrypoint-initdb.d/init.sql
