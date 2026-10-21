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
    'SELECT id, name
     FROM categories
     WHERE id = ?
     LIMIT 1',
    'i',
    [$categoryId]
);

if (!$category) {
    json_error('التصنيف غير موجود', 404);
}

$invoiceCount = DB::fetch(
    'SELECT COUNT(*) AS total
     FROM invoices
     WHERE category_id = ?',
    'i',
    [$categoryId]
);

if ((int) ($invoiceCount['total'] ?? 0) > 0) {
    json_error('لا يمكن حذف التصنيف لأنه مستخدم في فواتير',409);
}

DB::query(
    'DELETE FROM categories
     WHERE id = ?',
    'i',
    [$categoryId]
);

if (DB::affected() === 0) {
    json_error('تعذر حذف التصنيف', 500);
}

json_ok([
    'message' => 'تم حذف التصنيف بنجاح'
]);