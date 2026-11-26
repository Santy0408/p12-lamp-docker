<?php
// 12.1 - Pagina de prueba: demuestra que PHP se ejecuta dentro del contenedor Debian
$os = @parse_ini_file('/etc/os-release');
$datos = [
  'Sistema'           => $os['PRETTY_NAME'] ?? php_uname('s'),
  'Servidor web'      => 'Apache',
  'Version de PHP'    => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
  'Interfaz PHP'      => php_sapi_name(),
  'Extensiones'       => implode(', ', array_intersect(['curl','mbstring','xml','mysqli','pdo_mysql'], get_loaded_extensions())),
  'Protocolo'         => ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'http'),
];
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>P12.1 · Debian + PHP</title>
<style>
  :root{--bg:#0f172a;--card:#1e293b;--txt:#e2e8f0;--mut:#94a3b8;--acc:#8b5cf6;--ok:#22c55e}
  *{box-sizing:border-box}
  body{margin:0;font-family:system-ui,Segoe UI,Arial,sans-serif;background:radial-gradient(circle at 20% 0,#312e81 0,var(--bg) 55%);color:var(--txt);min-height:100vh;display:grid;place-items:center;padding:16px}
  .card{background:var(--card);border:1px solid #334155;border-radius:16px;max-width:720px;width:100%;padding:28px;box-shadow:0 20px 50px #0007}
  h1{margin:0 0 4px;font-size:1.8rem}
  h1 span{background:linear-gradient(90deg,#a78bfa,#38bdf8);-webkit-background-clip:text;background-clip:text;color:transparent}
  p{color:var(--mut);margin:0 0 20px}
  table{width:100%;border-collapse:collapse}
  td{padding:10px 8px;border-bottom:1px solid #334155;vertical-align:top}
  td:first-child{color:var(--mut);width:40%}
  .ok{display:inline-block;background:#14532d;color:#bbf7d0;border-radius:999px;padding:2px 10px;font-size:.85rem}
  footer{margin-top:18px;color:var(--mut);font-size:.85rem}
</style>
</head>
<body>
  <div class="card">
    <h1>🐘 <span>PHP funcionando en Docker</span></h1>
    <p>Imagen propia <code>debian:trixie-slim</code> + Apache + PHP <span class="ok">OK</span></p>
    <table>
      <?php foreach ($datos as $k => $v): ?>
        <tr><td><?= htmlspecialchars($k) ?></td><td><?= htmlspecialchars((string)$v) ?></td></tr>
      <?php endforeach; ?>
    </table>
    <footer>P12 · SRI &amp; SAD · ASIR</footer>
  </div>
</body>
</html>
