<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('طريقة الطلب غير مسموحة، استخدم POST', 405);
}

$user = require_auth();
$data = request_data();

$name = trim($data['name'] ?? '');
$email = strtolower(trim($data['email'] ?? ''));
$password = $data['password'] ?? '';
$passwordConfirmation = $data['password_confirmation'] ?? '';

if ($name === '') {
    json_error('الاسم مطلوب');
}

if (mb_strlen($name) < 2) {
    json_error('يجب أن يتكون الاسم من حرفين على الأقل');
}

if (mb_strlen($name) > 100) {
    json_error('يجب ألا يتجاوز الاسم 100 حرف');
}

if ($email === '') {
    json_error('البريد الإلكتروني مطلوب');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_error('صيغة البريد الإلكتروني غير صحيحة');
}

$existingUser = DB::fetch(
    'SELECT id
     FROM users
     WHERE email = ? AND id != ?
     LIMIT 1',
    'si',
    [$email, $user['id']]
);

if ($existingUser) {
    json_error('البريد الإلكتروني مستخدم من حساب آخر', 409);
}

if ($password !== '') {
    if (strlen($password) < 8) {
        json_error('يجب أن تتكون كلمة المرور من 8 أحرف على الأقل');
    }

    if ($password !== $passwordConfirmation) {
        json_error('كلمة المرور وتأكيد كلمة المرور غير متطابقتين');
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    if ($hashedPassword === false) {
        json_error('حدث خطأ أثناء معالجة كلمة المرور', 500);
    }

    DB::query(
        'UPDATE users
         SET name = ?, email = ?, password = ?
         WHERE id = ?',
        'sssi',
        [$name, $email, $hashedPassword, $user['id']]
    );
} else {
    DB::query(
        'UPDATE users
         SET name = ?, email = ?
         WHERE id = ?',
        'ssi',
        [$name, $email, $user['id']]
    );
}

json_ok([
    'message' => 'تم حفظ التعديلات بنجاح',
    'user' => [
        'id' => $user['id'],
        'name' => $name,
        'email' => $email,
        'role' => $user['role']
    ]
]);