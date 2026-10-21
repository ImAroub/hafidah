<?php

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('طريقة الطلب غير مسموحة، استخدم GET', 405);
}

$user = require_auth();

$invoiceId = (int) ($_GET['id'] ?? 0);

if ($invoiceId <= 0) {
    json_error('رقم الفاتورة غير صحيح');
}

$invoice = DB::fetch(
    'SELECT
        i.id,
        i.item_name,
        i.store_name,
        i.item_status,
        c.name AS category_name,
        c.type AS category_type
     FROM invoices i
     INNER JOIN categories c ON c.id = i.category_id
     WHERE i.id = ?
     AND i.user_id = ?
     LIMIT 1',
    'ii',
    [$invoiceId, $user['id']]
);

if (!$invoice) {
    json_error('الفاتورة غير موجودة', 404);
}

$operations = DB::fetchAll(
    'SELECT
        id,
        operation_type,
        operation_date,
        expected_end_date,
        cost,
        notes,
        operation_file_path,
        created_at
     FROM operations
     WHERE invoice_id = ?
     ORDER BY operation_date DESC, id DESC',
    'i',
    [$invoiceId]
);

json_ok([
    'invoice' => $invoice,
    'operations' => $operations
]);