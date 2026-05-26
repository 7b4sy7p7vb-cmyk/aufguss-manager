<?php
require_once 'config.php';
session_start();
if (empty($_SESSION['ok']) || empty($_SESSION['exp']) || $_SESSION['exp'] < time()) {
    header('Location: login.php'); exit;
}
$_SESSION['exp'] = time() + SESSION_TIMEOUT;

$f = __DIR__ . '/aufguss_app/index.html';
if (!file_exists($f)) {
    echo '<p style="font-family:sans-serif;padding:20px">Fehler: index.html nicht gefunden in ' . $f . '</p>';
    exit;
}

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
// CSP: externe Wetter-APIs erlauben
header("Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval'; connect-src 'self' https://api.open-meteo.com https://api.qrserver.com; img-src 'self' data: https://api.qrserver.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' data: https://fonts.gstatic.com;");

clearstatcache(true, $f);
readfile($f);
