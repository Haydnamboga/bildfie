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
        // Spec §5.3 — exactly 3 trust badges. Hard to earn. Do not add more.
        //   NCA Verified   — NCA licence number confirmed.
        //   bildfie Vetted — 10+ jobs completed at 4.5+ rating (awarded automatically).
        //   Insured        — valid Contractor All Risk certificate on file.
        return [
            'is_certified'  => ['NCA Verified',   'bi-patch-check-fill',    '#f0fdf4', '#166534'],
            'is_preferred'  => ['bildfie Vetted', 'bi-hand-thumbs-up-fill', '#eef2ff', '#4338ca'],
            'is_insured'    => ['Insured',        'bi-shield-fill-check',   '#eff6ff', '#1e40af'],
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
