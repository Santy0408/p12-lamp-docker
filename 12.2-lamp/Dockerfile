# 12.2 - Imagen LAMP propia en un solo contenedor: Linux (Debian 13) + Apache + MariaDB + PHP
FROM debian:trixie-slim

LABEL maintainer="Santy0408" \
      description="P12.2 - LAMP: Debian 13 + Apache + MariaDB + PHP" \
      version="1.0"

# Sin preguntas en apt. Nombre de la BD y usuario por defecto (la contrasena NUNCA va en la imagen: llega por .env)
ENV DEBIAN_FRONTEND=noninteractive \
    DB_NAME=lampdb \
    DB_USER=lamp

# Apache + PHP + extension MySQL + servidor MariaDB, en una sola capa y sin cache de apt
RUN apt-get update && apt-get install -y --no-install-recommends \
        apache2 \
        php \
        libapache2-mod-php \
        php-mysql \
        mariadb-server \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Apache: pasa DB_* a PHP, no muestra versiones (zz-seguridad) y quita la pagina por defecto
COPY lamp-env.conf /etc/apache2/conf-available/lamp-env.conf
COPY zz-seguridad.conf /etc/apache2/conf-available/zz-seguridad.conf
RUN a2enconf lamp-env zz-seguridad     && sed -i 's/^expose_php = On/expose_php = Off/' /etc/php/*/apache2/php.ini     && rm -f /var/www/html/index.html

# Web (index.php + QR) y script de arranque con el SQL inicial
COPY web/ /var/www/html/
COPY entrypoint.sh init.sql /docker-entrypoint/
RUN chmod +x /docker-entrypoint/entrypoint.sh \
    && chown -R www-data:www-data /var/www/html

# Los datos de MariaDB viven en un volumen: sobreviven a borrar/recrear el contenedor
VOLUME /var/lib/mysql

EXPOSE 80

# Sano = MariaDB responde
HEALTHCHECK --interval=30s --timeout=5s --start-period=20s \
    CMD mariadb-admin ping --silent || exit 1

# El entrypoint arranca MariaDB en segundo plano y Apache en primer plano
ENTRYPOINT ["/docker-entrypoint/entrypoint.sh"]
