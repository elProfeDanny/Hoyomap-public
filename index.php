<?php
require_once 'db.php';
date_default_timezone_set('America/Santiago');

$total = $reparados = $vecinos = 0;
try {
    $r = $mysqli->query("SELECT COUNT(*) as t FROM reportes")->fetch_assoc();
    $total = $r['t'] ?? 0;
    $r = $mysqli->query("SELECT COUNT(*) as r FROM reportes WHERE estado='reparado' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_assoc();
    $reparados = $r['r'] ?? 0;
    $r = $mysqli->query("SELECT COUNT(DISTINCT reportante_id) as v FROM reportes WHERE reportante_id IS NOT NULL")->fetch_assoc();
    $vecinos = $r['v'] ?? 0;
} catch(Exception $e) {}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>HoyoMap • Chile sin baches</title>
  <meta name="description" content="Reporta hoyos con foto y GPS. IA predictiva. Municipalidades actúan más rápido. Postulando Corfo 2025-2026.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <style>
    :root{--bg:#fafafa;--text:#111;--muted:#666;--primary:#0066ff;--accent:#ff5a00;--success:#00c853}
    *,*::before,*::after{box-sizing:border-box}
    body{margin:0;font-family:'Inter',system-ui,sans-serif;background:var(--bg);color:var(--text);line-height:1.6}
    .wrap{max-width:1280px;margin:0 auto;padding:0 24px}
    header{position:sticky;top:0;background:white;z-index:100;box-shadow:0 1px 0 #eee}
    .nav{height:72px;display:flex;align-items:center;justify-content:space-between}
    .logo{font-size:24px;font-weight:800;color:var(--primary)}
    .logo span{color:var(--accent)}
    nav{display:flex;gap:32px;align-items:center}
    nav a{color:var(--text);text-decoration:none;font-weight:500;font-size:15px}
    nav a:hover{color:var(--primary)}
    .btn{background:var(--primary);color:white;padding:12px 24px;border-radius:12px;font-weight:600;font-size:15px;text-decoration:none;display:inline-block}
    .btn.accent{background:var(--accent)}
    .btn:hover{transform:translateY(-2px);box-shadow:0 10px 20px rgba(0,102,255,0.2)}

    .hero{padding:120px 0 80px;text-align:center}
    .hero h1{font-size:56px;font-weight:800;line-height:1.1;margin:0 0 24px}
    .hero p{font-size:21px;color:var(--muted);max-width:720px;margin:0 auto 40px}
    .actions{display:flex;gap:16px;justify-content:center;flex-wrap:wrap;margin:40px 0}
    .stats{display:flex;gap:48px;justify-content:center;margin:80px 0;flex-wrap:wrap}
    .stat{text-align:center}
    .stat strong{font-size:42px;font-weight:800;display:block;color:var(--primary)}
    .stat span{font-size:15px;color:var(--muted);text-transform:uppercase;letter-spacing:1px}

    #map{height:560px;border-radius:20px;overflow:hidden;box-shadow:0 20px 40px rgba(0,0,0,0.1);margin:80px 0}

    .fab{position:fixed;bottom:24px;right:24px;width:64px;height:64px;background:var(--accent);color:white;border-radius:50%;display:grid;place-items:center;font-size:36px;box-shadow:0 10px 30px rgba(255,90,0,0.4);z-index:99;cursor:pointer}
    .fab:hover{transform:scale(1.1)}

    footer{padding:60px 0;text-align:center;color:var(--muted);font-size:14px}

    @media(max-width:768px){
      .hero h1{font-size:40px}
      .hero p{font-size:18px}
      .actions{flex-direction:column;align-items:center}
      .stats{gap:32px}
      nav{gap:16px;font-size:14px}
      nav a:not(.btn){display:none}
    }
  </style>
</head>
<body>

<header>
  <div class="wrap nav">
    <div class="logo">Hoyo<span>Map</span></div>
    <nav>
      <a href="#mapa">Mapa</a>
      <a href="reportar.php">Reportar</a>
      <a href="login.php">Autoridades</a>
      <a href="blog.php">Blog</a>
      <a href="reportar.php" class="btn accent">Reportar Hoyo</a>
    </nav>
  </div>
</header>

<main class="wrap">
  <section class="hero">
    <h1>Chile sin baches<br><span style="color:var(--primary)">empieza contigo</span></h1>
    <p>Reporta hoyos con una foto y tu ubicación. Usamos IA para predecir daños y ayudar a las municipalidades a reparar más rápido.</p>
    
    <div class="actions">
      <a href="reportar.php" class="btn accent" style="font-size:18px;padding:16px 36px">Reportar en 20 segundos</a>
      <a href="#mapa" class="btn" style="background:transparent;color:var(--primary);border:2px solid var(--primary)">Ver mapa en vivo</a>
    </div>

    <div class="stats">
      <div class="stat"><strong><?=number_format($total)?></strong><span>Reportes</span></div>
      <div class="stat"><strong><?=number_format($reparados)?></strong><span>Reparados (30d)</span></div>
      <div class="stat"><strong><?=number_format($vecinos)?></strong><span>Vecinos activos</span></div>
    </div>
  </section>

  <section id="mapa">
    <h2 style="text-align:center;font-size:36px;margin-bottom:16px">Mapa de calor en tiempo real</h2>
    <p style="text-align:center;color:var(--muted);margin-bottom:40px">Rojo = zonas críticas • La IA prioriza automáticamente</p>
    <div id="map"></div>
  </section>
</main>

<a href="reportar.php" class="fab">hole</a>

<footer>
  <p>© <?=date('Y')?> HoyoMap • Innovación social vial • Postulando a Corfo, Start-Up Chile y fondos internacionales</p>
</footer>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
<script>
const map = L.map('map').setView([-33.49, -70.75], 11);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom:19}).addTo(map);

fetch('api/reports_fixed.php')
  .then(r => r.json())
  .then(data => {
    const heat = data.filter(r => r.lat && r.lng).map(r => [r.lat, r.lng, r.severidad/5]);
    if(heat.length) L.heatLayer(heat, {radius:25, blur:18}).addTo(map);

    data.forEach(r => {
      if(!r.lat || !r.lng) return;
      const color = r.severidad >= 4 ? '#d32f2f' : r.severidad >= 3 ? '#ff9800' : '#43a047';
      L.circleMarker([r.lat, r.lng], {radius:9, color, fillOpacity:0.9, weight:2})
       .bindPopup(`<b>${r.tipo}</b><br>${r.address||'Sin dirección'}<br><small>${new Date(r.created_at).toLocaleDateString('es-CL')}</small>`)
       .addTo(map);
    });
  });
</script>
</body>
</html>