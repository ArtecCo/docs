<?php
declare(strict_types=1);
session_start();
$configFile=__DIR__.'/config.php';
if (!is_file($configFile)) { http_response_code(500); exit('Configuration missing. Copy config.example.php to config.php and enter your database details.'); }
?><!doctype html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#ffffff"><meta name="description" content="Private document vault">
<link rel="manifest" href="manifest.webmanifest"><link rel="stylesheet" href="assets/style.css?v=4">
<title>Araha DocuVault</title></head><body>
<header class="site-header"><div class="header-inner"><a class="brand" href="./">Araha DocuVault</a><button id="installBtn" class="button button-light" hidden>Install app</button></div></header>
<main>
  <section class="page-heading"><div><div class="eyebrow">PRIVATE LIBRARY</div><h1>Documents</h1><p id="count">Your secure document collection</p></div><label class="button upload">Upload documents<input id="fileInput" type="file" accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,application/pdf,image/jpeg,image/png,image/gif,image/webp" multiple></label></section>
  <section class="toolbar"><div class="search-wrap"><input id="search" type="search" placeholder="Search documents by name…" autocomplete="off"></div><select id="tagFilter" aria-label="Filter by tag"><option value="">All tags</option></select></section>
  <div id="notice" role="status" aria-live="polite"></div>
  <section id="documents" class="documents"></section>
  <footer class="footer">Araha DocuVault <span>·</span> Private document storage</footer>
</main>
<dialog id="editor"><form id="editForm"><div class="dialog-heading"><div><div class="eyebrow">DOCUMENT DETAILS</div><h2>Edit document</h2></div><button type="button" class="icon-button" id="cancelEdit" aria-label="Close">×</button></div><label>Document name<input id="editName" maxlength="180" required></label><label>Tags<input id="editTags" placeholder="Separate tags with commas"></label><input type="hidden" id="editId"><div class="actions"><button type="button" class="button button-light" id="cancelEditBottom">Cancel</button><button class="button" type="submit">Save changes</button></div></form></dialog>
<dialog id="viewer" class="viewer"><div class="viewer-head"><div><div class="eyebrow">DOCUMENT PREVIEW</div><strong id="viewerTitle">Preview</strong></div><div class="viewer-actions"><a id="downloadLink" class="button button-light" target="_blank" rel="noopener">Download</a><button id="closeViewer" class="button button-light">Close</button></div></div><div id="viewerBody"></div></dialog>
<script src="assets/app.js?v=4" defer></script></body></html>
