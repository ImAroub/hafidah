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

if (empty($user['delete_after'])) {
    json_error('الحساب غير مجدول للحذف', 409);
}

DB::query(
    'UPDATE users
     SET delete_requested_at = NULL, delete_after = NULL
     WHERE id = ?',
    'i',
    [$userId]
);

json_ok([
    'message' => 'تم إلغاء طلب الحذف واستعادة الحساب بنجاح'
]);
