<?php
declare(strict_types=1);
session_start();
$configFile=__DIR__.'/config.php';
if (!is_file($configFile)) { http_response_code(500); exit('Configuration missing. Copy config.example.php to config.php and enter your database details.'); }
?><!doctype html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#101010"><meta name="description" content="Private document repository">
<link rel="manifest" href="manifest.webmanifest"><link rel="icon" href="assets/icon.svg" type="image/svg+xml"><link rel="stylesheet" href="assets/style.css">
<title>Araha Docs</title></head><body>
<header><a class="brand" href="./"><span class="mark">A</span><span><strong>Araha Docs</strong><small>Private document repository</small></span></a><button id="installBtn" class="button secondary" hidden>Install app</button></header>
<main><section class="top"><div><h1>Documents</h1><p id="count">Your private library</p></div><label class="button upload">＋ Upload documents<input id="fileInput" type="file" accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,application/pdf,image/jpeg,image/png,image/gif,image/webp" multiple></label></section>
<section class="filters"><input id="search" type="search" placeholder="Search documents…" autocomplete="off"><select id="tagFilter"><option value="">All tags</option></select></section>
<div id="notice" role="status" aria-live="polite"></div><section id="documents" class="documents"></section></main>
<dialog id="editor"><form id="editForm"><h2>Edit document</h2><label>Document name<input id="editName" maxlength="180" required></label><label>Tags<input id="editTags" placeholder="Separate tags with commas"></label><input type="hidden" id="editId"><div class="actions"><button type="button" class="button secondary" id="cancelEdit">Cancel</button><button class="button" type="submit">Save changes</button></div></form></dialog>
<dialog id="viewer" class="viewer"><div class="viewer-head"><strong id="viewerTitle">Preview</strong><div><a id="downloadLink" class="button secondary" target="_blank" rel="noopener">Download</a><button id="closeViewer" class="button secondary">Close</button></div></div><div id="viewerBody"></div></dialog>
<script src="assets/app.js" defer></script></body></html>
