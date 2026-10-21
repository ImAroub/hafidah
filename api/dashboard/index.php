<?php

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('طريقة الطلب غير مسموحة، استخدم GET', 405);
}

$user = require_auth();

$invoicePeriod = trim($_GET['invoice_period'] ?? 'all');
$invoiceCategoryId = (int) ($_GET['invoice_category_id'] ?? 0);

$operationPeriod = trim($_GET['operation_period'] ?? 'all');
$operationType = trim($_GET['operation_type'] ?? '');

$allowedPeriods = ['all', 'week', 'month', 'year'];
$allowedOperationTypes = ['maintenance', 'maintenance_complete', 'replacement', 'return', 'renewal', 'cancel', 'other'];

if (!in_array($invoicePeriod, $allowedPeriods, true)) {
    $invoicePeriod = 'all';
}

if (!in_array($operationPeriod, $allowedPeriods, true)) {
    $operationPeriod = 'all';
}

$invoiceWhere = ['user_id = ?'];
$invoiceTypes = 'i';
$invoiceParams = [$user['id']];

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

$invoiceCount = DB::fetch(
    'SELECT COUNT(*) AS total
     FROM invoices
     WHERE ' . implode(' AND ', $invoiceWhere),
    $invoiceTypes,
    $invoiceParams
);

$operationWhere = ['i.user_id = ?'];
$operationTypes = 'i';
$operationParams = [$user['id']];

if ($operationPeriod === 'week') {
    $operationWhere[] = 'YEARWEEK(o.operation_date, 1) = YEARWEEK(CURDATE(), 1)';
} elseif ($operationPeriod === 'month') {
    $operationWhere[] = 'YEAR(o.operation_date) = YEAR(CURDATE()) AND MONTH(o.operation_date) = MONTH(CURDATE())';
} elseif ($operationPeriod === 'year') {
    $operationWhere[] = 'YEAR(o.operation_date) = YEAR(CURDATE())';
}

if ($operationType !== '' && in_array($operationType, $allowedOperationTypes, true)) {
    $operationWhere[] = 'o.operation_type = ?';
    $operationTypes .= 's';
    $operationParams[] = $operationType;
}

$operationCount = DB::fetch(
    'SELECT COUNT(*) AS total
     FROM operations o
     INNER JOIN invoices i ON i.id = o.invoice_id
     WHERE ' . implode(' AND ', $operationWhere),
    $operationTypes,
    $operationParams
);

$alertCount = DB::fetch(
    "SELECT COALESCE(SUM(
        CASE WHEN warranty_end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY) THEN 1 ELSE 0 END +
        CASE WHEN return_end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY) THEN 1 ELSE 0 END +
        CASE WHEN replacement_end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY) THEN 1 ELSE 0 END +
        CASE WHEN renewal_end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY) THEN 1 ELSE 0 END
     ), 0) AS total
     FROM invoices
     WHERE user_id = ?
     AND item_status NOT IN ('returned', 'cancelled')",
    'i',
    [$user['id']]
);

$recentAlerts = DB::fetchAll(
    "SELECT
        alert_data.invoice_id,
        alert_data.item_name,
        alert_data.alert_type,
        alert_data.end_date,
        DATEDIFF(alert_data.end_date, CURDATE()) AS days_left

     FROM (

        SELECT
            id AS invoice_id,
            item_name,
            'warranty' AS alert_type,
            warranty_end_date AS end_date
        FROM invoices
        WHERE user_id = ?
        AND item_status NOT IN ('returned', 'cancelled')
        AND warranty_end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)

        UNION ALL

        SELECT
            id AS invoice_id,
            item_name,
            'return' AS alert_type,
            return_end_date AS end_date
        FROM invoices
        WHERE user_id = ?
        AND item_status NOT IN ('returned', 'cancelled')
        AND return_end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)

        UNION ALL

        SELECT
            id AS invoice_id,
            item_name,
            'replacement' AS alert_type,
            replacement_end_date AS end_date
        FROM invoices
        WHERE user_id = ?
        AND item_status NOT IN ('returned', 'cancelled')
        AND replacement_end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)

        UNION ALL

        SELECT
            id AS invoice_id,
            item_name,
            'renewal' AS alert_type,
            renewal_end_date AS end_date
        FROM invoices
        WHERE user_id = ?
        AND item_status NOT IN ('returned', 'cancelled')
        AND renewal_end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)

     ) AS alert_data

     ORDER BY alert_data.end_date ASC, alert_data.invoice_id DESC
     LIMIT 3",
    'iiii',
    [$user['id'], $user['id'], $user['id'], $user['id']]
);

$recentInvoices = DB::fetchAll(
    'SELECT
        i.id,
        i.item_name,
        i.store_name,
        i.purchase_date,
        c.name AS category_name
     FROM invoices i
     INNER JOIN categories c ON c.id = i.category_id
     WHERE i.user_id = ?
     ORDER BY i.created_at DESC, i.id DESC
     LIMIT 2',
    'i',
    [$user['id']]
);

$recentOperations = DB::fetchAll(
    'SELECT
        o.id,
        o.invoice_id,
        o.operation_type,
        o.operation_date,
        o.cost,
        i.item_name
     FROM operations o
     INNER JOIN invoices i ON i.id = o.invoice_id
     WHERE i.user_id = ?
     ORDER BY o.created_at DESC, o.id DESC
     LIMIT 2',
    'i',
    [$user['id']]
);


json_ok([
    'total_invoices' => (int) ($invoiceCount['total'] ?? 0),
    'total_operations' => (int) ($operationCount['total'] ?? 0),
    'total_alerts' => (int) ($alertCount['total'] ?? 0),

    'recent_alerts' => $recentAlerts,
    'recent_invoices' => $recentInvoices,
    'recent_operations' => $recentOperations,

    'filters' => [
        'invoice_period' => $invoicePeriod,
        'invoice_category_id' => $invoiceCategoryId,
        'operation_period' => $operationPeriod,
        'operation_type' => $operationType
    ]
]);