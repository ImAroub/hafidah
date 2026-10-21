<?php
require_once __DIR__ . '/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($conn->connect_error) {
    exit('Database connection failed: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');

function seedDate(string $modifier): string {
    return date('Y-m-d', strtotime($modifier));
}

function getCategoryId(mysqli $conn, string $name): int {
    $stmt = $conn->prepare('SELECT id FROM categories WHERE name = ? LIMIT 1');
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return (int) ($row['id'] ?? 0);
}

function getUserId(mysqli $conn, string $email): int {
    $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return (int) ($row['id'] ?? 0);
}

function insertInvoice(mysqli $conn, array $invoice): int {
    $stmt = $conn->prepare(
        'INSERT INTO invoices (
            user_id, category_id, item_name, store_name, contact_info, purchase_date, cost,
            warranty_value, warranty_unit, warranty_end_date,
            return_value, return_unit, return_end_date,
            replacement_value, replacement_unit, replacement_end_date,
            renewal_value, renewal_unit, renewal_end_date,
            invoice_file_path, item_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    $stmt->bind_param(
        'iissssdississississss',
        $invoice['user_id'],
        $invoice['category_id'],
        $invoice['item_name'],
        $invoice['store_name'],
        $invoice['contact_info'],
        $invoice['purchase_date'],
        $invoice['cost'],
        $invoice['warranty_value'],
        $invoice['warranty_unit'],
        $invoice['warranty_end_date'],
        $invoice['return_value'],
        $invoice['return_unit'],
        $invoice['return_end_date'],
        $invoice['replacement_value'],
        $invoice['replacement_unit'],
        $invoice['replacement_end_date'],
        $invoice['renewal_value'],
        $invoice['renewal_unit'],
        $invoice['renewal_end_date'],
        $invoice['invoice_file_path'],
        $invoice['item_status']
    );

    $stmt->execute();
    return $conn->insert_id;
}

function insertOperation(mysqli $conn, int $invoiceId, string $type, string $date, ?string $expectedEndDate, ?float $cost, string $notes): void {
    $stmt = $conn->prepare(
        'INSERT INTO operations (invoice_id, operation_type, operation_date, expected_end_date, cost, notes, operation_file_path)
         VALUES (?, ?, ?, ?, ?, ?, NULL)'
    );
    $stmt->bind_param('isssds', $invoiceId, $type, $date, $expectedEndDate, $cost, $notes);
    $stmt->execute();
}

$categories = [
    ['إلكترونيات', 'product'],
    ['أجهزة منزلية', 'product'],
    ['أزياء', 'product'],
    ['صحة وجمال', 'product'],
    ['أثاث', 'product'],
    ['اشتراكات رقمية', 'subscription']
];

$categoryStmt = $conn->prepare('INSERT INTO categories (name, type) VALUES (?, ?) ON DUPLICATE KEY UPDATE type = VALUES(type)');
foreach ($categories as [$name, $type]) {
    $categoryStmt->bind_param('ss', $name, $type);
    $categoryStmt->execute();
}

$userPassword = password_hash('12345678', PASSWORD_DEFAULT);
$adminPassword = password_hash('12345678', PASSWORD_DEFAULT);

$userName = 'Ruby';
$userEmail = 'q@test.com';
$userRole = 'user';
$userStmt = $conn->prepare(
    'INSERT INTO users (name, email, password, role)
     VALUES (?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE name = VALUES(name), password = VALUES(password), role = VALUES(role), delete_requested_at = NULL, delete_after = NULL'
);
$userStmt->bind_param('ssss', $userName, $userEmail, $userPassword, $userRole);
$userStmt->execute();

$adminName = 'Admin';
$adminEmail = 'admin@test.com';
$adminRole = 'admin';
$adminStmt = $conn->prepare(
    'INSERT INTO users (name, email, password, role)
     VALUES (?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE name = VALUES(name), password = VALUES(password), role = VALUES(role)'
);
$adminStmt->bind_param('ssss', $adminName, $adminEmail, $adminPassword, $adminRole);
$adminStmt->execute();

$userId = getUserId($conn, $userEmail);

if ($userId <= 0) {
    exit('Could not create demo user.');
}

$oldInvoices = [];
$stmt = $conn->prepare('SELECT id FROM invoices WHERE user_id = ?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $oldInvoices[] = (int) $row['id'];
}

if ($oldInvoices) {
    $placeholders = implode(',', array_fill(0, count($oldInvoices), '?'));
    $types = str_repeat('i', count($oldInvoices));
    $stmt = $conn->prepare("DELETE FROM operations WHERE invoice_id IN ($placeholders)");
    $stmt->bind_param($types, ...$oldInvoices);
    $stmt->execute();
}

$stmt = $conn->prepare('DELETE FROM invoices WHERE user_id = ?');
$stmt->bind_param('i', $userId);
$stmt->execute();

$electronicsId = getCategoryId($conn, 'إلكترونيات');
$appliancesId = getCategoryId($conn, 'أجهزة منزلية');
$fashionId = getCategoryId($conn, 'أزياء');
$beautyId = getCategoryId($conn, 'صحة وجمال');
$furnitureId = getCategoryId($conn, 'أثاث');
$subscriptionsId = getCategoryId($conn, 'اشتراكات رقمية');

$invoices = [
    [
        'user_id' => $userId, 'category_id' => $electronicsId, 'item_name' => 'لابتوب Dell', 'store_name' => 'جرير', 'contact_info' => '920000089',
        'purchase_date' => seedDate('-11 months'), 'cost' => 3499.00,
        'warranty_value' => 1, 'warranty_unit' => 'years', 'warranty_end_date' => seedDate('+7 days'),
        'return_value' => 7, 'return_unit' => 'days', 'return_end_date' => seedDate('-10 months -23 days'),
        'replacement_value' => 14, 'replacement_unit' => 'days', 'replacement_end_date' => seedDate('-10 months -16 days'),
        'renewal_value' => null, 'renewal_unit' => null, 'renewal_end_date' => null, 'invoice_file_path' => null, 'item_status' => 'active'
    ],
    [
        'user_id' => $userId, 'category_id' => $electronicsId, 'item_name' => 'سماعات AirPods', 'store_name' => 'إكسترا', 'contact_info' => '920004123',
        'purchase_date' => seedDate('-20 days'), 'cost' => 799.00,
        'warranty_value' => 1, 'warranty_unit' => 'years', 'warranty_end_date' => seedDate('+11 months +10 days'),
        'return_value' => 14, 'return_unit' => 'days', 'return_end_date' => seedDate('-6 days'),
        'replacement_value' => 30, 'replacement_unit' => 'days', 'replacement_end_date' => seedDate('+10 days'),
        'renewal_value' => null, 'renewal_unit' => null, 'renewal_end_date' => null, 'invoice_file_path' => null, 'item_status' => 'active'
    ],
    [
        'user_id' => $userId, 'category_id' => $appliancesId, 'item_name' => 'ماكينة قهوة', 'store_name' => 'أمازون', 'contact_info' => 'amazon.sa',
        'purchase_date' => seedDate('-8 months'), 'cost' => 549.00,
        'warranty_value' => 1, 'warranty_unit' => 'years', 'warranty_end_date' => seedDate('+4 months'),
        'return_value' => 15, 'return_unit' => 'days', 'return_end_date' => seedDate('-7 months -15 days'),
        'replacement_value' => 30, 'replacement_unit' => 'days', 'replacement_end_date' => seedDate('-7 months'),
        'renewal_value' => null, 'renewal_unit' => null, 'renewal_end_date' => null, 'invoice_file_path' => null, 'item_status' => 'maintenance'
    ],
    [
        'user_id' => $userId, 'category_id' => $appliancesId, 'item_name' => 'مكنسة كهربائية', 'store_name' => 'ساكو', 'contact_info' => '920001530',
        'purchase_date' => seedDate('-3 months'), 'cost' => 899.00,
        'warranty_value' => 2, 'warranty_unit' => 'years', 'warranty_end_date' => seedDate('+21 months'),
        'return_value' => 7, 'return_unit' => 'days', 'return_end_date' => seedDate('-2 months -23 days'),
        'replacement_value' => 14, 'replacement_unit' => 'days', 'replacement_end_date' => seedDate('-2 months -16 days'),
        'renewal_value' => null, 'renewal_unit' => null, 'renewal_end_date' => null, 'invoice_file_path' => null, 'item_status' => 'replaced'
    ],
    [
        'user_id' => $userId, 'category_id' => $fashionId, 'item_name' => 'حقيبة جلد', 'store_name' => 'نمشي', 'contact_info' => 'namshi.com',
        'purchase_date' => seedDate('-12 days'), 'cost' => 320.00,
        'warranty_value' => null, 'warranty_unit' => null, 'warranty_end_date' => null,
        'return_value' => 14, 'return_unit' => 'days', 'return_end_date' => seedDate('+2 days'),
        'replacement_value' => null, 'replacement_unit' => null, 'replacement_end_date' => null,
        'renewal_value' => null, 'renewal_unit' => null, 'renewal_end_date' => null, 'invoice_file_path' => null, 'item_status' => 'active'
    ],
    [
        'user_id' => $userId, 'category_id' => $fashionId, 'item_name' => 'حذاء رياضي', 'store_name' => 'أديداس', 'contact_info' => 'adidas.sa',
        'purchase_date' => seedDate('-35 days'), 'cost' => 450.00,
        'warranty_value' => null, 'warranty_unit' => null, 'warranty_end_date' => null,
        'return_value' => 30, 'return_unit' => 'days', 'return_end_date' => seedDate('-5 days'),
        'replacement_value' => null, 'replacement_unit' => null, 'replacement_end_date' => null,
        'renewal_value' => null, 'renewal_unit' => null, 'renewal_end_date' => null, 'invoice_file_path' => null, 'item_status' => 'returned'
    ],
    [
        'user_id' => $userId, 'category_id' => $beautyId, 'item_name' => 'جهاز تصفيف شعر', 'store_name' => 'نايس ون', 'contact_info' => 'niceonesa.com',
        'purchase_date' => seedDate('-5 months'), 'cost' => 699.00,
        'warranty_value' => 1, 'warranty_unit' => 'years', 'warranty_end_date' => seedDate('+7 months'),
        'return_value' => 7, 'return_unit' => 'days', 'return_end_date' => seedDate('-4 months -23 days'),
        'replacement_value' => 30, 'replacement_unit' => 'days', 'replacement_end_date' => seedDate('-4 months'),
        'renewal_value' => null, 'renewal_unit' => null, 'renewal_end_date' => null, 'invoice_file_path' => null, 'item_status' => 'active'
    ],
    [
        'user_id' => $userId, 'category_id' => $furnitureId, 'item_name' => 'كرسي مكتب', 'store_name' => 'IKEA', 'contact_info' => 'ikea.com.sa',
        'purchase_date' => seedDate('-10 months'), 'cost' => 649.00,
        'warranty_value' => 1, 'warranty_unit' => 'years', 'warranty_end_date' => seedDate('+2 months'),
        'return_value' => 90, 'return_unit' => 'days', 'return_end_date' => seedDate('-7 months'),
        'replacement_value' => null, 'replacement_unit' => null, 'replacement_end_date' => null,
        'renewal_value' => null, 'renewal_unit' => null, 'renewal_end_date' => null, 'invoice_file_path' => null, 'item_status' => 'active'
    ],
    [
        'user_id' => $userId, 'category_id' => $subscriptionsId, 'item_name' => 'اشتراك Adobe', 'store_name' => 'Adobe', 'contact_info' => 'adobe.com',
        'purchase_date' => seedDate('-11 months -20 days'), 'cost' => 239.00,
        'warranty_value' => null, 'warranty_unit' => null, 'warranty_end_date' => null,
        'return_value' => null, 'return_unit' => null, 'return_end_date' => null,
        'replacement_value' => null, 'replacement_unit' => null, 'replacement_end_date' => null,
        'renewal_value' => 1, 'renewal_unit' => 'years', 'renewal_end_date' => seedDate('+10 days'), 'invoice_file_path' => null, 'item_status' => 'active'
    ],
    [
        'user_id' => $userId, 'category_id' => $subscriptionsId, 'item_name' => 'اشتراك Canva', 'store_name' => 'Canva', 'contact_info' => 'canva.com',
        'purchase_date' => seedDate('-2 months'), 'cost' => 55.00,
        'warranty_value' => null, 'warranty_unit' => null, 'warranty_end_date' => null,
        'return_value' => null, 'return_unit' => null, 'return_end_date' => null,
        'replacement_value' => null, 'replacement_unit' => null, 'replacement_end_date' => null,
        'renewal_value' => 1, 'renewal_unit' => 'months', 'renewal_end_date' => seedDate('+4 days'), 'invoice_file_path' => null, 'item_status' => 'active'
    ],
    [
        'user_id' => $userId, 'category_id' => $electronicsId, 'item_name' => 'شاشة Samsung', 'store_name' => 'نون', 'contact_info' => 'noon.com',
        'purchase_date' => seedDate('-18 months'), 'cost' => 1299.00,
        'warranty_value' => 2, 'warranty_unit' => 'years', 'warranty_end_date' => seedDate('+6 months'),
        'return_value' => 15, 'return_unit' => 'days', 'return_end_date' => seedDate('-17 months -15 days'),
        'replacement_value' => 30, 'replacement_unit' => 'days', 'replacement_end_date' => seedDate('-17 months'),
        'renewal_value' => null, 'renewal_unit' => null, 'renewal_end_date' => null, 'invoice_file_path' => null, 'item_status' => 'active'
    ],
    [
        'user_id' => $userId, 'category_id' => $electronicsId, 'item_name' => 'ساعة ذكية', 'store_name' => 'جرير', 'contact_info' => '920000089',
        'purchase_date' => seedDate('-6 months'), 'cost' => 1199.00,
        'warranty_value' => 1, 'warranty_unit' => 'years', 'warranty_end_date' => seedDate('+6 months'),
        'return_value' => 7, 'return_unit' => 'days', 'return_end_date' => seedDate('-5 months -23 days'),
        'replacement_value' => 14, 'replacement_unit' => 'days', 'replacement_end_date' => seedDate('-5 months -16 days'),
        'renewal_value' => null, 'renewal_unit' => null, 'renewal_end_date' => null, 'invoice_file_path' => null, 'item_status' => 'cancelled'
    ]
];

$invoiceIds = [];
foreach ($invoices as $invoice) {
    $invoiceIds[] = insertInvoice($conn, $invoice);
}

insertOperation($conn, $invoiceIds[2], 'maintenance', seedDate('-3 days'), seedDate('+5 days'), 120.00, 'تم إرسال ماكينة القهوة للصيانة بسبب مشكلة في التسخين.');
insertOperation($conn, $invoiceIds[3], 'replacement', seedDate('-1 month'), null, 0.00, 'تم استبدال المنتج بقطعة جديدة ضمن الضمان.');
insertOperation($conn, $invoiceIds[5], 'return', seedDate('-4 days'), null, 0.00, 'تم استرجاع المنتج وإعادة المبلغ.');
insertOperation($conn, $invoiceIds[8], 'renewal', seedDate('-1 month'), null, 239.00, 'تم تجديد الاشتراك السنوي.');
insertOperation($conn, $invoiceIds[9], 'renewal', seedDate('-26 days'), null, 55.00, 'تم تجديد الاشتراك الشهري.');
insertOperation($conn, $invoiceIds[10], 'maintenance', seedDate('-2 months'), seedDate('-2 months +7 days'), 85.00, 'صيانة للشاشة بسبب مشكلة في الإضاءة.');
insertOperation($conn, $invoiceIds[10], 'maintenance_complete', seedDate('-2 months +6 days'), null, 85.00, 'اكتملت الصيانة وعاد المنتج للحالة النشطة.');
insertOperation($conn, $invoiceIds[11], 'cancel', seedDate('-10 days'), null, 0.00, 'تم إلغاء استخدام المنتج وإغلاق المتابعة.');
insertOperation($conn, $invoiceIds[0], 'other', seedDate('-15 days'), null, 0.00, 'تم تحديث بيانات التواصل الخاصة بالضمان.');

$conn->close();

echo '<h3>Seeder completed successfully.</h3>';
echo '<p>User: q@test.com / 12345678</p>';
echo '<p>Admin: admin@test.com / 12345678</p>';
echo '<p>Demo data: 6 categories, 12 invoices, and 9 operations.</p>';