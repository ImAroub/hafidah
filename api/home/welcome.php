<?php

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('طريقة الطلب غير مسموحة، استخدم GET', 405);
}

json_ok([
    'message' => 'مرحبًا بك في API نظام ' . APP_NAME
]);