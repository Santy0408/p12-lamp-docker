# P12 · Imagen LAMP propia con Docker

**Santy0408** · ASIR · SRI & SAD

Este repositorio tiene dos imágenes Docker hechas desde cero sobre **Debian 13 (Trixie)**. Las dos están publicadas en mi VPS detrás de **Nginx Proxy Manager**, con certificado de Let's Encrypt:

| Proyecto | Qué lleva | URL |
|---|---|---|
| `12.1-php` | Debian + Apache + PHP | https://php.macki4all.es |
| `12.2-lamp` | **L**inux + **A**pache + **M**ariaDB + **P**HP en **un solo contenedor** | https://lamp.macki4all.es |

En la imagen LAMP, la base de datos funciona **en segundo plano** dentro del contenedor y una página PHP muestra **todas las filas** de la tabla `practicas`.

---

## Estructura

```
.
├── 12.1-php/
│   ├── Dockerfile        # Debian + Apache + PHP
│   ├── index.php         # página con datos del sistema y de PHP
│   └── compose.yaml
├── 12.2-lamp/
│   ├── Dockerfile        # Debian + Apache + MariaDB + PHP
│   ├── entrypoint.sh     # arranca MariaDB (&) y luego Apache en primer plano
│   ├── init.sql          # tabla "practicas" y sus filas
│   ├── lamp-env.conf     # Apache: pasa DB_* a PHP
│   ├── web/index.php     # SELECT * FROM practicas
│   ├── web/qr.png        # QR a este repositorio
│   ├── compose.yaml
│   └── .env.ejemplo      # la contraseña real NO se sube
├── 12-vps_desplegar.sh
├── 12.3-pc_generar_qr.py   # genera el QR (ver apartado QR)
└── 12.3-qr_github.png
```

---

## Paso 1 · Dockerfile Debian + PHP (12.1)

Parto del ejemplo de clase:

1. `FROM debian:trixie-slim`: imagen base oficial y reducida.
2. `ENV DEBIAN_FRONTEND=noninteractive`: evita que `apt` haga preguntas durante el build.
3. `RUN apt-get install … apache2 php libapache2-mod-php …`:
   - todo va en **una sola capa**;
   - al final se borra la caché de apt para que la imagen pese menos.
4. `COPY index.php`: copia la web. Antes se borra el `index.html` de Apache.
5. `EXPOSE 80`: documenta el puerto. **No lo publica**: lo publica NPM.
6. `CMD ["apachectl","-D","FOREGROUND"]`: Apache en **primer plano**. Si el proceso principal termina, el contenedor se para.

```bash
cd 12.1-php
docker build -t p12/debian-php:1.0 .
docker run -d --name debian-php -p 8081:80 p12/debian-php:1.0   # prueba local: http://localhost:8081
```

## Paso 2 · Imagen LAMP (12.2)

Docker recomienda **un proceso por contenedor**. Aquí el reto es meter Apache y MariaDB en el mismo contenedor. Se resuelve con un **entrypoint**:

```bash
mariadbd --user=mysql ... &        # 1) MariaDB en SEGUNDO PLANO
# espera a que responda (mariadb-admin ping)
# 2) solo la primera vez: crea BD, usuario de solo lectura y carga init.sql
apachectl -D FOREGROUND &          # 3) Apache
trap '... mariadb-admin shutdown' TERM   # docker stop apaga la BD limpiamente
wait
```

Detalles del Dockerfile:

| Instrucción | Para qué |
|---|---|
| `ENV DB_NAME=lampdb DB_USER=lamp` | Valores por defecto. La **contraseña no va en la imagen**: llega por `.env` |
| `RUN apt-get install … mariadb-server` | Añade el servidor de BD al Apache + PHP del paso 1 |
| `COPY lamp-env.conf` + `a2enconf` | `PassEnv`: PHP puede leer `DB_*` con `getenv()` |
| `VOLUME /var/lib/mysql` | Los datos sobreviven a borrar y recrear el contenedor |
| `HEALTHCHECK … mariadb-admin ping` | `docker ps` muestra `healthy` si la BD responde |
| `ENTRYPOINT ["/docker-entrypoint/entrypoint.sh"]` | Arranque de los dos servicios |

La página `web/index.php`:
- se conecta por PDO a `localhost`, es decir, por el socket unix de MariaDB dentro del contenedor;
- ejecuta `SELECT * FROM practicas`;
- dibuja la tabla con **todas las columnas y filas** de forma dinámica.

```bash
cd 12.2-lamp
echo "DB_PASS=$(openssl rand -hex 16)" > .env
docker compose up -d --build
docker exec -it lamp mariadb lampdb -e "SELECT * FROM practicas;"
```

## Paso 3 · Despliegue en el VPS detrás de NPM

1. **DNS** (Nominalia): crear los registros A `php` y `lamp` que apuntan a la IP del VPS.
2. **Red compartida**: los dos compose usan la red externa `proxy`, la misma que NPM. No se publica ningún puerto (`expose`, no `ports`).
3. `./12-vps_desplegar.sh`: copia los proyectos a `~/stacks/php` y `~/stacks/lamp`, genera `.env`, ejecuta `docker compose up -d --build` y comprueba que NPM llega a los dos contenedores.
4. **En NPM → Proxy Hosts:**
   - `php.macki4all.es` → `http://debian-php:80`
   - `lamp.macki4all.es` → `http://lamp:80`
   - En los dos: SSL con Let's Encrypt, Force SSL y HTTP/2.

## Paso 4 · Comprobaciones

```bash
docker ps                                   # lamp (healthy) y debian-php Up
docker top lamp                             # mariadbd y apache2 en el mismo contenedor
curl -sI https://lamp.macki4all.es | head -3
```

## QR

![QR](12.2-lamp/web/qr.png)

El QR lleva a este repositorio. También aparece en la propia página LAMP.

### Cómo se ha generado

El QR se genera con la librería de Python **segno**, ejecutando el script `12.3-pc_generar_qr.py` en el PC:

```bash
pip install segno
python 12.3-pc_generar_qr.py
```

| Parámetro | Qué hace |
|---|---|
| `segno.make(URL, error="m")` | Codifica la URL del repositorio. Usa corrección de errores **M**, así que se sigue leyendo aunque esté dañado un 15 % del código |
| `scale=8` | Cada módulo (cuadradito) mide 8 px |
| `border=2` | Margen blanco que la cámara necesita para detectarlo |

Salen dos ficheros:
- `12.2-lamp/web/qr.png`: el Dockerfile lo mete en la imagen con `COPY web/ /var/www/html/`;
- `12.3-qr_github.png`: más grande, para la documentación.

En la página aparece dentro de un enlace: se puede **escanear** con el móvil o **pulsar**.

```html
<a href="https://github.com/Santy0408/p12-lamp-docker"><img src="qr.png" alt="QR"></a>
```

En Linux se puede hacer lo mismo con `qrencode`:

```bash
sudo apt install -y qrencode
qrencode -o qr.png -s 8 -l M "https://github.com/Santy0408/p12-lamp-docker"
```
