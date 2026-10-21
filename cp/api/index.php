<?php

require_once __DIR__ . '/../../api/env/config.php';
require_once __DIR__ . '/../../api/env/DB.php';
require_once __DIR__ . '/lib/func.php';

header('Content-Type: application/json; charset=utf-8');

$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$apiPosition = strpos($requestPath, '/cp/api/');

if ($apiPosition !== false) {
    $path = substr($requestPath, $apiPosition + strlen('/cp/api/'));
} else {
    $path = '';
}

$path = trim($path, '/');

if ($path === '') {
    json_ok(['message' => 'Hafitha Admin API']);
}

if (str_contains($path, '..') || str_contains($path, '\\')) {
    json_error('Invalid endpoint path', 400);
}

$file = __DIR__ . '/' . $path . '.php';

if (!is_file($file)) {
    json_error('Endpoint not found', 404);
}

require $file;