FROM php:8.2-apache

# Instalamos extensiones mínimas para PDO MySQL y habilitamos rewrite para rutas limpias.
RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev \
    && docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Redirige DocumentRoot al directorio public para exponer solo punto de entrada API.
RUN sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf
# Permite override por .htaccess en caso de ampliar rutas en futuras iteraciones.
RUN sed -ri '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Carpeta de trabajo común para copiar/montar código backend.
WORKDIR /var/www/html
