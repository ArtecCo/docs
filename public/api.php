<?php
declare(strict_types=1);
session_start();
$configFile = dirname(__DIR__) . '/config.php';
if (!is_file($configFile)) { http_response_code(500); exit('Application is not configured.'); }
$config = require $configFile;
if (!empty($config['require_basic_auth']) && empty($_SERVER['REMOTE_USER'])) {
    http_response_code(401);
    header('WWW-Authenticate: Basic realm="Araha Docs"');
    exit('Authentication required.');
}
header('Cache-Control: no-store, private, max-age=0');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');
header('Vary: Authorization');
$pdo = new PDO(
    'mysql:host='.$config['db']['host'].';dbname='.$config['db']['name'].';charset='.$config['db']['charset'],
    $config['db']['user'], $config['db']['pass'],
    [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES=>false]
);
function jsonOut(array $data, int $status=200): never {
    http_response_code($status); header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES); exit;
}
function requirePost(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonOut(['error'=>'Method not allowed'],405);
}
function csrfCheck(): void {
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token)) jsonOut(['error'=>'Invalid security token'],403);
}
function cleanTags(string $raw): array {
    $parts = preg_split('/[,;]+/', $raw) ?: [];
    $out = [];
    foreach ($parts as $p) {
        $p = trim(mb_substr(strip_tags($p),0,40));
        if ($p !== '') $out[mb_strtolower($p)] = $p;
    }
    return array_values($out);
}
function saveTags(PDO $pdo, int $id, array $tags): void {
    $pdo->prepare('DELETE FROM document_tags WHERE document_id=?')->execute([$id]);
    foreach ($tags as $tag) {
        $pdo->prepare('INSERT INTO document_tags (document_id, tag) VALUES (?,?)')->execute([$id,$tag]);
    }
}
$action = $_GET['action'] ?? '';
if ($action === 'bootstrap') {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    jsonOut(['csrf'=>$_SESSION['csrf']]);
}
if ($action === 'list') {
    $q = trim((string)($_GET['q'] ?? ''));
    $tag = trim((string)($_GET['tag'] ?? ''));
    $sql = "SELECT d.id,d.name,d.mime_type,d.size_bytes,d.updated_at,GROUP_CONCAT(DISTINCT t.tag ORDER BY t.tag SEPARATOR ',') AS tags
            FROM documents d LEFT JOIN document_tags t ON t.document_id=d.id WHERE 1=1";
    $params=[];
    if ($q !== '') { $sql .= " AND d.name LIKE ?"; $params[]='%'.$q.'%'; }
    if ($tag !== '') { $sql .= " AND EXISTS (SELECT 1 FROM document_tags x WHERE x.document_id=d.id AND x.tag=?)"; $params[]=$tag; }
    $sql .= " GROUP BY d.id ORDER BY d.updated_at DESC";
    $st=$pdo->prepare($sql); $st->execute($params);
    $docs=$st->fetchAll();
    foreach($docs as &$d) { $d['tags']=$d['tags'] ? explode(',',$d['tags']) : []; }
    $tags=$pdo->query('SELECT DISTINCT tag FROM document_tags ORDER BY tag')->fetchAll(PDO::FETCH_COLUMN);
    jsonOut(['documents'=>$docs,'tags'=>$tags]);
}
if ($action === 'upload') {
    requirePost(); csrfCheck();
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) jsonOut(['error'=>'Upload failed'],400);
    $f=$_FILES['file'];
    if ($f['size'] < 1 || $f['size'] > (int)$config['max_upload_bytes']) jsonOut(['error'=>'File is empty or exceeds the upload limit'],400);
    $fi=new finfo(FILEINFO_MIME_TYPE); $mime=$fi->file($f['tmp_name']);
    $allowed=['application/pdf'=>'pdf','image/jpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif','image/webp'=>'webp'];
    if (!isset($allowed[$mime])) jsonOut(['error'=>'Only PDF and supported image files are allowed'],415);
    $name=trim(mb_substr(strip_tags((string)($_POST['name'] ?? pathinfo($f['name'],PATHINFO_FILENAME))),0,180));
    if ($name==='') jsonOut(['error'=>'Document name is required'],400);
    $tags=cleanTags((string)($_POST['tags'] ?? ''));
    $storage=rtrim($config['storage_path'],'/');
    if (!is_dir($storage) && !mkdir($storage,0700,true)) jsonOut(['error'=>'Private storage is unavailable'],500);
    $key=bin2hex(random_bytes(24)).'.'.$allowed[$mime];
    if (!move_uploaded_file($f['tmp_name'],$storage.'/'.$key)) jsonOut(['error'=>'Could not store file'],500);
    try {
        $pdo->beginTransaction();
        $st=$pdo->prepare('INSERT INTO documents (storage_key,name,mime_type,size_bytes,created_at,updated_at) VALUES (?,?,?,?,NOW(),NOW())');
        $st->execute([$key,$name,$mime,(int)$f['size']]); $id=(int)$pdo->lastInsertId();
        saveTags($pdo,$id,$tags); $pdo->commit();
    } catch(Throwable $e) { if($pdo->inTransaction())$pdo->rollBack(); @unlink($storage.'/'.$key); throw $e; }
    jsonOut(['ok'=>true]);
}
if ($action === 'update') {
    requirePost(); csrfCheck();
    $body=json_decode(file_get_contents('php://input'),true) ?: [];
    $id=(int)($body['id']??0); $name=trim(mb_substr(strip_tags((string)($body['name']??'')),0,180));
    if($id<1 || $name==='') jsonOut(['error'=>'Invalid document details'],400);
    $st=$pdo->prepare('UPDATE documents SET name=?,updated_at=NOW() WHERE id=?'); $st->execute([$name,$id]);
    if(!$st->rowCount() && !$pdo->query('SELECT 1 FROM documents WHERE id='.(int)$id)->fetchColumn()) jsonOut(['error'=>'Document not found'],404);
    saveTags($pdo,$id,cleanTags((string)($body['tags']??'')));
    jsonOut(['ok'=>true]);
}
if ($action === 'delete') {
    requirePost(); csrfCheck();
    $body=json_decode(file_get_contents('php://input'),true) ?: []; $id=(int)($body['id']??0);
    $st=$pdo->prepare('SELECT storage_key FROM documents WHERE id=?'); $st->execute([$id]); $doc=$st->fetch();
    if(!$doc) jsonOut(['error'=>'Document not found'],404);
    $pdo->prepare('DELETE FROM documents WHERE id=?')->execute([$id]);
    @unlink(rtrim($config['storage_path'],'/').'/'.$doc['storage_key']);
    jsonOut(['ok'=>true]);
}
if ($action === 'file') {
    $id=(int)($_GET['id']??0); $mode=($_GET['mode']??'view')==='download'?'attachment':'inline';
    $st=$pdo->prepare('SELECT storage_key,name,mime_type,size_bytes FROM documents WHERE id=?'); $st->execute([$id]); $doc=$st->fetch();
    if(!$doc) { http_response_code(404); exit('Not found'); }
    $path=rtrim($config['storage_path'],'/').'/'.$doc['storage_key'];
    if(!is_file($path)) { http_response_code(404); exit('File missing'); }
    $ext=pathinfo($doc['storage_key'],PATHINFO_EXTENSION);
    $downloadName=preg_replace('/[^A-Za-z0-9._ -]/','_', $doc['name']).'.'.$ext;
    header('Content-Type: '.$doc['mime_type']);
    header('Content-Length: '.filesize($path));
    header('Content-Disposition: '.$mode.'; filename="'.addcslashes($downloadName,'"\\').'"');
    header('Content-Security-Policy: default-src \'none\'; sandbox');
    readfile($path); exit;
}
jsonOut(['error'=>'Unknown action'],404);
