<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('طريقة الطلب غير مسموحة، استخدم POST', 405);
}

$data = request_data();

$email = strtolower(trim($data['email'] ?? ''));
$resetCode = trim($data['reset_code'] ?? '');

$password = $data['password'] ?? '';
$passwordConfirmation = $data['password_confirmation'] ?? '';

if ($email === '') {
    json_error('البريد الإلكتروني مطلوب');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_error('صيغة البريد الإلكتروني غير صحيحة');
}

if ($resetCode === '') {
    json_error('رمز الاستعادة مطلوب');
}

if (!preg_match('/^\d{6}$/', $resetCode)) {
    json_error('رمز الاستعادة يجب أن يتكون من 6 أرقام');
}

if ($password === '') {
    json_error('كلمة المرور الجديدة مطلوبة');
}

if (strlen($password) < 8) {
    json_error('يجب أن تتكون كلمة المرور من 8 أحرف على الأقل');
}

if ($password !== $passwordConfirmation) {
    json_error('كلمة المرور وتأكيد كلمة المرور غير متطابقتين');
}

$user = DB::fetch(
    'SELECT id, reset_code, reset_expires_at
     FROM users
     WHERE email = ?
     LIMIT 1',
    's',
    [$email]
);

if (!$user || empty($user['reset_code'])) {
    json_error('رمز الاستعادة غير صحيح', 400);
}

if (empty($user['reset_expires_at'])|| strtotime($user['reset_expires_at']) < time()) {
    json_error('انتهت صلاحية رمز الاستعادة', 400);
}

if (!password_verify($resetCode, $user['reset_code'])) {
    json_error('رمز الاستعادة غير صحيح', 400);
}

$hashedPassword = password_hash($password,PASSWORD_DEFAULT);

if ($hashedPassword === false) {
    json_error('تعذر معالجة كلمة المرور الجديدة', 500);
}

DB::query(
    'UPDATE users
     SET password = ?,
         reset_code = NULL,
         reset_expires_at = NULL,
         auth_key = NULL
     WHERE id = ?',
    'si',
    [$hashedPassword,$user['id']]
);

setcookie(
    'auth_key','',
    [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax'
    ]
);

json_ok([
    'message' => 'تم تغيير كلمة المرور بنجاح'
]);