<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('طريقة الطلب غير مسموحة، استخدم POST', 405);
}

$user = require_auth();
$data = request_data();

$id = (int) ($data['id'] ?? 0);

if ($id <= 0) {
    json_error('رقم العملية غير صحيح');
}

$operation = DB::fetch(
    'SELECT o.*, c.type AS category_type
     FROM operations o
     INNER JOIN invoices i ON i.id = o.invoice_id
     INNER JOIN categories c ON c.id = i.category_id
     WHERE o.id = ?
     AND i.user_id = ?
     LIMIT 1',
    'ii',
    [$id, $user['id']]
);

if (!$operation) {
    json_error('العملية غير موجودة', 404);
}

$operationType = strtolower(trim($data['operation_type'] ?? $operation['operation_type']));
$operationDate = trim($data['operation_date'] ?? $operation['operation_date']);
$expectedEndDate = array_key_exists('expected_end_date', $data) ? trim((string) $data['expected_end_date']) : ($operation['expected_end_date'] ?? '');
$cost = array_key_exists('cost', $data) ? $data['cost'] : $operation['cost'];
$notes = array_key_exists('notes', $data) ? trim((string) $data['notes']) : ($operation['notes'] ?? '');
$removeOperationFile = ($data['remove_operation_file'] ?? '') === '1';

$oldOperationFilePath = $operation['operation_file_path'] ?? null;
$operationFilePath = $oldOperationFilePath;

$allowedTypes = get_allowed_operation_types($operation['category_type']);

if (!in_array($operationType, $allowedTypes, true)) {
    json_error('نوع العملية غير مناسب لهذه الفاتورة');
}

if (!is_valid_date($operationDate)) {
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

if (isset($_FILES['operation_file']) && $_FILES['operation_file']['error'] !== UPLOAD_ERR_NO_FILE) {
    $operationFilePath = uploadImage('operations', $_FILES['operation_file']);
} elseif ($removeOperationFile) {
    $operationFilePath = null;
}

DB::query(
    'UPDATE operations
     SET operation_type = ?, operation_date = ?, expected_end_date = ?, cost = ?, notes = ?, operation_file_path = ?
     WHERE id = ?',
    'sssdssi',
    [$operationType, $operationDate, $expectedEndDate !== '' ? $expectedEndDate : null, $cost, $notes !== '' ? $notes : null, $operationFilePath, $id]
);

if ($oldOperationFilePath && $oldOperationFilePath !== $operationFilePath) {
    delete_uploaded_image('operations', $oldOperationFilePath);
}

sync_invoice_status((int) $operation['invoice_id'], $user['id']);

json_ok([
    'message' => 'تم تعديل العملية بنجاح',
    'invoice_id' => (int) $operation['invoice_id'],
    'operation_file_path' => $operationFilePath
]);