<?php

require_once __DIR__ . '/env/config.php';
require_once __DIR__ . '/env/DB.php';
require_once __DIR__ . '/lib/func.php';

header('Content-Type: application/json; charset=utf-8');

cleanup_expired_accounts();

$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$apiPosition = strpos($requestPath, '/api/');

if ($apiPosition !== false) {
    $path = substr($requestPath, $apiPosition + strlen('/api/'));
} else {
    $path ='';
}

$path = trim($path, '/');

if ($path === '') {
    $path = 'home/welcome';
}

if(str_contains($path, '..') || str_contains($path, '\\')) {
    json_error('Invalid endpoint path', 400);
}

$file = __DIR__ .'/'. $path.'.php';

if(str_starts_with($path, 'env/')) {
    json_error('Endpoint not found', 404);
}

if(!is_file($file)){
    json_error('Endpoint not found', 404);
} 

require $file;

