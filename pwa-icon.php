<?php
declare(strict_types=1);
$size=(int)($_GET['size']??512);
if(!in_array($size,[192,512],true)){http_response_code(404);exit;}
header('Content-Type: image/svg+xml; charset=utf-8');
header('Cache-Control: public, max-age=86400');
header('X-Content-Type-Options: nosniff');
echo <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
  <rect width="512" height="512" rx="112" fill="#ffffff"/>
  <path fill="#315b91" fill-rule="evenodd" d="M144 112h128c100 0 164 56 164 144s-64 144-164 144H144V112Zm88 76v136h38c52 0 82-25 82-68s-30-68-82-68h-38Z"/>
  <circle cx="294" cy="256" r="13" fill="#dc3545"/>
</svg>
SVG;
