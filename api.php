<?php
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$action = $_GET['action'] ?? '';

// ── save_rating: öffentlich zugänglich (Gäste ohne Login) ─────────
if ($action === 'save_rating') {
    try {
        $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    } catch(PDOException $e) {
        http_response_code(500); echo json_encode(['error'=>'DB-Fehler']); exit;
    }
    $inp = json_decode(file_get_contents('php://input'), true);
    if(!$inp){ http_response_code(400); echo json_encode(['error'=>'Ungueltig']); exit; }
    $st2 = $pdo->query("SELECT data_value FROM app_data WHERE data_key='ratings'");
    $rw2 = $st2->fetch(PDO::FETCH_ASSOC);
    $rats = $rw2 ? json_decode($rw2['data_value'], true) : [];
    if(!is_array($rats)) $rats = [];
    $inp['id'] = uniqid();
    $rats[] = $inp;
    $st3 = $pdo->prepare("INSERT INTO app_data (data_key,data_value) VALUES ('ratings',?) ON DUPLICATE KEY UPDATE data_value=VALUES(data_value)");
    $st3->execute([json_encode($rats, JSON_UNESCAPED_UNICODE)]);
    echo json_encode(['success'=>true]);
    exit;
}

// ── Alle anderen Aktionen: Admin-Session erforderlich ─────────────
session_start();
if (empty($_SESSION['ok']) || empty($_SESSION['exp']) || $_SESSION['exp'] < time()) {
    http_response_code(401); echo json_encode(['error'=>'Nicht eingeloggt']); exit;
}
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
} catch(PDOException $e) {
    http_response_code(500); echo json_encode(['error'=>'DB-Fehler']); exit;
}
switch($action) {
    case 'load':
        $data = [];
        foreach($pdo->query("SELECT data_key,data_value,updated_at FROM app_data") as $r) {
            $data[$r['data_key']] = ['value'=>json_decode($r['data_value'],true),'updated'=>$r['updated_at']];
        }
        echo json_encode(['success'=>true,'data'=>$data]);
        break;
    case 'save_all':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) { http_response_code(400); echo json_encode(['error'=>'Ungültig']); exit; }
        $stmt = $pdo->prepare("INSERT INTO app_data (data_key,data_value) VALUES (?,?) ON DUPLICATE KEY UPDATE data_value=VALUES(data_value)");
        $n = 0;
        foreach($input as $k=>$v) {
            $k = preg_replace('/[^a-zA-Z0-9_]/','', $k);
            if($k) { $stmt->execute([$k, json_encode($v,JSON_UNESCAPED_UNICODE)]); $n++; }
        }
        echo json_encode(['success'=>true,'saved'=>$n]);
        break;

    // ── Letzten Aufguss laden ──────────────────────────────────────
    case 'last_aufguss':
        $stmt = $pdo->query("SELECT data_value FROM app_data WHERE data_key='v2'");
        $row2 = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!$row2){ echo json_encode(['success'=>false]); break; }
        $ents = json_decode($row2['data_value'], true);
        if(!is_array($ents)||!count($ents)){ echo json_encode(['success'=>false]); break; }
        usort($ents, function($a,$b){
            return (strtotime($b['date'])*100+$b['hour'])-(strtotime($a['date'])*100+$a['hour']);
        });
        echo json_encode(['success'=>true,'aufguss'=>$ents[0]]);
        break;

    default:
        http_response_code(400); echo json_encode(['error'=>'Unbekannte Aktion']);
}