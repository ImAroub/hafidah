<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('طريقة الطلب غير مسموحة، استخدم POST', 405);
}

$user = require_auth();
$data = request_data();

$invoiceId = (int) ($data['id'] ?? 0);

if ($invoiceId <= 0) {
    json_error('رقم الفاتورة غير صحيح');
}

$invoice = DB::fetch(
    'SELECT *
     FROM invoices
     WHERE id = ?
       AND user_id = ?
     LIMIT 1',
    'ii',
    [$invoiceId, $user['id']]
);

if (!$invoice) {
    json_error('الفاتورة غير موجودة', 404);
}

$itemName = trim($data['item_name'] ?? $invoice['item_name']);
$categoryId = (int) ($data['category_id'] ?? $invoice['category_id']);
$storeName = trim($data['store_name'] ?? $invoice['store_name'] ?? '');
$contactInfo = trim($data['contact_info'] ?? $invoice['contact_info'] ?? '');
$purchaseDate = trim($data['purchase_date'] ?? $invoice['purchase_date']);
$cost = isset($data['cost']) && $data['cost'] !== '' ? (float) $data['cost'] : null;

if ($itemName === '') {
    json_error('اسم المنتج مطلوب');
}

if ($categoryId <= 0) {
    json_error('التصنيف مطلوب');
}

$category = DB::fetch(
    'SELECT id
     FROM categories
     WHERE id = ?
     LIMIT 1',
    'i',
    [$categoryId]
);

if (!$category) {
    json_error('التصنيف غير موجود');
}

$date = DateTimeImmutable::createFromFormat('!Y-m-d', $purchaseDate);

if (!$date || $date->format('Y-m-d') !== $purchaseDate) {
    json_error('صيغة تاريخ الشراء يجب أن تكون YYYY-MM-DD');
}

if ($cost !== null && $cost < 0) {
    json_error('التكلفة لا يمكن أن تكون أقل من صفر');
}

$warrantyValue = array_key_exists('warranty_value', $data)
    ? (int) $data['warranty_value']
    : (int) ($invoice['warranty_value'] ?? 0);

$warrantyUnit = strtolower(trim(
    $data['warranty_unit'] ?? $invoice['warranty_unit'] ?? ''
));

$returnValue = array_key_exists('return_value', $data)
    ? (int) $data['return_value']
    : (int) ($invoice['return_value'] ?? 0);

$returnUnit = strtolower(trim(
    $data['return_unit'] ?? $invoice['return_unit'] ?? ''
));

$replacementValue = array_key_exists('replacement_value', $data)
    ? (int) $data['replacement_value']
    : (int) ($invoice['replacement_value'] ?? 0);

$replacementUnit = strtolower(trim(
    $data['replacement_unit'] ?? $invoice['replacement_unit'] ?? ''
));

$renewalValue = array_key_exists('renewal_value', $data)
    ? (int) $data['renewal_value']
    : (int) ($invoice['renewal_value'] ?? 0);

$renewalUnit = strtolower(trim(
    $data['renewal_unit'] ?? $invoice['renewal_unit'] ?? ''
));

$calculateEndDate = function (int $value, string $unit) use ($date): ?string {
    if ($value <= 0) {
        return null;
    }

    return calculate_end_date($date, $value, $unit);
};

$warrantyEndDate = $calculateEndDate($warrantyValue,$warrantyUnit);

$returnEndDate = $calculateEndDate($returnValue,$returnUnit);

$replacementEndDate = $calculateEndDate($replacementValue,$replacementUnit);

$renewalEndDate = $calculateEndDate($renewalValue,$renewalUnit);

$oldFilePath = $invoice['invoice_file_path'];
$invoiceFilePath = $oldFilePath;
$deleteOldFile = false;
$removeInvoiceFile = ($data['remove_invoice_file'] ?? '') === '1';

if (isset($_FILES['invoice_file']) && $_FILES['invoice_file']['error'] !== UPLOAD_ERR_NO_FILE) {
    $invoiceFilePath = uploadImage('invoices', $_FILES['invoice_file']);
    $deleteOldFile = !empty($oldFilePath);
} elseif ($removeInvoiceFile) {
    $invoiceFilePath = null;
    $deleteOldFile = !empty($oldFilePath);
}

DB::query(
    'UPDATE invoices SET
        category_id = ?,
        item_name = ?,
        store_name = ?,
        contact_info = ?,
        purchase_date = ?,
        cost = ?,

        warranty_value = ?,
        warranty_unit = ?,
        warranty_end_date = ?,

        return_value = ?,
        return_unit = ?,
        return_end_date = ?,

        replacement_value = ?,
        replacement_unit = ?,
        replacement_end_date = ?,

        renewal_value = ?,
        renewal_unit = ?,
        renewal_end_date = ?,

        invoice_file_path = ?

     WHERE id = ?
       AND user_id = ?',
    'issssdississississsii',
    [
        $categoryId,
        $itemName,
        $storeName !== '' ? $storeName : null,
        $contactInfo !== '' ? $contactInfo : null,
        $purchaseDate,
        $cost,

        $warrantyValue > 0 ? $warrantyValue : null,
        $warrantyValue > 0 ? $warrantyUnit : null,
        $warrantyEndDate,

        $returnValue > 0 ? $returnValue : null,
        $returnValue > 0 ? $returnUnit : null,
        $returnEndDate,

        $replacementValue > 0 ? $replacementValue : null,
        $replacementValue > 0 ? $replacementUnit : null,
        $replacementEndDate,

        $renewalValue > 0 ? $renewalValue : null,
        $renewalValue > 0 ? $renewalUnit : null,
        $renewalEndDate,

        $invoiceFilePath,

        $invoiceId,
        $user['id']
    ]
);

if ($deleteOldFile) {
    $oldFile = UPLOAD_PATH . 'invoices/' . basename($oldFilePath);

    if (is_file($oldFile)) {
        unlink($oldFile);
    }
}

json_ok([
    'message' => 'تم تعديل الفاتورة بنجاح',
    'invoice_id' => $invoiceId
]);