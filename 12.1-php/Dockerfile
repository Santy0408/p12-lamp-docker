# 12.1 - Imagen Debian + Apache + PHP (basada en el ejemplo de clase)
# Imagen base oficial de Debian 13 (Trixie) reducida
FROM debian:trixie-slim

LABEL maintainer="Santy0408" \
      description="P12.1 - Debian 13 + Apache + PHP"

# Evita preguntas interactivas durante la instalacion de paquetes
ENV DEBIAN_FRONTEND=noninteractive

# Apache, PHP (modulo de Apache) y extensiones comunes; se limpia la cache de apt para que pese menos
RUN apt-get update && apt-get install -y --no-install-recommends \
        apache2 \
        php \
        libapache2-mod-php \
        php-mysql \
        php-curl \
        php-mbstring \
        php-xml \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Seguridad: Apache sin version en cabeceras ni errores, y PHP sin cabecera X-Powered-By
COPY zz-seguridad.conf /etc/apache2/conf-available/zz-seguridad.conf
RUN a2enconf zz-seguridad     && sed -i 's/^expose_php = On/expose_php = Off/' /etc/php/*/apache2/php.ini

# Quitar la pagina por defecto de Apache y copiar nuestra web
RUN rm -f /var/www/html/index.html
COPY index.php /var/www/html/

# Permisos para el usuario de Apache
RUN chown -R www-data:www-data /var/www/html

# Puerto web estandar (informativo: lo publica NPM, no el host)
EXPOSE 80

# Apache en primer plano: si el proceso principal termina, el contenedor se para
CMD ["apachectl", "-D", "FOREGROUND"]
