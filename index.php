<?php
// ──────────────────────────────────────────
//  abcMusic — Página de entrega de la canción (ES)
//  music.abcmusic.tech/{uuid}
// ──────────────────────────────────────────

$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$uuid = preg_replace('/[^a-f0-9\-]/i', '', $path);

if (strlen($uuid) !== 36) {
    http_response_code(404);
    die('Canción no encontrada.');
}

// Funil em espanhol (link "crea otra"). Troque pela variável SITE_URL no
// Easypanel quando o site novo estiver no ar; sem ela, cai no antigo.
define('SITE_URL', rtrim(getenv('SITE_URL') ?: 'https://abcmusic-quiz-us.netlify.app', '/'));
define('SUPABASE_URL',   'https://baltzukuszagxcgkfrpi.supabase.co');
define('SUPABASE_KEY',   'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImJhbHR6dWt1c3phZ3hjZ2tmcnBpIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzczMTg4MjMsImV4cCI6MjA5Mjg5NDgyM30.gcRHTzssV3OsbObvnpnbROrrpA8Dn6zZz9j_qDJdw0s');
define('SUPABASE_TABLE', 'presentes');

$api = SUPABASE_URL . '/rest/v1/' . SUPABASE_TABLE . '?uuid=eq.' . urlencode($uuid) . '&limit=1';
$ch  = curl_init($api);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'apikey: '        . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY,
    ],
]);
$resp = curl_exec($ch);
curl_close($ch);

$rows = json_decode($resp, true);
if (empty($rows)) {
    http_response_code(404);
    die('Canción no encontrada.');
}

