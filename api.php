<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/includes/functions.php';

$config = require __DIR__ . '/config.php';
header('Cache-Control: no-store, private, max-age=0');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');
header('Vary: Cookie, Authorization');

try {
    $db = $config['db'];
    $pdo = new PDO(
        'mysql:host='.$db['host'].';dbname='.$db['name'].';charset='.$db['charset'],
        $db['user'], $db['pass'],
        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES=>false]
    );
} catch (Throwable $e) {
    error_log('Araha Docs database connection failed: '.$e->getMessage());
    app_json(['error'=>'Database connection failed. Check config.php and database setup.'], 500);
}

$action = (string)($_GET['action'] ?? '');
if ($action === 'bootstrap') {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    app_json(['csrf'=>$_SESSION['csrf_token']]);
}

if ($action === 'list') {
    $query = trim((string)($_GET['q'] ?? ''));
    $tag = trim((string)($_GET['tag'] ?? ''));
    $sql = "SELECT d.id,d.name,d.mime_type,d.size_bytes,d.updated_at,
            GROUP_CONCAT(DISTINCT dt.tag ORDER BY dt.tag SEPARATOR ',') AS tags
            FROM documents d LEFT JOIN document_tags dt ON dt.document_id=d.id WHERE 1=1";
    $params = [];
    if ($query !== '') { $sql .= " AND d.name LIKE ?"; $params[] = '%'.$query.'%'; }
    if ($tag !== '') { $sql .= " AND EXISTS (SELECT 1 FROM document_tags x WHERE x.document_id=d.id AND x.tag=?)"; $params[] = $tag; }
    $sql .= " GROUP BY d.id ORDER BY d.updated_at DESC";
    $stmt=$pdo->prepare($sql); $stmt->execute($params);
    $documents=$stmt->fetchAll();
    foreach ($documents as &$document) $document['tags']=$document['tags'] ? explode(',', $document['tags']) : [];
    unset($document);
    $tags=$pdo->query('SELECT name FROM tags ORDER BY name')->fetchAll(PDO::FETCH_COLUMN);
    app_json(['documents'=>$documents,'tags'=>$tags]);
}

