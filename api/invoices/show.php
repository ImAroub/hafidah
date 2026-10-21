<?php

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('طريقة الطلب غير مسموحة', 405);
}

$user = require_auth();

$invoiceId = (int) ($_GET['id'] ?? 0);

if ($invoiceId <= 0) {
    json_error('رقم الفاتورة غير صحيح');
}

$invoice = DB::fetch(
    'SELECT
        i.id,
        i.category_id,
        i.item_name,
        i.store_name,
        i.contact_info,
        i.purchase_date,
        i.cost,

        i.warranty_value,
        i.warranty_unit,
        i.warranty_end_date,

        i.return_value,
        i.return_unit,
        i.return_end_date,

        i.replacement_value,
        i.replacement_unit,
        i.replacement_end_date,

        i.renewal_value,
        i.renewal_unit,
        i.renewal_end_date,

        i.invoice_file_path,
        i.item_status,
        i.created_at,

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
        operation_date
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