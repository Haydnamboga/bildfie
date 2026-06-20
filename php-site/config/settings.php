<?php
/**
 * Tiny key/value settings store (the `settings` table). Used for admin-editable
 * configuration like SMTP credentials — so non-technical owners never touch PHP files.
 */
require_once __DIR__ . '/db.php';

/** Get one setting (string) or $default when absent. */
function setting_get(string $key, $default = null) {
    try {
        $v = db_value("SELECT `value` FROM settings WHERE `key`=? LIMIT 1", [$key]);
    } catch (Throwable $e) { return $default; }
    return ($v === null) ? $default : $v;
}

/** Create or update one setting. */
function setting_set(string $key, ?string $value, string $group = 'general'): void {
    db_stmt(
        "INSERT INTO settings (`key`,`value`,`group`) VALUES (?,?,?)
         ON DUPLICATE KEY UPDATE `value`=VALUES(`value`), `group`=VALUES(`group`)",
        [$key, $value, $group]
    );
}

/** All settings in a group as [key => value]. One query. */
function settings_group(string $group): array {
    $out = [];
    try {
        foreach (db_all("SELECT `key`,`value` FROM settings WHERE `group`=?", [$group]) as $r) {
            $out[$r['key']] = $r['value'];
        }
    } catch (Throwable $e) {}
    return $out;
}
