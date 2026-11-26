-- 12.2 - Datos iniciales de la BD del LAMP (se cargan solo la primera vez)
-- Tabla con las practicas de la asignatura: la pagina PHP muestra TODAS sus filas.
CREATE TABLE IF NOT EXISTS practicas (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  codigo      VARCHAR(5)   NOT NULL,
  titulo      VARCHAR(80)  NOT NULL,
  tecnologia  VARCHAR(80)  NOT NULL,
  maquina     VARCHAR(40)  NOT NULL,
  estado      ENUM('Hecha','En curso','Pendiente') NOT NULL DEFAULT 'Hecha'
);

INSERT INTO practicas (codigo, titulo, tecnologia, maquina, estado) VALUES
 ('P1',  'Llaves SSH',                          'ssh-keygen, Bitvise',              'VM local',          'Hecha'),
 ('P4',  'Servicio SSH',                        'OpenSSH, sshd_config',             'VM local', 'Hecha'),
 ('P8',  'Bastionado y observabilidad',         'PostgreSQL',                       'VM local',          'Hecha'),
 ('P9',  'Docker en la nube',                   'Docker, OCI',                      'Nube','Hecha'),
 ('P10', 'Self-hosted en VPS. Dominio',         'Nextcloud, Uptime Kuma',           'VPS',        'Hecha'),
 ('P11', 'El misterio de la cripta digital',    'WebDAV, MariaDB, tunel SSH',       'Nube','Hecha'),
 ('P12', 'SRI & SAD, que bonita union',         'Apache, nginx, certbot',           'VPS',        'Hecha'),
 ('P12', 'Proxy inverso grafico',               'Nginx Proxy Manager, osTicket',    'VPS',        'Hecha'),
 ('P12', 'Imagen LAMP propia',                  'Dockerfile, Apache, MariaDB, PHP', 'VPS',        'En curso');
