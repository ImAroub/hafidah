<?php

if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_error('طريقة الطلب غير مسموحة، استخدم GET', 405);
require_admin();

$search = trim($_GET['search'] ?? '');
$operationType = trim($_GET['operation_type'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$where = ['1=1']; $types = ''; $params = [];

if ($search !== '') {
    $where[] = '(i.item_name LIKE ? OR u.name LIKE ? OR u.email LIKE ?)';
    $types .= 'sss'; $like = '%' . $search . '%'; array_push($params, $like, $like, $like);
}
$allowed = ['maintenance','maintenance_complete','replacement','return','renewal','cancel','other'];
if ($operationType !== '' && in_array($operationType, $allowed, true)) { $where[] = 'o.operation_type = ?'; $types .= 's'; $params[] = $operationType; }

$sql = 'SELECT o.id, o.operation_type, o.operation_date, o.cost, i.item_name, c.name AS category_name, u.name AS user_name
        FROM operations o
        INNER JOIN invoices i ON i.id = o.invoice_id
        INNER JOIN categories c ON c.id = i.category_id
        INNER JOIN users u ON u.id = i.user_id
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY o.operation_date DESC, o.id DESC';
$result = DB::paging($sql, $types, $params, 10, $page);
json_ok(['operations' => $result['data'], 'pagination' => $result['pagination']]);
