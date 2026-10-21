<?php

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('طريقة الطلب غير مسموحة، استخدم GET', 405);
}

$user = require_auth();

$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 9;

$search = trim($_GET['q'] ?? '');
$categoryId = (int) ($_GET['category_id'] ?? 0);
$status = trim($_GET['status'] ?? '');
$periodType = trim($_GET['period_type'] ?? '');
$purchasePeriod = trim($_GET['purchase_period'] ?? '');
$sort = trim($_GET['sort'] ?? 'priority');

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

$allowedStatuses = [
    'active',
    'maintenance',
    'returned',
    'replaced',
    'cancelled'
];

if ($status !== '') {
    if (!in_array($status, $allowedStatuses, true)) {
        json_error('حالة المنتج غير صحيحة');
    }

    $where[] = 'i.item_status = ?';
    $types .= 's';
    $params[] = $status;
}

$periodColumns = [
    'warranty' => 'i.warranty_end_date',
    'return' => 'i.return_end_date',
    'replacement' => 'i.replacement_end_date',
    'renewal' => 'i.renewal_end_date'
];

if ($periodType !== '') {
    if (!isset($periodColumns[$periodType])) {
        json_error('نوع المدة غير صحيح');
    }

    $where[] = $periodColumns[$periodType] . ' IS NOT NULL';
}

$purchasePeriods = [
    'week' => 'i.purchase_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)',
    'month' => 'i.purchase_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)',
    'year' => 'i.purchase_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)'
];

if ($purchasePeriod !== '') {
    if (!isset($purchasePeriods[$purchasePeriod])) {
        json_error('فترة الشراء غير صحيحة');
    }

    $where[] = $purchasePeriods[$purchasePeriod];
}

$nearestEndDateSql = "
    CASE
        WHEN i.item_status NOT IN ('returned', 'cancelled') THEN
            NULLIF(
                LEAST(
                    COALESCE(
                        CASE
                            WHEN i.warranty_end_date >= CURDATE()
                            THEN i.warranty_end_date
                        END,
                        '9999-12-31'
                    ),
                    COALESCE(
                        CASE
                            WHEN i.return_end_date >= CURDATE()
                            THEN i.return_end_date
                        END,
                        '9999-12-31'
                    ),
                    COALESCE(
                        CASE
                            WHEN i.replacement_end_date >= CURDATE()
                            THEN i.replacement_end_date
                        END,
                        '9999-12-31'
                    ),
                    COALESCE(
                        CASE
                            WHEN i.renewal_end_date >= CURDATE()
                            THEN i.renewal_end_date
                        END,
                        '9999-12-31'
                    )
                ),
                '9999-12-31'
            )
        ELSE NULL
    END
";

$baseSql = "
    SELECT
        i.id,
        i.category_id,
        c.name AS category_name,
        i.item_name,
        i.store_name,
        i.purchase_date,
        i.item_status,
        i.warranty_end_date,
        i.return_end_date,
        i.replacement_end_date,
        i.renewal_end_date,
        i.created_at,
        $nearestEndDateSql AS nearest_end_date
    FROM invoices i
    INNER JOIN categories c
        ON c.id = i.category_id
    WHERE " . implode(' AND ', $where);

$orderOptions = [
    'priority' => "
        ORDER BY
            CASE
                WHEN invoice_data.nearest_end_date
                     BETWEEN CURDATE()
                     AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)
                THEN 0
                WHEN invoice_data.nearest_end_date IS NOT NULL
                THEN 1
                ELSE 2
            END,
            invoice_data.nearest_end_date ASC,
            invoice_data.purchase_date DESC,
            invoice_data.id DESC
    ",

    'closest' => "
        ORDER BY
            invoice_data.nearest_end_date IS NULL,
            invoice_data.nearest_end_date ASC,
            invoice_data.id DESC
    ",

    'newest' => "
        ORDER BY
            invoice_data.purchase_date DESC,
            invoice_data.id DESC
    ",

    'oldest' => "
        ORDER BY
            invoice_data.purchase_date ASC,
            invoice_data.id ASC
    ",

    'name' => "
        ORDER BY
            invoice_data.item_name ASC,
            invoice_data.id DESC
    "
];

$orderBy = $orderOptions[$sort] ?? $orderOptions['priority'];

$sql = "
    SELECT
        invoice_data.*,
        CASE
            WHEN invoice_data.nearest_end_date = invoice_data.warranty_end_date
            THEN 'warranty'
            WHEN invoice_data.nearest_end_date = invoice_data.return_end_date
            THEN 'return'
            WHEN invoice_data.nearest_end_date = invoice_data.replacement_end_date
            THEN 'replacement'
            WHEN invoice_data.nearest_end_date = invoice_data.renewal_end_date
            THEN 'renewal'
            ELSE NULL
        END AS nearest_period_type,
        CASE
            WHEN invoice_data.nearest_end_date
                 BETWEEN CURDATE()
                 AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)
            THEN 1
            ELSE 0
        END AS is_expiring_soon
    FROM (
        $baseSql
    ) AS invoice_data
    $orderBy
";

$result = DB::paging(
    $sql,
    $types,
    $params,
    $perPage,
    $page
);

json_ok([
    'invoices' => $result['data'],
    'pagination' => $result['pagination']
]);
