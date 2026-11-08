<?php
// Copy to ../config.php relative to public/ (outside the subdomain document root).
return [
    'db' => [
        'host' => 'localhost',
        'name' => 'DATABASE_NAME',
        'user' => 'DATABASE_USER',
        'pass' => 'DATABASE_PASSWORD',
        'charset' => 'utf8mb4',
    ],
    'storage_path' => __DIR__ . '/private-documents',
    'max_upload_bytes' => 15 * 1024 * 1024,
    'require_basic_auth' => true,
];
