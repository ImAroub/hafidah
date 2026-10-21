<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('طريقة الطلب غير مسموحة، استخدم POST', 405);
}

$user = require_auth();

$deleteRequestedAt = date('Y-m-d H:i:s');
$deleteAfter = date('Y-m-d H:i:s', time() + (60 * 60 * 24 * 30));

DB::query(
    'UPDATE users
     SET delete_requested_at = ?, delete_after = ?, auth_key = NULL, auth_expires_at = NULL
     WHERE id = ?',
    'ssi',
    [$deleteRequestedAt, $deleteAfter, $user['id']]
);

clear_auth_cookie();

json_ok([
    'message' => 'تم طلب حذف الحساب وسيتم حذفه نهائيًا بعد 30 يوم',
    'delete_after' => $deleteAfter
]);