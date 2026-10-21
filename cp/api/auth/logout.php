<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('طريقة الطلب غير مسموحة، استخدم POST', 405);
}

$admin = require_admin();

DB::query(
    'UPDATE users
     SET auth_key = NULL, auth_expires_at = NULL
     WHERE id = ?',
    'i',
    [$admin['id']]
);

clear_admin_auth_cookie();

json_ok([
    'message' => 'تم تسجيل خروج الأدمن بنجاح'
]);