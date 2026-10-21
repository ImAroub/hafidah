<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('طريقة الطلب غير مسموحة، استخدم POST', 405);
}

require_admin();
$data = request_data();
$invoiceId = (int) ($data['id'] ?? 0);

if ($invoiceId <= 0) {
    json_error('رقم الفاتورة غير صحيح');
}

$invoice = DB::fetch(
    'SELECT id, invoice_file_path
     FROM invoices
     WHERE id = ?
     LIMIT 1',
    'i',
    [$invoiceId]
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
     WHERE id = ?',
    'i',
    [$invoiceId]
);

if (DB::affected() === 0) {
    json_error('تعذر حذف الفاتورة', 500);
}

if (!empty($invoice['invoice_file_path'])) {
    delete_uploaded_image('invoices', $invoice['invoice_file_path']);
}

json_ok([
    'message' => 'تم حذف الفاتورة وجميع عملياتها بنجاح'
]);
