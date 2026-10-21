<?php

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('طريقة الطلب غير مسموحة، استخدم GET', 405);
}

require_admin();

$search = trim($_GET['search'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$types = '';
$params = [];
$where = ["u.role = 'user'"];

if ($search !== '') {
    $where[] = '(u.name LIKE ? OR u.email LIKE ?)';
    $types .= 'ss';
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
}

$sql = 'SELECT
            u.id,
            u.name,
            u.email,
            u.delete_requested_at,
            u.delete_after,
            u.created_at,
            (SELECT COUNT(*) FROM invoices i WHERE i.user_id = u.id) AS invoices_count,
            (SELECT COUNT(*) FROM operations o INNER JOIN invoices i2 ON i2.id = o.invoice_id WHERE i2.user_id = u.id) AS operations_count
        FROM users u
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY u.created_at DESC, u.id DESC';

$result = DB::paging($sql, $types, $params, 10, $page);

json_ok([
    'users' => $result['data'],
    'pagination' => $result['pagination']
]);
