<?php
/**
 * Simple, dependency-free migration runner (mysqli).
 *   php database/migrate.php
 * Applies every database/migrations/*.sql that hasn't run yet, in filename order,
 * and records it in schema_migrations so it never runs twice.
 */
require_once __DIR__ . '/../config/db.php';

$c = db();
$c->query("CREATE TABLE IF NOT EXISTS schema_migrations (
  version VARCHAR(191) NOT NULL PRIMARY KEY,
  applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$applied = [];
$res = $c->query("SELECT version FROM schema_migrations");
while ($row = $res->fetch_row()) $applied[] = $row[0];

$files = glob(__DIR__ . '/migrations/*.sql');
sort($files);

$ran = 0;
foreach ($files as $file) {
    $version = basename($file);
    if (in_array($version, $applied, true)) continue;

    $sql = file_get_contents($file);
    $sql = preg_replace('/^\s*--.*$/m', '', $sql);          // strip line comments
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    echo "Applying {$version} ... ";
    try {
        // No transaction: DDL auto-commits in MariaDB. Migrations are written
        // idempotently (IF NOT EXISTS / INSERT IGNORE) so re-runs are safe.
        foreach ($statements as $stmt) {
            if ($stmt !== '') $c->query($stmt);
        }
        $ins = $c->prepare("INSERT INTO schema_migrations (version) VALUES (?)");
        $ins->bind_param('s', $version);
        $ins->execute();
        $ins->close();
        echo "OK (" . count($statements) . " statements)\n";
        $ran++;
    } catch (Throwable $e) {
        echo "FAILED\n  -> " . $e->getMessage() . "\n";
        exit(1);
    }
}

echo $ran ? "\nDone — applied {$ran} migration(s).\n" : "Up to date — nothing to apply.\n";