if ($action === 'upload') {
    app_require_post(); app_csrf();
    if (!isset($_FILES['file']) || is_array($_FILES['file']['name']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) app_json(['error'=>'Upload failed.'],400);
    $file=$_FILES['file'];
    if (!is_uploaded_file($file['tmp_name']) || $file['size'] < 1 || $file['size'] > (int)$config['max_upload_bytes']) app_json(['error'=>'File is empty or exceeds the 15 MB upload limit.'],400);
    $finfo=new finfo(FILEINFO_MIME_TYPE); $mime=$finfo->file($file['tmp_name']);
    if (!isset($config['allowed_mimes'][$mime])) app_json(['error'=>'Only PDF, JPG, PNG, GIF and WebP files are accepted.'],415);
    $name=trim(strip_tags((string)($_POST['name'] ?? pathinfo($file['name'],PATHINFO_FILENAME))));
    $name=function_exists('mb_substr') ? mb_substr($name,0,180,'UTF-8') : substr($name,0,180);
    if ($name==='') app_json(['error'=>'A document name is required.'],400);
    $tag=trim((string)($_POST['tag'] ?? ''));
    $tagCheck=$pdo->prepare('SELECT name FROM tags WHERE name=?'); $tagCheck->execute([$tag]);
    $validTag=$tagCheck->fetchColumn();
    if (!$validTag) app_json(['error'=>'Choose a valid tag from the list.'],400);
    $tags=[(string)$validTag];
    $storage=rtrim($config['storage_path'], DIRECTORY_SEPARATOR);
    if (!is_dir($storage) && !mkdir($storage,0700,true) && !is_dir($storage)) app_json(['error'=>'Cannot create storage directory.'],500);
    $key=bin2hex(random_bytes(24)).'.'.$config['allowed_mimes'][$mime];
    $path=app_storage_path($config,$key);
    if (!move_uploaded_file($file['tmp_name'],$path)) app_json(['error'=>'Could not save uploaded document.'],500);
    @chmod($path,0600);
    try {
        $pdo->beginTransaction();
        $stmt=$pdo->prepare('INSERT INTO documents (storage_key,name,mime_type,size_bytes,created_at,updated_at) VALUES (?,?,?,?,NOW(),NOW())');
        $stmt->execute([$key,$name,$mime,(int)$file['size']]);
        app_save_tags($pdo,(int)$pdo->lastInsertId(),$tags);
        $pdo->commit();
    } catch(Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        @unlink($path);
        error_log('Araha Docs upload failed: '.$e->getMessage());
        app_json(['error'=>'Could not save document metadata.'],500);
    }
    app_json(['ok'=>true]);
}

if ($action === 'update') {
    app_require_post(); app_csrf();
    $body=json_decode(file_get_contents('php://input'),true) ?: [];
    $id=(int)($body['id']??0); $name=trim(strip_tags((string)($body['name']??'')));
    $name=function_exists('mb_substr') ? mb_substr($name,0,180,'UTF-8') : substr($name,0,180);
    if ($id<1 || $name==='') app_json(['error'=>'Document name is required.'],400);
    $check=$pdo->prepare('SELECT id FROM documents WHERE id=?'); $check->execute([$id]);
    if (!$check->fetch()) app_json(['error'=>'Document not found.'],404);
    try {
        $pdo->beginTransaction();
        $pdo->prepare('UPDATE documents SET name=?,updated_at=NOW() WHERE id=?')->execute([$name,$id]);
        $tag=trim((string)($body['tag']??''));
        if ($tag !== '') {
            $tagCheck=$pdo->prepare('SELECT name FROM tags WHERE name=?'); $tagCheck->execute([$tag]);
            $validTag=$tagCheck->fetchColumn();
            if (!$validTag) throw new RuntimeException('Invalid tag selected');
            app_save_tags($pdo,$id,[(string)$validTag]);
        } else { app_save_tags($pdo,$id,[]); }
        $pdo->commit();
    } catch(Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('Araha Docs update failed: '.$e->getMessage());
        app_json(['error'=>'Could not update document.'],500);
    }
    app_json(['ok'=>true]);
}

if ($action === 'delete') {
    app_require_post(); app_csrf();
    $body=json_decode(file_get_contents('php://input'),true) ?: []; $id=(int)($body['id']??0);
    $stmt=$pdo->prepare('SELECT storage_key FROM documents WHERE id=?'); $stmt->execute([$id]); $doc=$stmt->fetch();
    if (!$doc) app_json(['error'=>'Document not found.'],404);
    $path=app_storage_path($config,$doc['storage_key']);
    $pdo->prepare('DELETE FROM documents WHERE id=?')->execute([$id]);
    if (is_file($path) && !unlink($path)) error_log('Araha Docs could not remove storage file for document '.$id);
    app_json(['ok'=>true]);
}

if ($action === 'file') {
    $id=(int)($_GET['id']??0);
    $stmt=$pdo->prepare('SELECT storage_key,name,mime_type,size_bytes FROM documents WHERE id=?'); $stmt->execute([$id]); $doc=$stmt->fetch();
    if (!$doc) { http_response_code(404); exit('Document not found.'); }
    $path=app_storage_path($config,$doc['storage_key']);
    if (!is_file($path)) { http_response_code(404); exit('Document file is missing.'); }
    $download=($_GET['mode']??'view')==='download';
    $extension=pathinfo($doc['storage_key'],PATHINFO_EXTENSION);
    $safeName=preg_replace('/[^A-Za-z0-9._ -]/','_', $doc['name']).'.'.$extension;
    header('Content-Type: '.$doc['mime_type']);
    header('Content-Length: '.(string)filesize($path));
    header('Content-Disposition: '.($download?'attachment':'inline').'; filename="'.addcslashes($safeName,'"\\').'"');
    header("Content-Security-Policy: default-src 'none'; sandbox");
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    readfile($path); exit;
}

app_json(['error'=>'Unknown action.'],404);
