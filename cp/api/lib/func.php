<?php

function json_ok(array $data = [], int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

function json_error(string $msg, int $statusCode = 400): void {
    http_response_code($statusCode);
    echo json_encode(['ok' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

function request_data(): array {
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

function clear_admin_auth_cookie(): void {
    setcookie('admin_auth_key', '', ['expires' => time() - 3600, 'path' => '/' . APP_DIR . "/cp", 'secure' => true, 'httponly' => true, 'samesite' => 'Lax']);
}

function require_admin(): array {
    $authKey = $_COOKIE['admin_auth_key'] ?? '';

    if ($authKey === '') {
        json_error('يجب تسجيل دخول الأدمن', 401);
    }

    $user = DB::fetch(
        "SELECT id, name, email, role, auth_expires_at
         FROM users
         WHERE auth_key = ?
         AND role = 'admin'
         LIMIT 1",
        's',
        [$authKey]
    );

    if (!$user) {
        clear_admin_auth_cookie();
        json_error('يجب تسجيل دخول الأدمن', 401);
    }

    if (!$user['auth_expires_at'] || strtotime($user['auth_expires_at']) <= time()) {
        DB::query(
            'UPDATE users SET auth_key = NULL, auth_expires_at = NULL WHERE id = ?',
            'i',
            [$user['id']]
        );

        clear_admin_auth_cookie();
        json_error('انتهت جلسة الأدمن، سجل الدخول مرة أخرى', 401);
    }

    unset($user['auth_expires_at']);

    return $user;
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