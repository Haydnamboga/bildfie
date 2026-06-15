<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Lightweight health check (Laravel also exposes /up). Confirms app + DB are alive.
Route::get('/health', function () {
    $db = 'unknown';
    try {
        DB::connection()->getPdo();
        $db = 'ok';
    } catch (\Throwable $e) {
        $db = 'unavailable';
    }

    return response()->json([
        'app' => 'bildfie',
        'status' => 'ok',
        'database' => $db,
        'time' => now()->toIso8601String(),
    ]);
});

// Phase 1 entry points (UI flows built in Phase 1 — see docs/ROADMAP.md).
// Routed now so the homepage CTAs never 404 and show a helpful prompt (spec §9).
foreach (['/post-a-project', '/join', '/login'] as $path) {
    Route::get($path, fn () => view('coming-soon', [
        'action' => trim($path, '/'),
    ]));
}
