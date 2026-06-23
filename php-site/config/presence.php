<?php
/**
 * User online-presence helpers.
 * green  = active in last 2 minutes
 * orange = seen 2–15 minutes ago
 * gray   = seen > 15 minutes ago OR never
 */

function user_presence_status(?string $last_seen_at): string {
    if (!$last_seen_at) return 'offline';
    $diff = time() - strtotime($last_seen_at);
    if ($diff < 120)  return 'online';   // < 2 min
    if ($diff < 900)  return 'away';     // < 15 min
    return 'offline';
}

function user_presence_dot(?string $last_seen_at, bool $withTitle = true): string {
    $s = user_presence_status($last_seen_at);
    $colors = ['online'=>'#22c55e','away'=>'#f97316','offline'=>'#94a3b8'];
    $labels = ['online'=>'Online','away'=>'Away','offline'=>'Offline'];
    $c = $colors[$s];
    $t = $withTitle ? ' title="' . $labels[$s] . '"' : '';
    return '<span class="bf-presence-dot" style="display:inline-block;width:9px;height:9px;border-radius:50%;background:'.$c.';border:2px solid #fff;flex-shrink:0;"'.$t.'></span>';
}
