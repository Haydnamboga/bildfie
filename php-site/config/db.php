<?php
/**
 * Database connection (MariaDB / MySQL via mysqli).
 * Production credentials live in config/config.local.php (NOT committed).
 * Any value may also be supplied as an environment variable of the same name.
 */
if (is_file(__DIR__ . '/config.local.php')) require_once __DIR__ . '/config.local.php';

if (!defined('APP_ENV')) define('APP_ENV', getenv('APP_ENV') ?: 'local');
if (APP_ENV === 'production') {
    ini_set('display_errors', '0'); ini_set('log_errors', '1');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
} else {
    ini_set('display_errors', '1'); error_reporting(E_ALL);
}

if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
if (!defined('DB_PORT')) define('DB_PORT', (int)(getenv('DB_PORT') ?: 3306));
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'bildfie');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: '');

/** Shared mysqli connection (singleton). Throws on any error. */
function db(): mysqli {
    static $conn = null;
    if ($conn === null) {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

/** Run a raw statement (DDL or no-param SQL). */
function db_exec(string $sql): void {
    db()->query($sql);
}

/** Prepare + bind (types inferred) + execute. Returns the executed stmt. */
function db_stmt(string $sql, array $params = []): mysqli_stmt {
    $stmt = db()->prepare($sql);
    if ($params) {
        $types = '';
        foreach ($params as $p) {
            $types .= is_int($p) ? 'i' : (is_float($p) ? 'd' : 's');
        }
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt;
}

/** Fetch all rows as associative arrays. */
function db_all(string $sql, array $params = []): array {
    $stmt = db_stmt($sql, $params);
    $res  = $stmt->get_result();
    $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $rows;
}

/** Fetch a single row (or null). */
function db_one(string $sql, array $params = []): ?array {
    $rows = db_all($sql, $params);
    return $rows[0] ?? null;
}

/** Fetch a single scalar value (first column of first row, or null). */
function db_value(string $sql, array $params = []) {
    $row = db_one($sql, $params);
    return $row === null ? null : array_values($row)[0];
}

/** Insert and return the new auto-increment id. */
function db_insert(string $sql, array $params = []): int {
    $stmt = db_stmt($sql, $params);
    $id   = db()->insert_id;
    $stmt->close();
    return $id;
}