$m         = $rows[0];
$titulo    = htmlspecialchars($m['titulo']    ?? 'Tu canción especial');
$audio_url = htmlspecialchars($m['audio_url'] ?? '');
$cover_url = htmlspecialchars($m['cover_url'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <meta property="og:title"       content="<?= $titulo ?> 🎵">
  <meta property="og:description" content="Una canción hecha solo para ti por abcMusic.">
  <meta property="og:image"       content="<?= $cover_url ?>">
  <meta name="theme-color"        content="#f6f8ee">
  <title><?= $titulo ?> — abcMusic</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      min-height: 100dvh;
      background: #f6f8ee;
      color: #24301d;
      font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, sans-serif;
      display: flex;
      flex-direction: column;
      align-items: center;
      overflow-x: hidden;
    }

    /* ── Blurred cover backdrop ── */
    .bg-blur {
      position: fixed;
      inset: 0;
      z-index: 0;
      background-image: url('<?= $cover_url ?>');
      background-size: cover;
      background-position: center;
      /* Tema claro (2026-09-28): a capa continua no fundo, só que como um
         véu suave sobre o creme — antes era escurecida a 12%. */
      filter: blur(90px) saturate(1.3);
      opacity: .22;
      transform: scale(1.2);
    }

    /* ── Page ── */
    .page {
      position: relative;
      z-index: 1;
      width: 100%;
      max-width: 480px;
      min-height: 100dvh;
      display: flex;
      flex-direction: column;
      padding: 0 28px 48px;
    }

    /* ── Top bar ── */
    .top-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 56px 0 28px;
    }
    .icon-btn {
      background: none;
      border: none;
      cursor: pointer;
      color: #24301d;
      padding: 6px;
      display: flex;
      align-items: center;
      opacity: 0.75;
      transition: opacity .15s;
      -webkit-tap-highlight-color: transparent;
    }
    .icon-btn:hover { opacity: 1; }
    .top-label {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: .18em;
      text-transform: uppercase;
      color: #24301d;
    }

    /* ── Cover ── */
    .cover-wrap {
      width: 100%;
      aspect-ratio: 1;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 24px 60px rgba(36,48,29,.22);
      margin-bottom: 36px;
      background: #e7f1d6;
    }
    .cover-wrap img {
      width: 100%; height: 100%;
      object-fit: cover;
      display: block;
    }
    .cover-placeholder {
      width: 100%; height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 88px;
    }

    /* ── Track info ── */
    .track-info {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin-bottom: 20px;
    }
    .track-text { flex: 1; min-width: 0; }
    .track-title {
      font-size: 22px;
      font-weight: 800;
      color: #24301d;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      margin-bottom: 4px;
      line-height: 1.2;
    }
    .track-sub {
      font-size: 14px;
      font-weight: 500;
      color: #74806a;
    }
    .heart-btn {
      background: none;
      border: none;
      cursor: pointer;
      color: #3f7d20;
      padding: 6px;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      transition: transform .1s;
      -webkit-tap-highlight-color: transparent;
    }
    .heart-btn:active { transform: scale(.88); }

    /* ── Progress bar ── */
    .progress-section { margin-bottom: 20px; }

    .progress-track-wrap {
      position: relative;
      height: 4px;
      cursor: pointer;
      margin-bottom: 6px;
      border-radius: 2px;
    }
    .progress-track-wrap::before {
      content: '';
      position: absolute;
      inset: -8px 0;
    }
    .progress-bg {
      position: absolute;
      inset: 0;
      background: #dfe6d2;
      border-radius: 2px;
    }
    .progress-fill {
      position: absolute;
      left: 0; top: 0; bottom: 0;
      width: 0%;
      background: #3f7d20;
      border-radius: 2px;
      pointer-events: none;
      transition: width .25s linear;
    }
    .progress-track-wrap:hover .progress-fill { background: #2d5e14; }
    .progress-thumb {
      position: absolute;
      top: 50%;
      left: 0%;
      transform: translate(-50%, -50%);
      width: 12px; height: 12px;
      background: #3f7d20;
      border-radius: 50%;
      opacity: 0;
      pointer-events: none;
      transition: opacity .15s;
    }
    .progress-track-wrap:hover .progress-thumb { opacity: 1; }

    .time-row {
      display: flex;
      justify-content: space-between;
      font-size: 11px;
      font-weight: 600;
      color: #74806a;
      font-variant-numeric: tabular-nums;
    }

    /* ── Controls ── */
    .controls {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 28px;
    }
    .ctrl-btn {
      background: none;
      border: none;
      cursor: pointer;
      color: #74806a;
      padding: 8px;
      display: flex;
      align-items: center;
      transition: color .15s, transform .1s;
      -webkit-tap-highlight-color: transparent;
    }
    .ctrl-btn:hover { color: #24301d; }
    .ctrl-btn:active { transform: scale(.9); }
    .ctrl-btn.active { color: #3f7d20; }
    .ctrl-btn.active::after {
      content: '';
      display: block;
      position: absolute;
      bottom: 0; left: 50%;
      transform: translateX(-50%);
      width: 4px; height: 4px;
      background: #3f7d20;
      border-radius: 50%;
    }
    .ctrl-btn { position: relative; }

    .play-btn {
      width: 64px; height: 64px;
      border-radius: 50%;
      background: #3f7d20;
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: transform .12s, background .12s;
      flex-shrink: 0;
      -webkit-tap-highlight-color: transparent;
    }
    .play-btn:hover { background: #2d5e14; transform: scale(1.05); }
    .play-btn:active { transform: scale(.95); }
    .play-btn svg { fill: #fff; }

    /* ── Volume ── */
    .volume-row {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 36px;
    }
    .vol-icon { color: #74806a; flex-shrink: 0; }
    .vol-slider-wrap {
      flex: 1;
      position: relative;
      height: 4px;
      cursor: pointer;
      border-radius: 2px;
    }
    .vol-slider-wrap::before {
      content: '';
      position: absolute;
      inset: -8px 0;
    }
    .vol-bg {
      position: absolute;
      inset: 0;
      background: #dfe6d2;
      border-radius: 2px;
    }
    .vol-fill {
      position: absolute;
      left: 0; top: 0; bottom: 0;
      width: 100%;
      background: #3f7d20;
      border-radius: 2px;
      pointer-events: none;
    }
    .vol-slider-wrap:hover .vol-fill { background: #2d5e14; }
    input.vol-input {
      position: absolute;
      inset: -10px 0;
      width: 100%;
      opacity: 0;
      cursor: pointer;
      z-index: 2;
      margin: 0;
    }

    /* ── Actions ── */
    .actions-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 32px;
      padding: 0 4px;
    }
    .action-icon-btn {
      background: none;
      border: none;
      cursor: pointer;
      color: #74806a;
      padding: 6px;
      display: flex;
      align-items: center;
      transition: color .15s;
      -webkit-tap-highlight-color: transparent;
    }
    .action-icon-btn:hover { color: #24301d; }

    /* ── Footer CTA ── */
    .brand {
      text-align: center;
      font-size: 15px;
      line-height: 1.5;
      font-weight: 600;
      color: #74806a;
      text-decoration: none;
      letter-spacing: .04em;
      margin-top: auto;
      padding: 8px 0;
      transition: color .15s;
    }
    .brand:hover { color: #24301d; }
    .brand span { color: #3f7d20; }

    audio { display: none; }
  </style>
</head>
<body>

<div class="bg-blur"></div>

<div class="page">

  <!-- Top bar -->
  <div class="top-bar">
    <button class="icon-btn" onclick="history.back()" aria-label="Volver">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="6 9 12 15 18 9"/>
      </svg>
    </button>
    <span class="top-label">Reproduciendo</span>
    <button class="icon-btn" aria-label="Más opciones" onclick="shareMusic(event)">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
        <circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/>
      </svg>
    </button>
  </div>

  <!-- Cover -->
  <div class="cover-wrap">
    <?php if ($cover_url): ?>
      <img src="<?= $cover_url ?>" alt="Portada de la canción" loading="eager">
    <?php else: ?>
      <div class="cover-placeholder">🎵</div>
    <?php endif; ?>
  </div>

  <!-- Track info -->
  <div class="track-info">
    <div class="track-text">
      <div class="track-title"><?= $titulo ?></div>
      <div class="track-sub">abcMusic</div>
    </div>
    <button class="heart-btn" id="heartBtn" onclick="toggleHeart()" aria-label="Me gusta">
      <svg id="heartIcon" width="26" height="26" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 21.593c-5.63-5.539-11-10.297-11-14.402 0-3.791 3.068-5.191 5.281-5.191 1.312 0 4.151.501 5.719 4.457 1.59-3.968 4.464-4.447 5.726-4.447 2.54 0 5.274 1.621 5.274 5.181 0 4.069-5.136 8.625-11 14.402z"/>
      </svg>
    </button>
  </div>

  <!-- Progress bar -->
  <div class="progress-section">
    <div class="progress-track-wrap" id="progressArea">
      <div class="progress-bg"></div>
      <div class="progress-fill" id="progressFill"></div>
      <div class="progress-thumb" id="progressThumb"></div>
    </div>
    <div class="time-row">
      <span id="timeNow">0:00</span>
      <span id="timeDur">0:00</span>
    </div>
  </div>

  <!-- Controls -->
  <div class="controls">
    <button class="ctrl-btn" id="shuffleBtn" aria-label="Aleatorio">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
        <path d="M10.59 9.17L5.41 4 4 5.41l5.17 5.17 1.42-1.41zM14.5 4l2.04 2.04L4 18.59 5.41 20 17.96 7.46 20 9.5V4h-5.5zm.33 9.41l-1.41 1.41 3.13 3.13L14.5 20H20v-5.5l-2.04 2.04-3.13-3.13z"/>
      </svg>
    </button>

    <button class="ctrl-btn" onclick="seek(-10)" aria-label="Retroceder 10 segundos">
      <svg width="30" height="30" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 5V1L7 6l5 5V7c3.31 0 6 2.69 6 6s-2.69 6-6 6-6-2.69-6-6H4c0 4.42 3.58 8 8 8s8-3.58 8-8-3.58-8-8-8z"/>
        <text x="12" y="16" text-anchor="middle" font-size="5.5" font-weight="bold" fill="currentColor" font-family="sans-serif">10</text>
      </svg>
    </button>

    <button class="play-btn" id="playBtn" onclick="togglePlay()" aria-label="Reproducir/Pausar">
      <svg id="iconPlay" width="30" height="30" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
      <svg id="iconPause" width="30" height="30" viewBox="0 0 24 24" style="display:none"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
    </button>

    <button class="ctrl-btn" onclick="seek(10)" aria-label="Adelantar 10 segundos">
      <svg width="30" height="30" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 5V1l5 5-5 5V7c-3.31 0-6 2.69-6 6s2.69 6 6 6 6-2.69 6-6h2c0 4.42-3.58 8-8 8s-8-3.58-8-8 3.58-8 8-8z"/>
        <text x="12" y="16" text-anchor="middle" font-size="5.5" font-weight="bold" fill="currentColor" font-family="sans-serif">10</text>
      </svg>
    </button>

    <button class="ctrl-btn" id="repeatBtn" aria-label="Repetir">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
        <path d="M7 7h10v3l4-4-4-4v3H5v6h2V7zm10 10H7v-3l-4 4 4 4v-3h12v-6h-2v4z"/>
      </svg>
    </button>
  </div>

  <!-- Volume -->
  <div class="volume-row">
    <svg class="vol-icon" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
      <path d="M18.5 12c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM5 9v6h4l5 5V4L9 9H5z"/>
    </svg>
    <div class="vol-slider-wrap">
      <div class="vol-bg"></div>
      <div class="vol-fill" id="volFill"></div>
      <input type="range" class="vol-input" id="volInput" min="0" max="100" value="100" aria-label="Volumen">
    </div>
    <svg class="vol-icon" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
      <path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/>
    </svg>
  </div>

  <!-- Actions row -->
  <div class="actions-row">
    <button class="action-icon-btn" aria-label="Dispositivos">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
        <path d="M21 3H3c-1.1 0-2 .9-2 2v3h2V5h18v13h-7v2h7c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-10 8H1c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h10c.55 0 1-.45 1-1V12c0-.55-.45-1-1-1zm-1 10H2v-8h8v8zm-4-1c.83 0 1.5-.67 1.5-1.5S7.83 17 7 17s-1.5.67-1.5 1.5S6.17 20 7 20z"/>
      </svg>
    </button>

    <button class="action-icon-btn" onclick="shareMusic(event)" aria-label="Compartir">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
        <path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92s2.92-1.31 2.92-2.92c0-1.61-1.31-2.92-2.92-2.92zM18 4c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zM6 13c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm12 7.02c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1z"/>
      </svg>
    </button>
  </div>

  <!-- CTA -->
  <a class="brand" href="<?= SITE_URL ?>/?utm_source=link_pagina_entrega" target="_blank" rel="noopener">
    ¿Te gustó? Crea otra en <span>abcMusic</span>
  </a>

</div>

<audio id="audio" src="<?= $audio_url ?>" preload="metadata"></audio>

<script>
  const audio         = document.getElementById('audio');
  const progressFill  = document.getElementById('progressFill');
  const progressThumb = document.getElementById('progressThumb');
  const timeNow       = document.getElementById('timeNow');
  const timeDur       = document.getElementById('timeDur');
  const iconPlay      = document.getElementById('iconPlay');
  const iconPause     = document.getElementById('iconPause');
  const volInput      = document.getElementById('volInput');
  const volFill       = document.getElementById('volFill');

  const trackTitle    = <?= json_encode($titulo) ?>;

  function fmt(s) {
    s = Math.floor(s || 0);
    return Math.floor(s / 60) + ':' + String(s % 60).padStart(2, '0');
  }

  audio.addEventListener('loadedmetadata', () => {
    timeDur.textContent = fmt(audio.duration);
  });

  audio.addEventListener('timeupdate', () => {
    if (!audio.duration) return;
    const pct = (audio.currentTime / audio.duration) * 100;
    progressFill.style.width  = pct + '%';
    progressThumb.style.left  = pct + '%';
    timeNow.textContent = fmt(audio.currentTime);
  });

  audio.addEventListener('ended', () => {
    iconPlay.style.display  = '';
    iconPause.style.display = 'none';
  });

  function togglePlay() {
    if (audio.paused) {
      audio.play();
      iconPlay.style.display  = 'none';
      iconPause.style.display = '';
    } else {
      audio.pause();
      iconPlay.style.display  = '';
      iconPause.style.display = 'none';
    }
  }

  function seek(delta) {
    audio.currentTime = Math.max(0, Math.min(audio.duration || 0, audio.currentTime + delta));
  }

  document.getElementById('progressArea').addEventListener('click', function(e) {
    if (!audio.duration) return;
    const rect = this.getBoundingClientRect();
    audio.currentTime = ((e.clientX - rect.left) / rect.width) * audio.duration;
  });

  // Volume
  volInput.addEventListener('input', function() {
    audio.volume = this.value / 100;
    volFill.style.width = this.value + '%';
  });

  // Repeat
  let isRepeat = false;
  document.getElementById('repeatBtn').addEventListener('click', function() {
    isRepeat = !isRepeat;
    audio.loop = isRepeat;
    this.classList.toggle('active', isRepeat);
  });

  // Shuffle (visual only — single track)
  document.getElementById('shuffleBtn').addEventListener('click', function() {
    this.classList.toggle('active');
  });

  // Like
  let hearted = false;
  function toggleHeart() {
    hearted = !hearted;
    document.getElementById('heartIcon').style.color = hearted ? '#3f7d20' : '#3f7d20';
    document.getElementById('heartBtn').style.transform = 'scale(.88)';
    setTimeout(() => document.getElementById('heartBtn').style.transform = '', 150);
  }

  // Share
  function shareMusic(e) {
    if (navigator.share) {
      navigator.share({
        title: trackTitle,
        text: '¡Escucha esta canción especial hecha por abcMusic!',
        url: window.location.href
      }).catch(() => {});
    } else {
      const btn = e && e.currentTarget;
      navigator.clipboard.writeText(window.location.href).then(() => {
        if (!btn) return;
        btn.style.color = '#3f7d20';
        setTimeout(() => btn.style.color = '', 1500);
      }).catch(() => {
        alert('Copia el enlace: ' + window.location.href);
      });
    }
  }
</script>
</body>
</html>
