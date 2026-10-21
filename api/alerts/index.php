<?php

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('طريقة الطلب غير مسموحة، استخدم GET', 405);
}

$user = require_auth();

$alerts = DB::fetchAll(
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

     ORDER BY alert_data.end_date ASC, alert_data.invoice_id DESC",
    'iiii',
    [$user['id'], $user['id'], $user['id'], $user['id']]
);

json_ok([
    'total' => count($alerts),
    'alerts' => $alerts
]);