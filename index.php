<?php
declare(strict_types=1);
session_start();
$configFile=__DIR__.'/config.php';
if (!is_file($configFile)) { http_response_code(500); exit('Configuration missing. Copy config.example.php to config.php and enter your database details.'); }
?><!doctype html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#ffffff"><meta name="description" content="Private document vault">
<link rel="icon" type="image/svg+xml" href="favicon.svg?v=2">
<link rel="manifest" href="manifest.webmanifest?v=10"><link rel="stylesheet" href="assets/style.css?v=10">
<title>Araha DocuVault</title></head><body>
<header class="site-header"><div class="header-inner"><a class="brand" href="./">Araha DocuVault</a><button id="installBtn" class="button button-light" hidden>Install app</button></div></header>
<main>
  <section class="page-heading"><div><div class="eyebrow">PRIVATE LIBRARY</div><h1>Documents</h1><p id="count">Your secure document collection</p></div><button type="button" class="button upload" id="openUpload">Upload document</button></section>
  <section class="toolbar"><div class="search-wrap"><input id="search" type="search" placeholder="Search documents by name…" autocomplete="off"></div><select id="tagFilter" aria-label="Filter by tag"><option value="">All tags</option></select></section>
  <div id="notice" role="status" aria-live="polite"></div>
  <section id="documents" class="documents"></section>
  <footer class="footer">Araha DocuVault <span>·</span> Private document storage</footer>
</main>
<dialog id="uploadDialog"><form id="uploadForm"><div class="dialog-heading"><div><div class="eyebrow">ADD TO YOUR VAULT</div><h2>Upload document</h2></div><button type="button" class="icon-button" id="closeUpload" aria-label="Close">×</button></div><label>Document name<input id="uploadName" name="name" maxlength="180" placeholder="Enter document name" required></label><label>Tag<select id="uploadTag" name="tag" required><option value="">Loading tags…</option></select></label><label class="file-picker">Choose PDF or image<input id="uploadFile" name="file" type="file" accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,application/pdf,image/jpeg,image/png,image/gif,image/webp" required><span id="selectedFile">No file selected</span></label><div class="actions"><button type="button" class="button button-light" id="cancelUpload">Cancel</button><button class="button" type="submit" id="submitUpload">Upload document</button></div></form></dialog>
<dialog id="editor"><form id="editForm"><div class="dialog-heading"><div><div class="eyebrow">DOCUMENT DETAILS</div><h2>Edit document</h2></div><button type="button" class="icon-button" id="cancelEdit" aria-label="Close">×</button></div><label>Document name<input id="editName" maxlength="180" required></label><label>Tag<select id="editTag"><option value="">No tag</option></select></label><input type="hidden" id="editId"><div class="actions"><button type="button" class="button button-light" id="cancelEditBottom">Cancel</button><button class="button" type="submit">Save changes</button></div></form></dialog>
<dialog id="viewer" class="viewer"><div class="viewer-head"><div><div class="eyebrow">DOCUMENT PREVIEW</div><strong id="viewerTitle">Preview</strong></div><div class="viewer-actions"><a id="downloadLink" class="button button-light">Download</a><button id="closeViewer" class="button button-light">Close</button></div></div><div id="pdfToolbar" class="pdf-toolbar" hidden><button type="button" class="button button-light" id="prevPage">Previous</button><span id="pageStatus">Page 1 of 1</span><button type="button" class="button button-light" id="nextPage">Next</button><span class="toolbar-divider"></span><button type="button" class="button button-light" id="zoomOut" aria-label="Zoom out">−</button><span id="zoomStatus">100%</span><button type="button" class="button button-light" id="zoomIn" aria-label="Zoom in">+</button><button type="button" class="button button-light" id="fitWidth">Fit width</button></div><div id="viewerBody"></div></dialog>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js" defer></script><script src="assets/app.js?v=10" defer></script></body></html>