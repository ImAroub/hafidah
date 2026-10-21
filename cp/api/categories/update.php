<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('طريقة الطلب غير مسموحة، استخدم POST', 405);
}

require_admin();
$data = request_data();

$categoryId = (int) ($data['id'] ?? 0);

if ($categoryId <= 0) {
    json_error('رقم التصنيف غير صحيح');
}

$category = DB::fetch(
    'SELECT id, name, type
     FROM categories
     WHERE id = ?
     LIMIT 1',
    'i',
    [$categoryId]
);

if (!$category) {
    json_error('التصنيف غير موجود', 404);
}

$name = strtolower(trim($data['name'] ?? $category['name']));

$type = strtolower(trim($data['type'] ?? $category['type']));

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
     WHERE name = ? AND id != ?
     LIMIT 1',
    'si',
    [$name, $categoryId]
);

if ($existingCategory) {
    json_error('يوجد تصنيف آخر بنفس الاسم', 409);
}

DB::query(
    'UPDATE categories
     SET name = ?, type = ?
     WHERE id = ?',
    'ssi',
    [$name, $type, $categoryId
    ]
);

json_ok([
    'message' => 'تم تعديل التصنيف بنجاح',
    'category' => [
        'id' => $categoryId,
        'name' => $name,
        'type' => $type
    ]
]);