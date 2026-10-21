<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('طريقة الطلب غير مسموحة، استخدم POST', 405);
}

require_admin();
$data = request_data();

$name = strtolower(trim($data['name'] ?? ''));
$type = strtolower(trim($data['type'] ?? 'product'));

if ($name === '') {
    json_error('اسم التصنيف مطلوب');
}

if (mb_strlen($name) > 100) {
    json_error('اسم التصنيف يجب ألا يتجاوز 100 حرف');
}

$allowedTypes = ['product', 'subscription'];

if (!in_array($type, $allowedTypes, true)) {
    json_error('نوع التصنيف يجب أن يكون product أو subscription');
}

$existingCategory = DB::fetch(
    'SELECT id
     FROM categories
     WHERE name = ?
     LIMIT 1',
    's',
    [$name]
);

if ($existingCategory) {
    json_error('التصنيف موجود مسبقًا', 409);
}

$categoryId = DB::insert(
    'INSERT INTO categories (name, type) VALUES (?, ?)',
    'ss',
    [$name, $type]
);

json_ok([
    'message' => 'تمت إضافة التصنيف بنجاح',
    'category_id' => $categoryId
], 201);