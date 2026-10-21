<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('طريقة الطلب غير مسموحة، استخدم POST', 405);
}

require_admin();
$data = request_data();
$id = (int) ($data['id'] ?? 0);

if ($id <= 0) {
    json_error('رقم العملية غير صحيح');
}

$operation = DB::fetch(
    'SELECT o.invoice_id, o.operation_file_path, i.user_id
     FROM operations o
     INNER JOIN invoices i ON i.id = o.invoice_id
     WHERE o.id = ?
     LIMIT 1',
    'i',
    [$id]
);

if (!$operation) {
    json_error('العملية غير موجودة', 404);
}

DB::query(
    'DELETE FROM operations
     WHERE id = ?',
    'i',
    [$id]
);

if (DB::affected() === 0) {
    json_error('تعذر حذف العملية', 500);
}

if (!empty($operation['operation_file_path'])) {
    delete_uploaded_image('operations', $operation['operation_file_path']);
}

sync_invoice_status((int) $operation['invoice_id'], (int) $operation['user_id']);

json_ok([
    'message' => 'تم حذف العملية بنجاح',
    'invoice_id' => (int) $operation['invoice_id']
]);
