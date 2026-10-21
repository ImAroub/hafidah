<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('طريقة الطلب غير مسموحة، استخدم POST', 405);
}

$user = require_auth();
$data = request_data();

$itemName = trim($data['item_name'] ?? '');
$categoryId = isset($data['category_id'])? (int) $data['category_id'] : 0;
$storeName = trim($data['store_name'] ?? '');
$contactInfo = trim($data['contact_info'] ?? '');
$purchaseDate = trim($data['purchase_date'] ?? '');
$cost = isset($data['cost']) && $data['cost'] !== '' ? (float) $data['cost'] : null;

$warrantyValue = isset($data['warranty_value']) ?(int) $data['warranty_value'] : 0;
$warrantyUnit = strtolower(trim($data['warranty_unit'] ?? ''));

$returnValue = isset($data['return_value']) ?(int) $data['return_value'] : 0;
$returnUnit =strtolower(trim($data['return_unit'] ?? ''));

$replacementValue = isset($data['replacement_value']) ?(int) $data['replacement_value'] : 0;
$replacementUnit = strtolower(trim($data['replacement_unit'] ?? ''));

$renewalValue = isset($data['renewal_value']) ? (int) $data['renewal_value'] : 0;
$renewalUnit = strtolower(trim($data['renewal_unit'] ?? ''));

if ($itemName === '') {
    json_error('اسم المنتج مطلوب');
}

if ($categoryId <= 0) {
    json_error('التصنيف مطلوب');
}
$category = DB::fetch(
    'SELECT id FROM categories WHERE id = ? LIMIT 1',
    'i',
    [$categoryId]
);
if (!$category) {
    json_error('التصنيف غير موجود');
}

if ($purchaseDate === '') {
    json_error('تاريخ الشراء مطلوب');
}

if ($cost !== null && $cost < 0) {
    json_error('التكلفة لا يمكن أن تكون أقل من صفر');
}

$date = DateTimeImmutable::createFromFormat('!Y-m-d', $purchaseDate);

if (!$date || $date->format('Y-m-d') !== $purchaseDate) {
    json_error('صيغة تاريخ الشراء يجب أن تكون YYYY-MM-DD');
}

$allowedUnits = ['days','months','years'];

$warrantyEndDate = null;

if ($warrantyValue > 0) {
    if (!in_array($warrantyUnit, $allowedUnits, true)) {
        json_error('وحدة الضمان يجب أن تكون ايام أو شهور أو سنين');
    }

    $warrantyEndDate = calculate_end_date(
        $date,
        $warrantyValue,
        $warrantyUnit
    );
}

$returnEndDate = null;

if ($returnValue > 0) {
    if (!in_array($returnUnit, $allowedUnits, true)) {
        json_error('وحدة الاسترجاع يجب أن تكون ايام أو شهور أو سنين');
    }

    $returnEndDate = calculate_end_date(
        $date,
        $returnValue,
        $returnUnit
    );
}

$replacementEndDate = null;

if ($replacementValue > 0) {
    if (!in_array($replacementUnit, $allowedUnits, true)) {
        json_error('وحدة الاستبدال يجب أن تكون days أو months أو years');
    }

    $replacementEndDate = calculate_end_date(
        $date,
        $replacementValue,
        $replacementUnit
    );
}

$renewalEndDate = null;

if ($renewalValue > 0) {
    if (!in_array($renewalUnit, $allowedUnits, true)) {
        json_error('وحدة مدة الاشتراك غير صحيحة');
    }

    $renewalEndDate = calculate_end_date($date, $renewalValue, $renewalUnit);
}

$invoiceFilePath = null;

if (isset($_FILES['invoice_file']) && $_FILES['invoice_file']['error'] !== UPLOAD_ERR_NO_FILE) {
    $invoiceFilePath = uploadImage(
        'invoices',
        $_FILES['invoice_file']
    );
}

$invoiceId = DB::insert(
    'INSERT INTO invoices (
        user_id,
        category_id,
        item_name,
        store_name,
        contact_info,
        purchase_date,
        cost,

        warranty_value,
        warranty_unit,
        warranty_end_date,

        replacement_value,
        replacement_unit,
        replacement_end_date,

        return_value,
        return_unit,
        return_end_date,

        renewal_value,
        renewal_unit,
        renewal_end_date,

        invoice_file_path,
        item_status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
    'iissssdississississss',
    [
        $user['id'],
        $categoryId,
        $itemName,
        $storeName !== '' ? $storeName : null,
        $contactInfo !== '' ? $contactInfo : null,
        $purchaseDate,
        $cost,

        $warrantyValue > 0 ? $warrantyValue : null,
        $warrantyValue > 0 ? $warrantyUnit : null,
        $warrantyEndDate,

        $replacementValue > 0 ? $replacementValue : null,
        $replacementValue > 0 ? $replacementUnit : null,
        $replacementEndDate,

        $returnValue > 0 ? $returnValue : null,
        $returnValue > 0 ? $returnUnit : null,
        $returnEndDate,

        $renewalValue > 0 ? $renewalValue : null,
        $renewalValue > 0 ? $renewalUnit : null,
        $renewalEndDate,

        $invoiceFilePath,
        'active'
    ]
);

json_ok([
    'message' => 'تمت إضافة الفاتورة بنجاح',
    'invoice_id' => $invoiceId,
    'purchase_date' => $purchaseDate,
    'cost' => $cost,
    'warranty_end_date' => $warrantyEndDate,
    'replacement_end_date' => $replacementEndDate,
    'return_end_date' => $returnEndDate,
    'renewal_end_date' => $renewalEndDate,
    'invoice_file_path' => $invoiceFilePath
], 201);
