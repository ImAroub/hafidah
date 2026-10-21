<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('طريقة الطلب غير مسموحة، استخدم POST', 405);
}

$user = require_auth();
$data = request_data();

$invoiceId = (int) ($data['invoice_id'] ?? 0);
$operationType = strtolower(trim($data['operation_type'] ?? ''));
$operationDate = trim($data['operation_date'] ?? '');
$expectedEndDate = trim($data['expected_end_date'] ?? '');
$cost = $data['cost'] ?? null;
$notes = trim($data['notes'] ?? '');
$operationFilePath = null;

if ($invoiceId <= 0) {
    json_error('رقم الفاتورة غير صحيح');
}

if ($operationType === '') {
    json_error('نوع العملية مطلوب');
}

if ($operationDate === '' || !is_valid_date($operationDate)) {
    json_error('تاريخ العملية غير صحيح');
}

if ($expectedEndDate !== '' && !is_valid_date($expectedEndDate)) {
    json_error('تاريخ الانتهاء المتوقع غير صحيح');
}

if ($expectedEndDate !== '' && $expectedEndDate < $operationDate) {
    json_error('تاريخ الانتهاء المتوقع لا يمكن أن يكون قبل تاريخ العملية');
}

if ($cost !== null && $cost !== '') {
    if (!is_numeric($cost) || (float) $cost < 0) {
        json_error('تكلفة العملية غير صحيحة');
    }

    $cost = (float) $cost;
} else {
    $cost = null;
}

$invoice = DB::fetch(
    'SELECT i.id, i.item_status, c.type AS category_type
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

if ($invoice['item_status'] === 'returned') {
    json_error('لا يمكن إضافة عملية على منتج مسترجع');
}

$allowedTypes = get_allowed_operation_types($invoice['category_type']);

if (!in_array($operationType, $allowedTypes, true)) {
    json_error('نوع العملية غير مناسب لهذه الفاتورة');
}

if (isset($_FILES['operation_file']) && $_FILES['operation_file']['error'] !== UPLOAD_ERR_NO_FILE) {
    $operationFilePath = uploadImage('operations', $_FILES['operation_file']);
}

$operationId = DB::insert(
    'INSERT INTO operations (
        invoice_id,
        operation_type,
        operation_date,
        expected_end_date,
        cost,
        notes,
        operation_file_path
     ) VALUES (?, ?, ?, ?, ?, ?, ?)',
    'isssdss',
    [$invoiceId, $operationType, $operationDate, $expectedEndDate !== '' ? $expectedEndDate : null, $cost, $notes !== '' ? $notes : null, $operationFilePath]
);

sync_invoice_status($invoiceId, $user['id']);

json_ok([
    'message' => 'تمت إضافة العملية بنجاح',
    'operation_id' => $operationId,
    'operation_file_path' => $operationFilePath
], 201);