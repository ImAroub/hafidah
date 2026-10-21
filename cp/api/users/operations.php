<?php

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('طريقة الطلب غير مسموحة، استخدم GET', 405);
}

require_admin();

$userId = (int) ($_GET['user_id'] ?? 0);
$search = trim($_GET['search'] ?? '');
$operationType = trim($_GET['operation_type'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));

if ($userId <= 0) {
    json_error('رقم المستخدم غير صحيح');
}

$user = DB::fetch(
    "SELECT id, name, email
     FROM users
     WHERE id = ? AND role = 'user'
     LIMIT 1",
    'i',
    [$userId]
);

if (!$user) {
    json_error('المستخدم غير موجود', 404);
}

$where = ['i.user_id = ?'];
$types = 'i';
$params = [$userId];

if ($search !== '') {
    $where[] = 'i.item_name LIKE ?';

    $types .= 's';
    $params[] = '%' . $search . '%';
}

$allowedTypes = ['maintenance', 'maintenance_complete', 'replacement', 'return', 'renewal', 'cancel', 'other'];

if ($operationType !== '' && in_array($operationType, $allowedTypes, true)) {
    $where[] = 'o.operation_type = ?';

    $types .= 's';
    $params[] = $operationType;
}

$sql = 'SELECT o.id, o.operation_type, o.operation_date, o.cost, i.item_name, c.name AS category_name
        FROM operations o
        INNER JOIN invoices i ON i.id = o.invoice_id
        INNER JOIN categories c ON c.id = i.category_id
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY o.operation_date DESC, o.id DESC';

$result = DB::paging($sql, $types, $params, 10, $page);

json_ok([
    'user' => $user,
    'operations' => $result['data'],
    'pagination' => $result['pagination']
]);