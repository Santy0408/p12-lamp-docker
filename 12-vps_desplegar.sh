#!/bin/bash
# 12-vps_desplegar.sh - P12 punto 12 (VPS)
# Construye y levanta las dos imagenes propias detras de NPM:
#   12.1 Debian + PHP  -> ~/stacks/php   (php.macki4all.es  -> debian-php:80)
#   12.2 LAMP propio   -> ~/stacks/lamp  (lamp.macki4all.es -> lamp:80)
# Ejecutar desde la carpeta Eje12 copiada al VPS.
set -e
cd "$(dirname "$0")"

echo "[1/4] Copiar cada proyecto a su stack"
mkdir -p ~/stacks/php ~/stacks/lamp
cp -r 12.1-php/. ~/stacks/php/
cp -r 12.2-lamp/. ~/stacks/lamp/
sed -i 's/\r$//' ~/stacks/lamp/entrypoint.sh      # por si llega con finales CRLF de Windows

echo "[2/4] Contrasena de la BD del LAMP (solo si no existe .env)"
[ -f ~/stacks/lamp/.env ] || { echo "DB_PASS=$(openssl rand -hex 16)" > ~/stacks/lamp/.env; chmod 600 ~/stacks/lamp/.env; }

echo "[3/4] Construir y levantar 12.1 (Debian + PHP)"
(cd ~/stacks/php && docker compose up -d --build)

echo "[4/4] Construir y levantar 12.2 (LAMP)"
(cd ~/stacks/lamp && docker compose up -d --build)

sleep 8
docker images | grep -E 'p12/(debian-php|lamp)'
docker ps --filter name=debian-php --filter name=lamp --format 'table {{.Names}}\t{{.Status}}\t{{.Ports}}'
docker logs --tail 8 lamp
echo "Pruebas desde NPM (red proxy):"
docker exec npm curl -s -o /dev/null -w "debian-php:80 -> %{http_code}\n" http://debian-php:80
docker exec npm curl -s -o /dev/null -w "lamp:80       -> %{http_code}\n" http://lamp:80
