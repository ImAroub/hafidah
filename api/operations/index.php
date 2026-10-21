<?php

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('طريقة الطلب غير مسموحة، استخدم GET', 405);
}

$user = require_auth();

$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 9;

$search = trim($_GET['q'] ?? '');
$categoryId = (int) ($_GET['category_id'] ?? 0);
$operationType = trim($_GET['type'] ?? '');
$sort = trim($_GET['sort'] ?? 'recently_added');

$where = ['i.user_id = ?'];
$types = 'i';
$params = [(int) $user['id']];

if ($search !== '') {
    $where[] = '(i.item_name LIKE ? OR i.store_name LIKE ?)';
    $types .= 'ss';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

if ($categoryId > 0) {
    $where[] = 'i.category_id = ?';
    $types .= 'i';
    $params[] = $categoryId;
}

$allowedTypes = ['maintenance', 'maintenance_complete', 'replacement', 'return', 'renewal', 'cancel', 'other'];

if ($operationType !== '') {
    if (!in_array($operationType, $allowedTypes, true)) {
        json_error('نوع العملية غير صحيح');
    }

    $where[] = 'EXISTS (
        SELECT 1
        FROM operations filtered_operation
        WHERE filtered_operation.invoice_id = i.id
        AND filtered_operation.operation_type = ?
    )';

    $types .= 's';
    $params[] = $operationType;
}

$orderOptions = [
    'recently_added' => 'ORDER BY last_added_at DESC, invoice_id DESC',
    'newest' => 'ORDER BY last_operation_date DESC, invoice_id DESC',
    'oldest' => 'ORDER BY last_operation_date ASC, invoice_id ASC',
    'name' => 'ORDER BY item_name ASC, invoice_id DESC',
    'most_operations' => 'ORDER BY operation_count DESC, last_added_at DESC'
];

$orderBy = $orderOptions[$sort] ?? $orderOptions['recently_added'];

$sql = "
    SELECT
        i.id AS invoice_id,
        i.item_name,
        i.store_name,
        i.item_status,
        c.name AS category_name,
        COUNT(o.id) AS operation_count,
        MAX(o.operation_date) AS last_operation_date,
        MAX(o.created_at) AS last_added_at,
        SUBSTRING_INDEX(GROUP_CONCAT(o.operation_type ORDER BY o.operation_date DESC, o.id DESC), ',', 1) AS last_operation_type
    FROM invoices i
    INNER JOIN categories c ON c.id = i.category_id
    INNER JOIN operations o ON o.invoice_id = i.id
    WHERE " . implode(' AND ', $where) . "
    GROUP BY i.id, i.item_name, i.store_name, i.item_status, c.name
    $orderBy
";

$result = DB::paging($sql, $types, $params, $perPage, $page);

json_ok([
    'invoices' => $result['data'],
    'pagination' => $result['pagination']
]);