<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('طريقة الطلب غير مسموحة، استخدم POST', 405);
}

$data = request_data();

$email = strtolower(trim($data['email'] ?? ''));

if ($email === '') {
    json_error('البريد الإلكتروني مطلوب');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_error('صيغة البريد الإلكتروني غير صحيحة');
}

$user = DB::fetch(
    'SELECT id
     FROM users
     WHERE email = ?
     LIMIT 1',
    's',
    [$email]
);

if (!$user) {
    json_error('لا يوجد مستخدم بهذا البريد الإلكتروني', 404);
}

$resetCode = (string) random_int(100000, 999999);

$hashedResetCode = password_hash(
    $resetCode,
    PASSWORD_DEFAULT
);

if ($hashedResetCode === false) {
    json_error('تعذر إنشاء رمز استعادة كلمة المرور', 500);
}

$expiresAt = date(
    'Y-m-d H:i:s',
    time() + (10 * 60)
);

DB::query(
    'UPDATE users
     SET reset_code = ?,
         reset_expires_at = ?
     WHERE id = ?',
    'ssi',
    [
        $hashedResetCode,
        $expiresAt,
        $user['id']
    ]
);

json_ok([
    'message' => 'تم إنشاء رمز استعادة كلمة المرور',
    'reset_code' => $resetCode,
    'expires_in_minutes' => 10
]);