<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('طريقة الطلب غير مسموحة، استخدم POST', 405);
}

$data = request_data();

$email = strtolower(trim($data['email'] ?? ''));
$password = $data['password'] ?? '';
$restoreAccount = !empty($data['restore_account']);

if ($email === '') {
    json_error('البريد الإلكتروني مطلوب');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_error('صيغة البريد الإلكتروني غير صحيحة');
}

if ($password === '') {
    json_error('كلمة المرور مطلوبة');
}

$user = DB::fetch(
    'SELECT id, name, email, password, role, delete_requested_at, delete_after
     FROM users
     WHERE email = ?
     LIMIT 1',
    's',
    [$email]
);

if (!$user) {
    json_error('البريد الإلكتروني أو كلمة المرور غير صحيحة', 401);
}

if (!password_verify($password, $user['password'])) {
    json_error('البريد الإلكتروني أو كلمة المرور غير صحيحة', 401);
}

if (($user['role'] ?? '') !== 'user') {
    json_error('هذا الحساب مخصص للوحة التحكم', 403);
}

if (!empty($user['delete_after'])) {
    if (strtotime($user['delete_after']) <= time()) {
        json_error('انتهت مهلة استعادة الحساب وسيتم حذف بيانات الحساب نهائيًا', 403);
    }

    if (!$restoreAccount) {
        $deleteDate = date('Y-m-d', strtotime($user['delete_after']));

        json_ok([
            'requires_restore' => true,
            'message' => "هذا الحساب مجدول للحذف بتاريخ {$deleteDate}. هل تريد إلغاء الحذف واستعادة الحساب؟"
        ]);
    }

    DB::query(
        'UPDATE users
         SET delete_requested_at = NULL, delete_after = NULL
         WHERE id = ?',
        'i',
        [$user['id']]
    );

    $user['delete_requested_at'] = null;
    $user['delete_after'] = null;
}

$authKey = bin2hex(random_bytes(32));
$authExpiresTimestamp = time() + (60 * 60 * 24 * 7);
$authExpiresAt = date('Y-m-d H:i:s', $authExpiresTimestamp);

DB::query(
    'UPDATE users 
    SET auth_key = ?, 
    auth_expires_at = ? 
    WHERE id = ?', 
    'ssi', 
    [$authKey, $authExpiresAt, $user['id']]
    );

cookie('auth_key', $authKey);

unset($user['password'], $user['delete_requested_at'], $user['delete_after']);

json_ok([
    'message' => 'تم تسجيل الدخول بنجاح',
    'user' => $user
]);