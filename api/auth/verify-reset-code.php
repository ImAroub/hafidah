<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('طريقة الطلب غير مسموحة، استخدم POST', 405);
}

$data = request_data();

$email = strtolower(trim($data['email'] ?? ''));
$resetCode = trim($data['reset_code'] ?? '');

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

$user = DB::fetch(
    'SELECT
        id,
        reset_code,
        reset_expires_at
     FROM users
     WHERE email = ?
     LIMIT 1',
    's',
    [$email]
);

if (!$user || empty($user['reset_code'])) {
    json_error('رمز الاستعادة غير صحيح', 400);
}

if (
    empty($user['reset_expires_at'])
    || strtotime($user['reset_expires_at']) < time()
) {
    json_error('انتهت صلاحية رمز الاستعادة', 400);
}

if (!password_verify($resetCode, $user['reset_code'])) {
    json_error('رمز الاستعادة غير صحيح', 400);
}

json_ok([
    'message' => 'تم التحقق من رمز الاستعادة بنجاح'
]);