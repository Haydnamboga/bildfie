<?php
/**
 * Provider trust badges — single source of truth.
 * Drives the admin Users row toggles, the public profile pills, and the
 * marketplace cards. Pure (no DB/session) so it loads in member AND admin contexts.
 *
 * To add a badge: add ONE row here + ONE column on `providers` (migration).
 * Each badge is fully independent — awarded/removed on its own.
 *
 *   column => [ label, bootstrap-icon, pill-bg, pill-color ]   (array order = display priority)
 */
if (!function_exists('provider_trust_defs')) {
    function provider_trust_defs(): array {
        return [
            'is_elite'      => ['Elite Pro',             'bi-gem',                  '#1e3a5f', '#ffffff'],
            'is_preferred'  => ['bildfie Choice',        'bi-hand-thumbs-up-fill',  '#eef2ff', '#4338ca'],
            'is_top_rated'  => ['Top Rated',             'bi-award-fill',           '#fdf6e3', '#9a7d27'],
            'is_verified'   => ['ID Verified',           'bi-person-badge-fill',    '#eaf0f6', '#1e3a5f'],
            'is_certified'  => ['Licence / NCA Certified','bi-patch-check-fill',    '#f0fdf4', '#166534'],
            'is_insured'    => ['Insured',               'bi-shield-fill-check',    '#eff6ff', '#1e40af'],
            'is_warranty'   => ['Warranty Offered',      'bi-clipboard2-check',     '#f0fdf4', '#15803d'],
            'is_escrow'     => ['Escrow Ready',          'bi-lock-fill',            '#ecfeff', '#0e7490'],
            'is_fast_responder' => ['Fast Responder',    'bi-lightning-charge-fill','#fffbeb', '#b45309'],
            'is_featured'   => ['Premium',               'bi-star-fill',            '#fff7ed', '#c2410c'],
        ];
    }
}
if (!function_exists('provider_badges')) {
    /** Active trust badges for a provider row → list of [label, icon, bg, color].
     *  $skip excludes flags a surface renders separately (e.g. tier). */
    function provider_badges(array $p, array $skip = []): array {
        $out = [];
        foreach (provider_trust_defs() as $col => $def) {
            if (in_array($col, $skip, true)) continue;
            if (!empty($p[$col])) $out[] = $def;
        }
        return $out;
    }
}
