<?php

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('طريقة الطلب غير مسموحة، استخدم GET', 405);
}

require_admin();

$userId = (int) ($_GET['user_id'] ?? 0);
$search = trim($_GET['search'] ?? '');
$categoryId = (int) ($_GET['category_id'] ?? 0);
$status = trim($_GET['status'] ?? '');
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
    $where[] = '(i.item_name LIKE ? OR i.store_name LIKE ?)';

    $like = '%' . $search . '%';

    $types .= 'ss';
    $params[] = $like;
    $params[] = $like;
}

if ($categoryId > 0) {
    $where[] = 'i.category_id = ?';

    $types .= 'i';
    $params[] = $categoryId;
}

$allowedStatuses = ['active', 'maintenance', 'returned', 'replaced', 'cancelled'];

if ($status !== '' && in_array($status, $allowedStatuses, true)) {
    $where[] = 'i.item_status = ?';

    $types .= 's';
    $params[] = $status;
}

$sql = 'SELECT i.id, i.item_name, i.store_name, i.purchase_date, i.item_status, c.name AS category_name
        FROM invoices i
        INNER JOIN categories c ON c.id = i.category_id
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY i.created_at DESC, i.id DESC';

$result = DB::paging($sql, $types, $params, 10, $page);

json_ok([
    'user' => $user,
    'invoices' => $result['data'],
    'pagination' => $result['pagination']
]);