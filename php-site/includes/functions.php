<?php
/* Shared helpers, session + auth. Every page requires this file. */

require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ---- Output escaping ---- */
function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/* ---- App base path (works at web root OR in a subfolder) ---- */
function app_base() {
    static $base = null;
    if ($base !== null) return $base;
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    if (basename($dir) === 'admin') $dir = dirname($dir);          // up out of /admin
    $dir = ($dir === '/' || $dir === '.' || $dir === '\\') ? '' : rtrim($dir, '/');
    $base = $dir;
    return $base;
}
function url($path = '') {
    return app_base() . '/' . ltrim($path, '/');
}

/* ---- Redirect ---- */
function redirect($path) {
    header('Location: ' . url($path));
    exit;
}

/* ---- CSRF ---- */
function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}
function check_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) {
            http_response_code(400);
            die('Invalid form session. Please go back and try again.');
        }
    }
}

/* ---- Flash messages ---- */
function flash($msg = null, $type = 'success') {
    if ($msg !== null) {
        $_SESSION['flash'][] = ['msg' => $msg, 'type' => $type];
        return;
    }
    $out = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $out;
}

/* ---- Current user ---- */
function current_user() {
    global $mysqli;
    static $cached = false;
    static $user = null;
    if ($cached) return $user;
    $cached = true;
    if (empty($_SESSION['uid'])) return null;
    $stmt = $mysqli->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $_SESSION['uid']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
    return $user;
}
function is_logged_in() { return current_user() !== null; }

function require_login() {
    if (!is_logged_in()) {
        flash('Please log in to continue.', 'error');
        redirect('login.php');
    }
}
function require_role($roles) {
    require_login();
    $u = current_user();
    $roles = (array)$roles;
    if (!in_array($u['role'], $roles, true)) {
        http_response_code(403);
        die('You do not have permission to view this page.');
    }
}

/* ---- Domain constants ---- */
function trade_categories() {
    return ['ELECTRICAL','PLUMBING','CARPENTRY','CIVIL','TILING','PAINTING',
            'ROOFING','HVAC','LANDSCAPING','MASONRY','WELDING','GENERAL'];
}
function pro_levels() { return ['STARTER','VERIFIED','TOP_RATED']; }

function nice($s) { return ucwords(strtolower(str_replace('_', ' ', (string)$s))); }

function money($v) {
    if ($v === null || $v === '') return '—';
    return 'KES ' . number_format((float)$v, 0);
}

function time_ago($datetime) {
    $ts = strtotime($datetime);
    $diff = time() - $ts;
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff/60) . 'm ago';
    if ($diff < 86400) return floor($diff/3600) . 'h ago';
    if ($diff < 2592000) return floor($diff/86400) . 'd ago';
    return date('M j, Y', $ts);
}

/* ---- Status badge helper ---- */
function badge($text) {
    $t = strtolower($text);
    return '<span class="badge badge-' . e($t) . '">' . e(nice($text)) . '</span>';
}
