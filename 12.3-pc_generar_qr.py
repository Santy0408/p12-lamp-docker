# 12.3-pc_generar_qr.py - P12 punto 12.3
# Genera el QR que lleva al repositorio de GitHub con el paso a paso.
# Se ejecuta en el PC (Windows), desde la carpeta Eje12:
#   pip install segno
#   python 12.3-pc_generar_qr.py
import segno

URL = "https://github.com/Santy0408/p12-lamp-docker"

# error="m": correccion de errores nivel M (se sigue leyendo con ~15 % del codigo danado)
qr = segno.make(URL, error="m")

# QR pequeno para la web: el Dockerfile lo mete en la imagen con "COPY web/ /var/www/html/"
qr.save("12.2-lamp/web/qr.png", scale=8, border=2)    # 296 x 296 px

# QR grande para el documento de la practica
qr.save("12.3-qr_github.png", scale=10, border=3)

print("QR generado ->", URL)
