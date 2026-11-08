<?php
$configFile=dirname(__DIR__).'/config.php';
if(!is_file($configFile)){http_response_code(500);exit('Copy config.example.php to ../config.php and configure the database.');}
$config=require $configFile;
if(!empty($config['require_basic_auth']) && empty($_SERVER['REMOTE_USER'])){http_response_code(401);header('WWW-Authenticate: Basic realm="Araha Docs"');exit('Authentication required.');}
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#101010"><meta name="description" content="Private document repository">
<link rel="manifest" href="manifest.webmanifest"><link rel="stylesheet" href="assets/app.css">
<title>Araha Docs</title></head><body>
<header><div class="brand"><span class="mark">A</span><div><strong>Araha Docs</strong><small>Private document repository</small></div></div><button id="installBtn" class="quiet" hidden>Install app</button></header>
<main><section class="toolbar"><div><h1>Documents</h1><p id="count">Your private library</p></div><label class="upload-btn">＋ Upload documents<input id="fileInput" type="file" accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,application/pdf,image/jpeg,image/png,image/gif,image/webp" multiple></label></section>
<section class="filters"><input id="search" type="search" placeholder="Search documents…" autocomplete="off"><select id="tagFilter"><option value="">All tags</option></select></section>
<div id="notice" role="status"></div><section id="documents" class="documents"></section>
</main>
<dialog id="editor"><form method="dialog" id="editForm"><h2>Edit document</h2><input id="editName" maxlength="180" placeholder="Document name" required><input id="editTags" placeholder="Tags, separated by commas"><input type="hidden" id="editId"><div class="actions"><button value="cancel" class="quiet">Cancel</button><button id="saveEdit" value="default">Save changes</button></div></form></dialog>
<dialog id="viewer" class="viewer"><div class="viewer-head"><strong id="viewerTitle">Preview</strong><div><a id="downloadLink" class="quiet" target="_blank">Download</a><button id="closeViewer" class="quiet">Close</button></div></div><div id="viewerBody"></div></dialog>
<script src="assets/app.js" defer></script></body></html>
