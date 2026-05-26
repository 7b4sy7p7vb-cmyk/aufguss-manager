<?php
require_once 'config.php';
session_start();
if (!empty($_SESSION['ok']) && !empty($_SESSION['exp']) && $_SESSION['exp'] > time()) {
    header('Location: dashboard.php'); exit;
}
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (hash('sha256', $_POST['pw'] ?? '') === LOGIN_HASH) {
        $_SESSION['ok'] = true;
        $_SESSION['exp'] = time() + SESSION_TIMEOUT;
        header('Location: dashboard.php'); exit;
    }
    $err = 'Falsches Passwort';
    sleep(1);
}
?><!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>SaunaManager — Login</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',sans-serif;background:linear-gradient(135deg,#1a1d23,#2d3748);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.box{background:#fff;border-radius:16px;padding:40px;width:100%;max-width:380px;box-shadow:0 25px 60px rgba(0,0,0,.4)}
.ico{width:60px;height:60px;background:#2563eb;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:28px;margin:0 auto 12px}
h1{text-align:center;font-size:22px;color:#1a1d23;margin-bottom:4px}
p{text-align:center;font-size:13px;color:#6b7280;margin-bottom:24px}
label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#374151;margin-bottom:5px}
input{width:100%;padding:12px 14px;border:2px solid #e5e7eb;border-radius:8px;font-size:15px;outline:none;margin-bottom:14px}
input:focus{border-color:#2563eb}
button{width:100%;padding:13px;background:#2563eb;border:none;border-radius:8px;color:#fff;font-size:15px;font-weight:700;cursor:pointer}
button:hover{background:#1d4ed8}
.err{background:#fef2f2;border:1px solid #fecaca;color:#dc2626;border-radius:7px;padding:10px;font-size:13px;text-align:center;margin-bottom:14px}
</style>
</head>
<body>
<div class="box">
  <div class="ico">🧖</div>
  <h1>SaunaManager</h1>
  <p>Professionelles Aufguss-Management</p>
  <?php if($err): ?><div class="err">⚠ <?=htmlspecialchars($err)?></div><?php endif ?>
  <form method="POST">
    <label>Passwort</label>
    <input type="password" name="pw" autofocus required placeholder="••••••••">
    <button type="submit">→ Anmelden</button>
  </form>
</div>
</body></html>
