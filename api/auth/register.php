<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('طريقة الطلب غير مسموحة، استخدم POST', 405);
}

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

if ($password === '') {
    json_error('كلمة المرور مطلوبة');
}

if (strlen($password) < 8) {
    json_error('يجب أن تتكون كلمة المرور من 8 أحرف على الأقل');
}

if ($password !== $passwordConfirmation) {
    json_error('كلمة المرور وتأكيد كلمة المرور غير متطابقتين');
}

$existingUser = DB::fetch(
    'SELECT id 
    FROM users 
    WHERE email = ? 
    LIMIT 1',
    's',
    [$email]
);

if ($existingUser) {
    json_error('البريد الإلكتروني مسجل مسبقًا', 409);
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

if ($hashedPassword === false) {
    json_error('حدث خطأ أثناء معالجة كلمة المرور', 500);
}

$userId = DB::insert(
    'INSERT INTO users (name, email, password)
     VALUES (?, ?, ?)',
    'sss',
    [$name, $email, $hashedPassword]
);

json_ok([
        'message' => 'تم إنشاء الحساب بنجاح',    
        'user_id' => $userId
    ], 201);