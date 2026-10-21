<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('طريقة الطلب غير مسموحة، استخدم POST', 405);
}

$authKey = $_COOKIE['auth_key'] ?? '';

if ($authKey !== '') {
    DB::query(
        'UPDATE users 
        SET auth_key = NULL, 
        auth_expires_at = NULL 
        WHERE auth_key = ?', 
        's', 
        [$authKey]
        );
}

clear_auth_cookie();

json_ok([
    'message' => 'تم تسجيل الخروج بنجاح'
]);