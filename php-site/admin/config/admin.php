<?php
/**
 * Back-office bootstrap. Included by every /admin page.
 * Uses its OWN session cookie so the back-office is fully isolated from the
 * public marketplace / member dashboard.
 */
if (!defined('ADMIN_NAME'))   define('ADMIN_NAME', 'bildfie Admin');
if (!defined('ADMIN_ASSETS')) define('ADMIN_ASSETS', '/admin/assets');
if (!defined('ASSETS_URL'))   define('ASSETS_URL', '/assets'); // reuse shared design tokens

if (session_status() === PHP_SESSION_NONE) {
    session_name('bildfie_admin');   // distinct cookie → total separation from members
    session_start();
}

require_once __DIR__ . '/../../config/db.php';   // back-office runs on the real database
require_once __DIR__ . '/admin_auth.php';

/** Inline SVG sparkline (area + line) for KPI tiles. */
function adm_spark(array $d, string $color = '#16a34a'): string {
    $n = count($d);
    if ($n < 2) return '';
    $w = 100; $h = 26; $mn = min($d); $mx = max($d); $rng = ($mx - $mn) ?: 1;
    $step = $w / ($n - 1); $pts = [];
    foreach ($d as $i => $v) {
        $x = round($i * $step, 1);
        $y = round($h - 2 - (($v - $mn) / $rng) * ($h - 4), 1);
        $pts[] = "$x,$y";
    }
    $line = implode(' ', $pts);
    $svg  = '<svg viewBox="0 0 ' . $w . ' ' . $h . '" preserveAspectRatio="none" width="100%" height="' . $h . '">';
    $svg .= '<polyline points="0,' . $h . ' ' . $line . ' ' . $w . ',' . $h . '" fill="' . $color . '" fill-opacity="0.12" stroke="none"/>';
    $svg .= '<polyline points="' . $line . '" fill="none" stroke="' . $color . '" stroke-width="1.6" stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke"/>';
    $svg .= '</svg>';
    return $svg;
}
