<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('طريقة الطلب غير مسموحة، استخدم POST', 405);
}

require_admin();
$data = request_data();
$userId = (int) ($data['id'] ?? 0);

if ($userId <= 0) {
    json_error('رقم المستخدم غير صحيح');
}

$user = DB::fetch(
    "SELECT id, delete_after
     FROM users
     WHERE id = ? AND role = 'user'
     LIMIT 1",
    'i',
    [$userId]
);

if (!$user) {
    json_error('المستخدم غير موجود', 404);
}

if (!empty($user['delete_after'])) {
    json_error('الحساب مجدول للحذف مسبقًا', 409);
}

$deleteRequestedAt = date('Y-m-d H:i:s');
$deleteAfter = date('Y-m-d H:i:s', time() + (60 * 60 * 24 * 30));

DB::query(
    'UPDATE users
     SET delete_requested_at = ?, delete_after = ?, auth_key = NULL, auth_expires_at = NULL
     WHERE id = ?',
    'ssi',
    [$deleteRequestedAt, $deleteAfter, $userId]
);

json_ok([
    'message' => 'تم طلب حذف الحساب وسيتم حذف جميع بياناته نهائيًا بعد 30 يوم',
    'delete_after' => $deleteAfter
]);
