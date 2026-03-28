<?php
// socket-proxy.php - Proxy vers Node.js
$target = 'http://127.0.0.1:3002' . $_SERVER['REQUEST_URI'];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $target);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $header_size);
$body = substr($response, $header_size);

// Transmettre les headers
$header_lines = explode("\n", $headers);
foreach ($header_lines as $header) {
    if (strpos($header, 'Transfer-Encoding') === false) {
        header($header);
    }
}

echo $body;
curl_close($ch);