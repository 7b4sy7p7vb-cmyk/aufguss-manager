<?php
require_once 'config.php';
session_start();
if (empty($_SESSION['ok']) || empty($_SESSION['exp']) || $_SESSION['exp'] < time()) {
    header('Location: login.php'); exit;
}
$_SESSION['exp'] = time() + SESSION_TIMEOUT;
if (isset($_GET['logout'])) { session_destroy(); header('Location: login.php'); exit; }
?><!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>SaunaManager — Dashboard</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',sans-serif;background:#f3f4f6;min-height:100vh}
.hdr{background:#1a1d23;padding:16px 24px;display:flex;align-items:center;justify-content:space-between}
.hdr-l{display:flex;align-items:center;gap:12px}
.ico{width:36px;height:36px;background:#2563eb;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:18px}
.hdr h1{color:#fff;font-size:18px;font-weight:700}
.hdr sub{color:#9ca3af;font-size:12px;display:block}
.out{color:#9ca3af;text-decoration:none;font-size:13px;padding:6px 12px;border:1px solid #374151;border-radius:6px}
.out:hover{color:#fff;border-color:#6b7280}
.main{max-width:1100px;margin:0 auto;padding:28px 20px}
.welcome{background:#fff;border-radius:12px;padding:24px;margin-bottom:24px;border-left:4px solid #2563eb;box-shadow:0 1px 3px rgba(0,0,0,.08)}
.welcome h2{font-size:20px;font-weight:700;color:#1a1d23;margin-bottom:6px}
.welcome p{color:#6b7280;font-size:14px}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;margin-bottom:24px}
.card{background:#fff;border-radius:12px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.08)}
.card-ico{font-size:28px;margin-bottom:10px}
.card h3{font-size:15px;font-weight:700;color:#1a1d23;margin-bottom:5px}
.card p{font-size:13px;color:#6b7280;line-height:1.5}
.center{text-align:center;padding:20px 0}
.btn{display:inline-flex;align-items:center;gap:8px;padding:14px 32px;background:#2563eb;color:#fff;border-radius:10px;font-size:16px;font-weight:700;text-decoration:none;box-shadow:0 4px 14px rgba(37,99,235,.3)}
.btn:hover{background:#1d4ed8}
.note{background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:10px 14px;font-size:13px;color:#16a34a;margin-top:12px;display:inline-block}
.info{background:#fff;border-radius:12px;padding:18px;margin-top:20px;box-shadow:0 1px 3px rgba(0,0,0,.08)}
.info h3{font-size:14px;font-weight:700;margin-bottom:10px;color:#1a1d23}
.row{display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid #f3f4f6;font-size:13px}
.row:last-child{border-bottom:none}
.row span:first-child{color:#6b7280}
.row a{color:#2563eb;text-decoration:none}
</style>
</head>
<body>
<div class="hdr">
  <div class="hdr-l">
    <div class="ico">🧖</div>
    <div><h1>SaunaManager</h1><sub>Professionelles Aufguss-Management</sub></div>
  </div>
  <a href="?logout=1" class="out">⎋ Abmelden</a>
</div>
<div class="main">
  <div class="welcome">
    <h2>👋 Willkommen zurück!</h2>
    <p>Verwalte deine Aufgüsse, Düfte und Mitarbeiter — auf allen Geräten synchronisiert.</p>
  </div>
  <div class="grid">
    <div class="card"><div class="card-ico">✏️</div><h3>Tagesplan & Eingabe</h3><p>Aufgüsse erfassen, Düfte zuweisen, Gästezahlen eintragen.</p></div>
    <div class="card"><div class="card-ico">🧴</div><h3>Duft-Lager</h3><p>Bestand überwachen, Bestellliste erstellen, Preise verwalten.</p></div>
    <div class="card"><div class="card-ico">📊</div><h3>Auswertungen</h3><p>Gäste-Trends, Stoßzeiten und Kostenübersicht.</p></div>
    <div class="card"><div class="card-ico">⭐</div><h3>Bewertungen</h3><p>Anonymes Feedback direkt nach dem Aufguss sammeln.</p></div>
    <div class="card"><div class="card-ico">📅</div><h3>Dienstplan</h3><p>Mitarbeiter planen, Feiertage automatisch berücksichtigt.</p></div>
    <div class="card"><div class="card-ico">🌸</div><h3>Duft-Berater</h3><p>40 Kombinationsempfehlungen mit Überraschungsgenerator.</p></div>
  </div>
  <div class="center">
    <a href="app.php" class="btn">🚀 App öffnen</a>
    <div class="note">✓ Alle Geräte synchronisiert</div>
  </div>
  <div class="info">
    <h3>⚙️ Einstellungen</h3>
    <div class="row"><span>Passwort ändern</span><a href="change_password.php">→ Ändern</a></div>
    <div class="row"><span>Angemeldet als</span><span>admin</span></div>
    <div class="row"><span>Session gültig bis</span><span><?= date('H:i \U\h\r', $_SESSION['exp']) ?></span></div>
  </div>
</div>
</body></html>
