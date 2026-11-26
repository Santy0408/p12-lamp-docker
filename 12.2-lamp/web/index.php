<?php
// 12.2 - Muestra TODAS las filas de la tabla "practicas" de la MariaDB que corre en el mismo contenedor
function env($k, $def = '') { $v = getenv($k); return $v !== false ? $v : ($_SERVER[$k] ?? $def); }

$db    = env('DB_NAME', 'lampdb');
$user  = env('DB_USER', 'lamp');
$pass  = env('DB_PASS');
$filas = []; $cols = []; $error = null; $version = '';

try {
    // host=localhost -> PHP usa el socket unix de MariaDB (/run/mysqld/mysqld.sock)
    $pdo = new PDO("mysql:host=localhost;dbname=$db;charset=utf8mb4", $user, $pass,
                   [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
    $filas   = $pdo->query('SELECT * FROM practicas ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    $cols    = $filas ? array_keys($filas[0]) : [];
} catch (PDOException $e) {
    $error = true;
    error_log('[lamp] ' . $e->getMessage());   // el detalle va al log del contenedor, no al visitante
}
$os = @parse_ini_file('/etc/os-release');
$h  = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>P12.2 · LAMP propio</title>
<style>
  :root{--bg:#0b1020;--card:#141b2e;--line:#26304a;--txt:#e6e9f2;--mut:#8f9bb8;--a1:#f97316;--a2:#ec4899;--a3:#3b82f6;--a4:#22c55e}
  *{box-sizing:border-box}
  body{margin:0;font-family:system-ui,Segoe UI,Arial,sans-serif;color:var(--txt);
       background:radial-gradient(1000px 500px at 10% -10%,#3b1d4a 0,transparent 60%),radial-gradient(900px 500px at 100% 0,#10324a 0,transparent 55%),var(--bg);min-height:100vh}
  .wrap{max-width:1050px;margin:0 auto;padding:32px 16px 48px}
  header{display:flex;gap:24px;align-items:center;justify-content:space-between;flex-wrap:wrap}
  h1{margin:0;font-size:clamp(1.8rem,4vw,2.6rem);line-height:1.1}
  h1 b{background:linear-gradient(90deg,var(--a1),var(--a2),var(--a3));-webkit-background-clip:text;background-clip:text;color:transparent}
  .sub{color:var(--mut);margin:8px 0 0}
  .stack{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin:28px 0}
  .pill{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:14px}
  .pill .l{font-size:2rem;font-weight:800}
  .pill .n{color:var(--mut);font-size:.85rem}
  .pill:nth-child(1) .l{color:var(--a4)}.pill:nth-child(2) .l{color:var(--a2)}
  .pill:nth-child(3) .l{color:var(--a3)}.pill:nth-child(4) .l{color:var(--a1)}
  .card{background:var(--card);border:1px solid var(--line);border-radius:16px;overflow:hidden}
  .card h2{margin:0;padding:16px 18px;font-size:1.05rem;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap}
  .card h2 small{color:var(--mut);font-weight:400}
  .scroll{overflow-x:auto}
  table{width:100%;border-collapse:collapse;font-size:.95rem}
  th,td{padding:10px 14px;text-align:left;white-space:nowrap;border-bottom:1px solid var(--line)}
  th{color:var(--mut);font-weight:600;text-transform:uppercase;font-size:.75rem;letter-spacing:.04em}
  tr:hover td{background:#1a2340}
  .e{border-radius:999px;padding:2px 10px;font-size:.8rem}
  .e-Hecha{background:#123d26;color:#86efac}.e-En{background:#3d2a12;color:#fdba74}.e-Pendiente{background:#3d1220;color:#fda4af}
  .err{background:#3d1220;border:1px solid #7f1d1d;color:#fecaca;padding:16px;border-radius:12px}
  .qr{display:flex;gap:14px;align-items:center;background:#fff;color:#111;border-radius:14px;padding:10px 14px 10px 10px;text-decoration:none}
  .qr img{width:96px;height:96px;display:block}
  .qr span{font-size:.85rem;max-width:140px}
  footer{margin-top:24px;color:var(--mut);font-size:.85rem;text-align:center}
  @media (max-width:640px){.stack{grid-template-columns:repeat(2,1fr)}}
</style>
</head>
<body>
<div class="wrap">
  <header>
    <div>
      <h1>Mi <b>LAMP</b> en Docker</h1>
      <p class="sub">Imagen propia: un único contenedor con Debian, Apache, MariaDB y PHP</p>
    </div>
    <a class="qr" href="https://github.com/Santy0408/p12-lamp-docker" target="_blank" rel="noopener">
      <img src="qr.png" alt="QR al repositorio de GitHub">
      <span><b>Paso a paso en GitHub</b><br>Escanea o pulsa</span>
    </a>
  </header>

  <section class="stack">
    <div class="pill"><div class="l">L</div><div class="n">Debian <?= $h($os['VERSION_ID'] ?? '') ?></div></div>
    <div class="pill"><div class="l">A</div><div class="n">Apache 2.4</div></div>
    <div class="pill"><div class="l">M</div><div class="n">MariaDB <?= $h(implode('.', array_slice(explode('.', explode('-', $version)[0]), 0, 1)) ?: '—') ?></div></div>
    <div class="pill"><div class="l">P</div><div class="n">PHP <?= PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION ?></div></div>
  </section>

  <?php if ($error): ?>
    <div class="err"><b>No se pudo leer la base de datos.</b></div>
  <?php else: ?>
  <div class="card">
    <h2><span>Tabla <code>practicas</code> de la BD <code><?= $h($db) ?></code></span>
        <small><?= count($filas) ?> filas · SELECT * FROM practicas</small></h2>
    <div class="scroll">
      <table>
        <tr><?php foreach ($cols as $c): ?><th><?= $h($c) ?></th><?php endforeach; ?></tr>
        <?php foreach ($filas as $f): ?>
          <tr>
          <?php foreach ($f as $c => $v): ?>
            <td><?php if ($c === 'estado'): ?><span class="e e-<?= $h(explode(' ', $v)[0]) ?>"><?= $h($v) ?></span><?php else: ?><?= $h($v) ?><?php endif; ?></td>
          <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <footer>P12 · SRI &amp; SAD · ASIR · imagen LAMP propia</footer>
</div>
</body>
</html>
