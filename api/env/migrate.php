<?php
require_once __DIR__ . '/config.php';

$schema = require __DIR__ . '/db_schema.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, '', DB_PORT);
if ($conn->connect_error) {
    exit("Connection failed: " . $conn->connect_error);
}

$conn->set_charset('utf8mb4');

$createDatabaseSQL= "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci" ;
if (!$conn->query($createDatabaseSQL)) {
    exit("Error creating database: " . $conn->error);
}
$conn->select_db(DB_NAME);

function tableToSQL(string $table, array $columns): string
{
    $lines = [];

    foreach ($columns as $name => $definition) {
        if (is_int($name)) {
            $lines[] = $definition;
        } else {
            $lines[] = "`{$name}` {$definition}";
        }
    }

    return "CREATE TABLE IF NOT EXISTS `{$table}` (
        " . implode(",\n", $lines) . "
    )
    ENGINE=InnoDB
    DEFAULT CHARSET=utf8mb4
    COLLATE=utf8mb4_unicode_ci";
}

foreach ($schema as $table => $columns) {
    $sql = tableToSQL($table, $columns);
    if (!$conn->query($sql)) {
        exit("Error creating table {$table}: " . $conn->error);
    }
        echo" Table {$table} created successfully.<br>";
}

echo '<br> Database and tables created successfully.<br>';
$conn->close();
