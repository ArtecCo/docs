<?php
declare(strict_types=1);

function app_json(array $data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
function app_require_post(): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') app_json(['error' => 'Method not allowed'], 405);
}
function app_csrf(): void {
    $provided = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (empty($_SESSION['csrf_token']) || !is_string($provided) || !hash_equals($_SESSION['csrf_token'], $provided)) {
        app_json(['error' => 'Security token expired. Refresh the page and try again.'], 403);
    }
}
function app_tags(string $input): array {
    $result = [];
    foreach (preg_split('/[,;]+/u', $input) ?: [] as $tag) {
        $tag = trim(strip_tags($tag));
        if (function_exists('mb_substr')) $tag = mb_substr($tag, 0, 40, 'UTF-8');
        else $tag = substr($tag, 0, 40);
        if ($tag !== '') $result[function_exists('mb_strtolower') ? mb_strtolower($tag, 'UTF-8') : strtolower($tag)] = $tag;
    }
    return array_values($result);
}
function app_save_tags(PDO $pdo, int $documentId, array $tags): void {
    $pdo->prepare('DELETE FROM document_tags WHERE document_id = ?')->execute([$documentId]);
    $insert = $pdo->prepare('INSERT INTO document_tags (document_id, tag) VALUES (?, ?)');
    foreach ($tags as $tag) $insert->execute([$documentId, $tag]);
}
function app_storage_path(array $config, string $key): string {
    // Only generated hexadecimal IDs with a known extension can reach the filesystem.
    if (!preg_match('/^[a-f0-9]{48}\\.(pdf|jpg|png|gif|webp)$/', $key)) {
        throw new RuntimeException('Invalid storage identifier');
    }
    return rtrim($config['storage_path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $key;
}
