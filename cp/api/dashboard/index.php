<?php

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('طريقة الطلب غير مسموحة، استخدم GET', 405);
}

require_admin();

$userPeriod = trim($_GET['user_period'] ?? 'all');
$invoicePeriod = trim($_GET['invoice_period'] ?? 'all');
$invoiceCategoryId = (int) ($_GET['invoice_category_id'] ?? 0);
$operationPeriod = trim($_GET['operation_period'] ?? 'all');
$operationType = trim($_GET['operation_type'] ?? '');

$allowedPeriods = ['all', 'week', 'month', 'year'];
$allowedOperationTypes = ['maintenance', 'maintenance_complete', 'replacement', 'return', 'renewal', 'cancel', 'other'];

if (!in_array($userPeriod, $allowedPeriods, true)) {
    $userPeriod = 'all';
}

if (!in_array($invoicePeriod, $allowedPeriods, true)) {
    $invoicePeriod = 'all';
}

if (!in_array($operationPeriod, $allowedPeriods, true)) {
    $operationPeriod = 'all';
}

$userWhere = ["role = 'user'"];

if ($userPeriod === 'week') {
    $userWhere[] = 'YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)';
} elseif ($userPeriod === 'month') {
    $userWhere[] = 'YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())';
} elseif ($userPeriod === 'year') {
    $userWhere[] = 'YEAR(created_at) = YEAR(CURDATE())';
}

$totalUsers = DB::fetch(
    'SELECT COUNT(*) AS total FROM users WHERE ' . implode(' AND ', $userWhere)
);

$pendingDeleteUsers = DB::fetch(
    "SELECT COUNT(*) AS total
     FROM users
     WHERE role = 'user'
     AND delete_after IS NOT NULL"
);

$invoiceWhere = ['1=1'];
$invoiceTypes = '';
$invoiceParams = [];

if ($invoicePeriod === 'week') {
    $invoiceWhere[] = 'YEARWEEK(purchase_date, 1) = YEARWEEK(CURDATE(), 1)';
} elseif ($invoicePeriod === 'month') {
    $invoiceWhere[] = 'YEAR(purchase_date) = YEAR(CURDATE()) AND MONTH(purchase_date) = MONTH(CURDATE())';
} elseif ($invoicePeriod === 'year') {
    $invoiceWhere[] = 'YEAR(purchase_date) = YEAR(CURDATE())';
}

if ($invoiceCategoryId > 0) {
    $invoiceWhere[] = 'category_id = ?';
    $invoiceTypes .= 'i';
    $invoiceParams[] = $invoiceCategoryId;
}

$totalInvoices = DB::fetch(
    'SELECT COUNT(*) AS total FROM invoices WHERE ' . implode(' AND ', $invoiceWhere),
    $invoiceTypes,
    $invoiceParams
);

$operationWhere = ['1=1'];
$operationTypes = '';
$operationParams = [];

if ($operationPeriod === 'week') {
    $operationWhere[] = 'YEARWEEK(operation_date, 1) = YEARWEEK(CURDATE(), 1)';
} elseif ($operationPeriod === 'month') {
    $operationWhere[] = 'YEAR(operation_date) = YEAR(CURDATE()) AND MONTH(operation_date) = MONTH(CURDATE())';
} elseif ($operationPeriod === 'year') {
    $operationWhere[] = 'YEAR(operation_date) = YEAR(CURDATE())';
}

if ($operationType !== '' && in_array($operationType, $allowedOperationTypes, true)) {
    $operationWhere[] = 'operation_type = ?';
    $operationTypes .= 's';
    $operationParams[] = $operationType;
}

$totalOperations = DB::fetch(
    'SELECT COUNT(*) AS total FROM operations WHERE ' . implode(' AND ', $operationWhere),
    $operationTypes,
    $operationParams
);

$recentUsers = DB::fetchAll(
    "SELECT
        u.id,
        u.name,
        u.email,
        u.created_at,
        (SELECT COUNT(*) FROM invoices i WHERE i.user_id = u.id) AS invoices_count,
        (SELECT COUNT(*) FROM operations o INNER JOIN invoices i2 ON i2.id = o.invoice_id WHERE i2.user_id = u.id) AS operations_count
     FROM users u
     WHERE u.role = 'user'
     ORDER BY u.created_at DESC, u.id DESC
     LIMIT 3"
);

$recentInvoices = DB::fetchAll(
    'SELECT
        i.id,
        i.item_name,
        i.store_name,
        i.purchase_date,
        u.name AS user_name
     FROM invoices i
     INNER JOIN users u ON u.id = i.user_id
     ORDER BY i.created_at DESC, i.id DESC
     LIMIT 3'
);

$recentOperations = DB::fetchAll(
    'SELECT
        o.id,
        o.operation_type,
        o.operation_date,
        i.item_name,
        u.name AS user_name
     FROM operations o
     INNER JOIN invoices i ON i.id = o.invoice_id
     INNER JOIN users u ON u.id = i.user_id
     ORDER BY o.created_at DESC, o.id DESC
     LIMIT 3'
);

json_ok([
    'total_users' => (int) ($totalUsers['total'] ?? 0),
    'pending_delete_users' => (int) ($pendingDeleteUsers['total'] ?? 0),
    'total_invoices' => (int) ($totalInvoices['total'] ?? 0),
    'total_operations' => (int) ($totalOperations['total'] ?? 0),
    'recent_users' => $recentUsers,
    'recent_invoices' => $recentInvoices,
    'recent_operations' => $recentOperations
]);
