<?php
declare(strict_types=1);

$size = (int)($_GET['size'] ?? 512);
if (!in_array($size, [192, 512], true)) {
    http_response_code(404);
    exit;
}

header('Content-Type: image/svg+xml; charset=utf-8');
header('Cache-Control: public, max-age=86400');
header('X-Content-Type-Options: nosniff');

echo <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
<defs>
 <linearGradient id="paper" x1="110" y1="90" x2="400" y2="430" gradientUnits="userSpaceOnUse"><stop stop-color="#fff"/><stop offset="1" stop-color="#f3f7ff"/></linearGradient>
 <linearGradient id="accent" x1="150" y1="140" x2="370" y2="390" gradientUnits="userSpaceOnUse"><stop stop-color="#60a5fa"/><stop offset="1" stop-color="#4f46e5"/></linearGradient>
 <filter id="shadow" x="60" y="55" width="400" height="410" color-interpolation-filters="sRGB" filterUnits="userSpaceOnUse"><feGaussianBlur stdDeviation="18"/></filter>
</defs>
<rect width="512" height="512" rx="116" fill="#fff"/>
<rect x="104" y="104" width="292" height="320" rx="48" fill="#cbd5e1" opacity=".24" filter="url(#shadow)"/>
<path d="M170 112h137l72 72v204a32 32 0 0 1-32 32H170a32 32 0 0 1-32-32V144a32 32 0 0 1 32-32Z" fill="url(#paper)" stroke="#e2e8f0" stroke-width="3"/>
<path d="M307 113v58a16 16 0 0 0 16 16h57" fill="#eff6ff"/>
<path d="M307 113v58a16 16 0 0 0 16 16h57" fill="none" stroke="#dbeafe" stroke-width="3"/>
<rect x="185" y="230" width="143" height="14" rx="7" fill="#cbd5e1"/>
<rect x="185" y="270" width="112" height="14" rx="7" fill="#cbd5e1"/>
<rect x="185" y="310" width="132" height="14" rx="7" fill="#cbd5e1"/>
<rect x="185" y="350" width="72" height="14" rx="7" fill="url(#accent)"/>
<circle cx="348" cy="348" r="54" fill="url(#accent)"/>
<path d="M330 348l13 13 25-28" fill="none" stroke="#fff" stroke-width="12" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
SVG;
