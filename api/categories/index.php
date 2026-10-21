<?php

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('طريقة الطلب غير مسموحة، استخدم GET', 405);
}

require_auth();

$categories = DB::fetchAll(
    'SELECT id, name, type, created_at
     FROM categories
     ORDER BY id ASC'
);

json_ok([
    'categories' => $categories
]);