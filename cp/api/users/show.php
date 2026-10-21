<?php

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('طريقة الطلب غير مسموحة، استخدم GET', 405);
}

require_admin();

$userId = (int) ($_GET['id'] ?? 0);
if ($userId <= 0) json_error('رقم المستخدم غير صحيح');

$user = DB::fetch(
    "SELECT
        u.id,
        u.name,
        u.email,
        u.delete_requested_at,
        u.delete_after,
        u.created_at,
        (SELECT COUNT(*) FROM invoices i WHERE i.user_id = u.id) AS invoices_count,
        (SELECT COUNT(*) FROM operations o INNER JOIN invoices i2 ON i2.id = o.invoice_id WHERE i2.user_id = u.id) AS operations_count
     FROM users u
     WHERE u.id = ? AND u.role = 'user'
     LIMIT 1",
    'i',
    [$userId]
);

if (!$user) json_error('المستخدم غير موجود', 404);

$recentInvoices = DB::fetchAll(
    'SELECT i.id, i.item_name, i.store_name, i.purchase_date, c.name AS category_name
     FROM invoices i
     INNER JOIN categories c ON c.id = i.category_id
     WHERE i.user_id = ?
     ORDER BY i.created_at DESC, i.id DESC
     LIMIT 5',
    'i',
    [$userId]
);

$recentOperations = DB::fetchAll(
    'SELECT o.id, o.operation_type, o.operation_date, o.cost, i.item_name
     FROM operations o
     INNER JOIN invoices i ON i.id = o.invoice_id
     WHERE i.user_id = ?
     ORDER BY o.operation_date DESC, o.id DESC
     LIMIT 5',
    'i',
    [$userId]
);

json_ok(['user' => $user, 'recent_invoices' => $recentInvoices, 'recent_operations' => $recentOperations]);
