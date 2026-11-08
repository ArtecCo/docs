<?php
declare(strict_types=1);
/*
 * Set your cPanel MySQL credentials here.
 * This file is protected by the root .htaccess. Do not publish real credentials.
 */
return [
    'db' => [
        'host' => 'localhost',
        'name' => 'CPANEL_DATABASE_NAME',
        'user' => 'CPANEL_DATABASE_USER',
        'pass' => 'CHANGE_THIS_PASSWORD',
        'charset' => 'utf8mb4',
    ],
    'storage_path' => __DIR__ . '/storage',
    'max_upload_bytes' => 15 * 1024 * 1024,
    'allowed_mimes' => [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ],
];
