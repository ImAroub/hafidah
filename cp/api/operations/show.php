<?php

if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_error('طريقة الطلب غير مسموحة، استخدم GET', 405);
require_admin();
$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) json_error('رقم العملية غير صحيح');

$operation = DB::fetch(
    'SELECT o.*, i.item_name, i.user_id, c.name AS category_name, u.name AS user_name, u.email AS user_email
     FROM operations o
     INNER JOIN invoices i ON i.id = o.invoice_id
     INNER JOIN categories c ON c.id = i.category_id
     INNER JOIN users u ON u.id = i.user_id
     WHERE o.id = ? LIMIT 1',
    'i', [$id]
);
if (!$operation) json_error('العملية غير موجودة', 404);
json_ok(['operation' => $operation]);
