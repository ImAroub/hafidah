<?php
return [
    'users' => [
        'id'                => 'INT AUTO_INCREMENT PRIMARY KEY',
        'name'              => 'VARCHAR(100) NOT NULL',
        'email'             => 'VARCHAR(150) NOT NULL UNIQUE',
        'password'          => 'VARCHAR(255) NOT NULL',
        'role'              => "ENUM('user', 'admin') NOT NULL DEFAULT 'user'",
        'auth_key'          => 'VARCHAR(64) DEFAULT NULL',
        'auth_expires_at'   => 'DATETIME DEFAULT NULL',
        'delete_requested_at' => 'DATETIME DEFAULT NULL',
        'delete_after'      => 'DATETIME DEFAULT NULL',
        'reset_code'        => 'VARCHAR(255) DEFAULT NULL',
        'reset_expires_at'  => 'DATETIME DEFAULT NULL',
        'created_at'        => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',

        'UNIQUE KEY uq_users_auth_key (auth_key)'
    ],

    'categories' => [
        'id'                => 'INT AUTO_INCREMENT PRIMARY KEY',
        'name'              => 'VARCHAR(100) NOT NULL UNIQUE',
        'type'              => "ENUM('product', 'subscription') NOT NULL DEFAULT 'product'",
        'created_at'        => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ],

    'invoices' => [
        'id'                => 'INT AUTO_INCREMENT PRIMARY KEY',
        'user_id'           => 'INT NOT NULL',
        'category_id'       => 'INT NOT NULL',
        'item_name'         => 'VARCHAR(100) NOT NULL',
        'store_name'        => 'VARCHAR(100) DEFAULT NULL',
        'contact_info'      => 'VARCHAR(100) DEFAULT NULL',
        'purchase_date'     => 'DATE NOT NULL',
        'cost'              => 'DECIMAL(10,2) DEFAULT NULL',

        'warranty_value'    => 'INT DEFAULT NULL',
        'warranty_unit'     => "ENUM('days', 'months', 'years') DEFAULT NULL",
        'warranty_end_date' => 'DATE DEFAULT NULL',

        'return_value'      => 'INT DEFAULT NULL',
        'return_unit'       => "ENUM('days', 'months', 'years') DEFAULT NULL",
        'return_end_date'   => 'DATE DEFAULT NULL',

        'replacement_value' => 'INT DEFAULT NULL',
        'replacement_unit'  => "ENUM('days', 'months', 'years') DEFAULT NULL",
        'replacement_end_date'=> 'DATE DEFAULT NULL',

        'renewal_value'     => 'INT DEFAULT NULL',
        'renewal_unit'      => "ENUM('days', 'months', 'years') DEFAULT NULL",
        'renewal_end_date'  => 'DATE DEFAULT NULL',

        'invoice_file_path' => 'VARCHAR(255) DEFAULT NULL',
        'item_status'       => "ENUM('active','maintenance','returned','replaced','cancelled') NOT NULL DEFAULT 'active'",
        'created_at'        => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'updated_at'        => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        
        'INDEX idx_invoices_user_id (user_id)',
        'INDEX idx_invoices_category_id (category_id)'

    ],

    'operations' => [
        'id'                => 'INT AUTO_INCREMENT PRIMARY KEY',
        'invoice_id'        => 'INT NOT NULL',
        'operation_type'    => "ENUM('maintenance','maintenance_complete','replacement','return','renewal','cancel','other') NOT NULL",
        'operation_date'    => 'DATE NOT NULL',
        'expected_end_date' => 'DATE DEFAULT NULL',
        'cost'              => 'DECIMAL(10,2) DEFAULT NULL',
        'notes'             => 'TEXT DEFAULT NULL',
        'operation_file_path'=> 'VARCHAR(255) DEFAULT NULL',
        'created_at'        => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',

        'INDEX idx_operations_invoice_id (invoice_id)',
        'INDEX idx_operations_type (operation_type)',
        'INDEX idx_operations_date (operation_date)'
    ]
];