<?php
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_hafitha');
define('DB_PORT', 3306);

define('ROOT', dirname(__DIR__).'/');
define('UPLOAD_PATH', dirname(__DIR__).'/uploads/');
define('APP_DIR', 'hafidah');
define('APP_URL', 'http://localhost/' . APP_DIR . '/');
define('APP_NAME', 'حافظة');
define('APP_TIMEZONE', 'Asia/Riyadh');

date_default_timezone_set(APP_TIMEZONE);