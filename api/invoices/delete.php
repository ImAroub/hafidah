<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('طريقة الطلب غير مسموحة، استخدم POST', 405);
}

$user = require_auth();

$data = request_data();

$invoiceId = isset($data['id'])? (int) $data['id']: 0;

if ($invoiceId <= 0) {
    json_error('رقم الفاتورة غير صحيح');
}

$invoice = DB::fetch(
    'SELECT
        id,
        invoice_file_path

     FROM invoices

     WHERE id = ?
       AND user_id = ?

     LIMIT 1',
    'ii',
    [
        $invoiceId,
        $user['id']
    ]
);

if (!$invoice) {
    json_error('الفاتورة غير موجودة', 404);
}

$operations = DB::fetchAll(
    'SELECT operation_file_path
     FROM operations
     WHERE invoice_id = ?',
    'i',
    [$invoiceId]
);

DB::query(
    'DELETE FROM operations
     WHERE invoice_id = ?',
    'i',
    [$invoiceId]
);

foreach ($operations as $operation) {
    if (!empty($operation['operation_file_path'])) {
        delete_uploaded_image('operations', $operation['operation_file_path']);
    }
}

DB::query(
    'DELETE FROM invoices
     WHERE id = ?
       AND user_id = ?',
    'ii',
    [
        $invoiceId,
        $user['id']
    ]
);

if (DB::affected() === 0) {
    json_error('تعذر حذف الفاتورة', 500);
}

if (!empty($invoice['invoice_file_path'])) {
    delete_uploaded_image('invoices', $invoice['invoice_file_path']);
}

json_ok([
    'message' => 'تم حذف الفاتورة بنجاح'
]);