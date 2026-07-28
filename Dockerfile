FROM php:8.1-apache

# Instalar extensiones PHP necesarias
RUN apt-get update && apt-get install -y \
    libmariadb-dev \
    default-mysql-client \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && a2enmod rewrite \
    && a2enmod headers \
    && rm -rf /var/lib/apt/lists/*

# Configurar Apache
RUN echo '<Directory /var/www/html>' > /etc/apache2/conf-available/app.conf && \
    echo '    Options Indexes FollowSymLinks' >> /etc/apache2/conf-available/app.conf && \
    echo '    AllowOverride All' >> /etc/apache2/conf-available/app.conf && \
    echo '    Require all granted' >> /etc/apache2/conf-available/app.conf && \
    echo '</Directory>' >> /etc/apache2/conf-available/app.conf && \
    a2enconf app

# Exportar variables de entorno a Apache
RUN echo 'SetEnv DB_HOST mysql' > /etc/apache2/conf-available/env.conf && \
    echo 'SetEnv DB_PORT 3306' >> /etc/apache2/conf-available/env.conf && \
    echo 'SetEnv DB_NAME abastospos' >> /etc/apache2/conf-available/env.conf && \
    echo 'SetEnv DB_USER abastospos' >> /etc/apache2/conf-available/env.conf && \
    echo 'SetEnv DB_PASSWORD abastospos123' >> /etc/apache2/conf-available/env.conf && \
    echo 'SetEnv APP_DEBUG 1' >> /etc/apache2/conf-available/env.conf && \
    a2enconf env

# Copiar aplicación
COPY . /var/www/html/

# Establecer permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

WORKDIR /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
