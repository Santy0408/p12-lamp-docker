#!/bin/bash
# 12.2 - Arranque del contenedor LAMP
#  1) MariaDB en SEGUNDO PLANO (&)
#  2) Primera vez: crea la BD, el usuario (solo lectura) y la tabla con datos
#  3) Apache en primer plano; al parar el contenedor se apagan los dos de forma limpia
set -e
: "${DB_PASS:?Falta la variable DB_PASS (ponla en el fichero .env)}"

mkdir -p /run/mysqld && chown mysql:mysql /run/mysqld

# Volumen vacio -> inicializar el directorio de datos
if [ ! -d /var/lib/mysql/mysql ]; then
  echo "[lamp] Inicializando el directorio de datos de MariaDB"
  chown -R mysql:mysql /var/lib/mysql
  mariadb-install-db --user=mysql --datadir=/var/lib/mysql > /dev/null
fi

echo "[lamp] Arrancando MariaDB en segundo plano"
mariadbd --user=mysql --datadir=/var/lib/mysql --bind-address=127.0.0.1 &

for i in $(seq 1 30); do
  mariadb-admin ping --silent 2>/dev/null && break
  sleep 1
done
mariadb-admin ping --silent || { echo "[lamp] MariaDB no arranca"; exit 1; }

if [ ! -f /var/lib/mysql/.p12_inicializada ]; then
  echo "[lamp] Primera ejecucion: creando BD '$DB_NAME', usuario '$DB_USER' y tabla"
  mariadb <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT SELECT ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL
  mariadb "$DB_NAME" < /docker-entrypoint/init.sql
  touch /var/lib/mysql/.p12_inicializada
fi

echo "[lamp] Arrancando Apache en primer plano"
apachectl -D FOREGROUND &
APACHE_PID=$!

# docker stop envia SIGTERM: parar Apache y apagar MariaDB sin corromper datos
trap 'echo "[lamp] Parando..."; apachectl -k graceful-stop; mariadb-admin shutdown; exit 0' TERM INT
wait $APACHE_PID
