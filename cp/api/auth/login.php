<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('طريقة الطلب غير مسموحة، استخدم POST', 405);
}

$data = request_data();

$email = strtolower(trim($data['email'] ?? ''));
$password = $data['password'] ?? '';

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_error('البريد الإلكتروني غير صحيح');
}

if ($password === '') {
    json_error('كلمة المرور مطلوبة');
}

$user = DB::fetch(
    "SELECT id, name, email, password, role, delete_requested_at, delete_after
     FROM users
     WHERE email = ?
     LIMIT 1",
    's',
    [$email]
);

if (!$user || !password_verify($password, $user['password'])) {
    json_error('البريد الإلكتروني أو كلمة المرور غير صحيحة', 401);
}

if (($user['role'] ?? '') !== 'admin') {
    json_error('هذا الحساب ليس حساب أدمن', 403);
}

if (!empty($user['delete_after'])) {
    json_error('حساب الأدمن مجدول للحذف، ألغِ طلب الحذف أولًا', 403);
}

$authKey = bin2hex(random_bytes(32));

$authExpiresTimestamp = time() + (60 * 60 * 24 * 7);
$authExpiresAt = date('Y-m-d H:i:s', $authExpiresTimestamp);

DB::query(
    'UPDATE users
     SET auth_key = ?, auth_expires_at = ?
     WHERE id = ?',
    'ssi',
    [$authKey, $authExpiresAt, $user['id']]
);

setcookie('admin_auth_key', $authKey, [
    'path' => '/' . APP_DIR . "/cp",
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);

unset($user['password'], $user['delete_requested_at'], $user['delete_after']);

json_ok([
    'message' => 'تم تسجيل دخول الأدمن بنجاح',
    'user' => $user
]);