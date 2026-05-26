<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: max-age=900'); // 15 Min Cache

// Mehrere Endpunkte versuchen
$endpoints = [
    'https://api.open-meteo.com/v1/forecast?latitude=51.7&longitude=11.05&current_weather=true&daily=temperature_2m_max,temperature_2m_min,precipitation_sum,weathercode,sunshine_duration&timezone=Europe%2FBerlin&forecast_days=7',
    'http://api.open-meteo.com/v1/forecast?latitude=51.7&longitude=11.05&current_weather=true&daily=temperature_2m_max,temperature_2m_min,precipitation_sum,weathercode,sunshine_duration&timezone=Europe%2FBerlin&forecast_days=7',
];

$data = null;

foreach($endpoints as $url) {
    // cURL mit längerem Timeout
    if(function_exists('curl_init') && !$data) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; SaunaManager/1.0)',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if($result && $httpCode === 200) { $data = $result; break; }
    }

    // file_get_contents Fallback
    if(!$data) {
        $ctx = stream_context_create([
            'http' => ['timeout' => 15, 'user_agent' => 'SaunaManager/1.0', 'follow_location' => true],
            'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false]
        ]);
        $result = @file_get_contents($url, false, $ctx);
        if($result) { $data = $result; break; }
    }
}

if(!$data) {
    http_response_code(503);
    echo json_encode(['error' => 'nicht_erreichbar']);
    exit;
}

echo $data;
