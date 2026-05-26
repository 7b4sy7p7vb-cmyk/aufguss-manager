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

// Cache verhindern - Browser soll immer die aktuelle Version laden
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// clearstatcache() verhindert dass PHP die alte Dateigröße cached
clearstatcache(true, $f);

// Content-Length weglassen - verhindert Truncation bei neuen Dateiversionen
// Datei direkt ausgeben
readfile($f);
