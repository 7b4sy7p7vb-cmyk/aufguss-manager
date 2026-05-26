<?php
require_once 'config.php';
session_start();
if (empty($_SESSION['ok'])) { header('Location: login.php'); exit; }
$msg=''; $err='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $cur=$_POST['cur']??''; $n1=$_POST['n1']??''; $n2=$_POST['n2']??'';
    if (hash('sha256',$cur)!==LOGIN_HASH) { $err='Aktuelles Passwort falsch'; }
    elseif (strlen($n1)<8) { $err='Mindestens 8 Zeichen'; }
    elseif ($n1!==$n2) { $err='Passwörter stimmen nicht überein'; }
    else {
        $h=hash('sha256',$n1);
        $c=file_get_contents('config.php');
        $c=preg_replace("/define\('LOGIN_HASH',.*?\);/","define('LOGIN_HASH', '$h');",$c);
        file_put_contents('config.php',$c);
        $msg='Passwort geändert!';
    }
}
?><!DOCTYPE html>
<html lang="de"><head><meta charset="UTF-8"><title>Passwort ändern</title>
<style>body{font-family:'Segoe UI',sans-serif;background:#f3f4f6;display:flex;align-items:center;justify-content:center;min-height:100vh}
.box{background:#fff;padding:36px;border-radius:16px;width:100%;max-width:400px;box-shadow:0 4px 20px rgba(0,0,0,.1)}
h1{font-size:20px;margin-bottom:20px} label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;color:#374151;margin-bottom:5px}
input{width:100%;padding:10px 12px;border:2px solid #e5e7eb;border-radius:7px;font-size:14px;outline:none;margin-bottom:12px}
input:focus{border-color:#2563eb} button{width:100%;padding:12px;background:#2563eb;border:none;border-radius:7px;color:#fff;font-size:14px;font-weight:700;cursor:pointer}
.ok{background:#f0fdf4;border:1px solid #bbf7d0;color:#16a34a;padding:10px;border-radius:7px;margin-bottom:12px;font-size:13px}
.err{background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:10px;border-radius:7px;margin-bottom:12px;font-size:13px}
a{display:block;text-align:center;margin-top:14px;color:#6b7280;font-size:13px;text-decoration:none}</style></head>
<body><div class="box">
<h1>🔐 Passwort ändern</h1>
<?php if($msg): ?><div class="ok">✓ <?=htmlspecialchars($msg)?></div><?php endif ?>
<?php if($err): ?><div class="err">⚠ <?=htmlspecialchars($err)?></div><?php endif ?>
<form method="POST">
<label>Aktuelles Passwort</label><input type="password" name="cur" required>
<label>Neues Passwort (min. 8 Zeichen)</label><input type="password" name="n1" required minlength="8">
<label>Bestätigen</label><input type="password" name="n2" required>
<button type="submit">Passwort ändern</button>
</form>
<a href="dashboard.php">← Zurück</a>
</div></body></html>
