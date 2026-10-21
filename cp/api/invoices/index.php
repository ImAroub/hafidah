<?php

if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_error('طريقة الطلب غير مسموحة، استخدم GET', 405);
require_admin();

$search = trim($_GET['search'] ?? '');
$categoryId = (int) ($_GET['category_id'] ?? 0);
$status = trim($_GET['status'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$where = ['1=1'];
$types = '';
$params = [];

if ($search !== '') {
    $where[] = '(i.item_name LIKE ? OR i.store_name LIKE ? OR u.name LIKE ? OR u.email LIKE ?)';
    $types .= 'ssss';
    $like = '%' . $search . '%';
    array_push($params, $like, $like, $like, $like);
}
if ($categoryId > 0) { $where[] = 'i.category_id = ?'; $types .= 'i'; $params[] = $categoryId; }
$allowedStatuses = ['active','maintenance','returned','replaced','cancelled'];
if ($status !== '' && in_array($status, $allowedStatuses, true)) { $where[] = 'i.item_status = ?'; $types .= 's'; $params[] = $status; }

$sql = 'SELECT i.id, i.item_name, i.store_name, i.purchase_date, i.item_status, c.name AS category_name, u.name AS user_name
        FROM invoices i
        INNER JOIN categories c ON c.id = i.category_id
        INNER JOIN users u ON u.id = i.user_id
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY i.created_at DESC, i.id DESC';
$result = DB::paging($sql, $types, $params, 10, $page);
json_ok(['invoices' => $result['data'], 'pagination' => $result['pagination']]);
