<?php

function debug(mixed $val):void{
    echo '<pre>';
    print_r($val);
    echo '</pre>';
}

function json_ok(array $data = [], int $statusCode=200): void {
    http_response_code($statusCode);
    echo json_encode(
        [
            'ok'   => true,
            'data' => $data
        ],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

function json_error(string $msg, int $statusCode = 400): void {
    http_response_code($statusCode);
    echo json_encode(
        [
            'ok'    => false,
            'error' => $msg
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

function request_data(): array{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (str_contains($contentType, 'application/json')) {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            json_error('صيغة JSON غير صحيحة');
        }
        return is_array($data) ? $data : [];
    }
    return $_POST;
}

function cookie($key, $value, $expire = 0): void {
    setcookie($key, $value, [
        'expires' => $expire,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

function clear_auth_cookie(): void {
    setcookie('auth_key', '', ['expires' => time() - 3600, 'path' => '/', 'secure' => true, 'httponly' => true, 'samesite' => 'Lax']);
}

function require_auth(): array {
    $authKey = $_COOKIE['auth_key'] ?? '';

    if ($authKey === '') {
        json_error('يجب تسجيل الدخول', 401);
    }

    $user = DB::fetch(
        "SELECT id, name, email, role, auth_expires_at
         FROM users
         WHERE auth_key = ?
         AND role = 'user'
         LIMIT 1",
        's',
        [$authKey]
    );

    if (!$user) {
        clear_auth_cookie();
        json_error('يجب تسجيل الدخول', 401);
    }

    if (!$user['auth_expires_at'] || strtotime($user['auth_expires_at']) <= time()) {
        DB::query(
            'UPDATE users
             SET auth_key = NULL, auth_expires_at = NULL
             WHERE id = ?',
            'i',
            [$user['id']]
        );

        clear_auth_cookie();
        json_error('انتهت الجلسة، سجل الدخول مرة أخرى', 401);
    }

    unset($user['auth_expires_at']);

    return $user;
}

function calculate_end_date(DateTimeImmutable $startDate,int $value,string $unit): string {
    $allowedUnits = ['days', 'months', 'years'];

    if ($value <= 0) {
        json_error('قيمة المدة يجب أن تكون أكبر من صفر');
    }

    if (!in_array($unit, $allowedUnits, true)) {
        json_error('وحدة المدة يجب أن تكون days أو months أو years');
    }

    $interval = match ($unit) {
        'days' => new DateInterval("P{$value}D"),
        'months' => new DateInterval("P{$value}M"),
        'years' => new DateInterval("P{$value}Y")
    };

    return $startDate ->add($interval) ->format('Y-m-d');
}

function uploadImage(string $directory,array $file,int $maxSizeMB = 5,int $quality = 80): string {
    
    if (!isset($file['error'], $file['size'], $file['tmp_name'] )) {
        json_error('بيانات الملف غير صحيحة');
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        json_error('فشل رفع الصورة');
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        json_error('الملف المرفوع غير صالح');
    }

    $maxSize = $maxSizeMB * 1024 * 1024;

    if ($file['size'] <= 0 ||$file['size'] > $maxSize) {
        json_error("حجم الصورة يجب ألا يتجاوز {$maxSizeMB} ميجابايت");
    }

    $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);

    $allowedTypes = ['image/jpeg','image/png','image/webp'];

    if (!in_array($mimeType, $allowedTypes, true)) {
        json_error('نوع الصورة غير مسموح');
    }

    $directory = trim(str_replace('\\', '/', $directory),'/');

    if ($directory === '' || str_contains($directory, '..')) {
        json_error('مسار مجلد الرفع غير صحيح', 500);
    }

    $uploadDirectory = UPLOAD_PATH . $directory;

     if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true)){
            json_error('تعذر إنشاء مجلد الصور', 500);
        }
    
    $image = match ($mimeType) {
        'image/jpeg' => imagecreatefromjpeg($file['tmp_name']),
        'image/png'  => imagecreatefrompng($file['tmp_name']),
        'image/webp' => imagecreatefromwebp($file['tmp_name']),
        default      => false
    };

    if ($image === false) {
        json_error('تعذر قراءة الصورة', 500);
    }

    if (!imageistruecolor($image)) {
        imagepalettetotruecolor($image);
    }

    imagealphablending($image, false);
    imagesavealpha($image, true);

    $fileName = time() . '_' .bin2hex(random_bytes(8)) .'.webp';
    $fullPath = $uploadDirectory . '/' . $fileName;
    $quality = max(0, min(100, $quality));
    
    $saved = imagewebp($image, $fullPath, $quality);

    imagedestroy($image);

    if (!$saved) {
        json_error('تعذر حفظ الصورة', 500);
    }

    return $fileName;// uploads/invoices/78782.webp
}

function is_valid_date(string $date): bool{
    $dateObject = DateTime::createFromFormat(
        '!Y-m-d',
        $date
    );

    return $dateObject !== false
        && $dateObject->format('Y-m-d') === $date;
}

function get_allowed_operation_types(string $categoryType): array {
    if ($categoryType === 'subscription') {
        return ['renewal', 'cancel', 'other'];
    }

    return ['maintenance', 'maintenance_complete', 'replacement', 'return', 'other'];
}

function get_invoice_status_by_operation(string $operationType): ?string {
    $statuses = [
        'maintenance' => 'maintenance',
        'maintenance_complete' => 'active',
        'replacement' => 'replaced',
        'return' => 'returned',
        'renewal' => 'active',
        'cancel' => 'cancelled'
    ];

    return $statuses[$operationType] ?? null;
}

function delete_uploaded_image(string $directory, ?string $fileName): void {
    if (!$fileName) {
        return;
    }

    $directory = trim(str_replace('\\', '/', $directory), '/');
    $filePath = UPLOAD_PATH . $directory . '/' . basename($fileName);

    if (is_file($filePath)) {
        unlink($filePath);
    }
}

function sync_invoice_status(int $invoiceId, int $userId): void {
    $latestOperation = DB::fetch(
        'SELECT operation_type
         FROM operations
         WHERE invoice_id = ?
         AND operation_type != ?
         ORDER BY operation_date DESC, id DESC
         LIMIT 1',
        'is',
        [$invoiceId, 'other']
    );

    $invoiceStatus = $latestOperation ? get_invoice_status_by_operation($latestOperation['operation_type']) : 'active';

    DB::query(
        'UPDATE invoices
         SET item_status = ?
         WHERE id = ?
         AND user_id = ?',
        'sii',
        [$invoiceStatus ?? 'active', $invoiceId, $userId]
    );
}

function delete_user_completely(int $userId): void {
    $invoices = DB::fetchAll(
        'SELECT id, invoice_file_path
         FROM invoices
         WHERE user_id = ?',
        'i',
        [$userId]
    );

    foreach ($invoices as $invoice) {
        $operations = DB::fetchAll(
            'SELECT operation_file_path
             FROM operations
             WHERE invoice_id = ?',
            'i',
            [$invoice['id']]
        );

        foreach ($operations as $operation) {
            if (!empty($operation['operation_file_path'])) {
                delete_uploaded_image('operations', $operation['operation_file_path']);
            }
        }

        DB::query(
            'DELETE FROM operations
             WHERE invoice_id = ?',
            'i',
            [$invoice['id']]
        );

        if (!empty($invoice['invoice_file_path'])) {
            delete_uploaded_image('invoices', $invoice['invoice_file_path']);
        }
    }

    DB::query(
        'DELETE FROM invoices
         WHERE user_id = ?',
        'i',
        [$userId]
    );

    DB::query(
        'DELETE FROM users
         WHERE id = ?',
        'i',
        [$userId]
    );
}

function cleanup_expired_accounts(): void {
    $users = DB::fetchAll(
        'SELECT id
         FROM users
         WHERE role = ?
         AND delete_after IS NOT NULL
         AND delete_after <= NOW()',
        's',
        ['user']
    );

    foreach ($users as $user) {
        delete_user_completely((int) $user['id']);
    }
}