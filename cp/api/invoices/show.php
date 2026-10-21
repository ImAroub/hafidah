<?php

if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_error('طريقة الطلب غير مسموحة، استخدم GET', 405);
require_admin();
$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) json_error('رقم الفاتورة غير صحيح');

$invoice = DB::fetch(
    'SELECT i.*, c.name AS category_name, c.type AS category_type, u.name AS user_name, u.email AS user_email
     FROM invoices i
     INNER JOIN categories c ON c.id = i.category_id
     INNER JOIN users u ON u.id = i.user_id
     WHERE i.id = ? LIMIT 1',
    'i', [$id]
);
if (!$invoice) json_error('الفاتورة غير موجودة', 404);
json_ok(['invoice' => $invoice]);
