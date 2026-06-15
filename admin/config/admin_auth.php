<?php
/**
 * Back-office (staff) authentication — DB-backed, fully separate from members.
 * Staff live in the `staff` table; the session holds a snapshot under
 * $_SESSION['admin'] on the 'bildfie_admin' cookie.
 */

function admin_logged_in(): bool {
    return !empty($_SESSION['admin']);
}

function current_admin(): array {
    return $_SESSION['admin'] ?? [];
}

/** Build the session snapshot from a joined staff row. */
function admin_set_session(array $s): void {
    $_SESSION['admin'] = [
        'id'           => (int) $s['id'],
        'public_id'    => $s['public_id'],
        'name'         => $s['name'],
        'email'        => $s['email'],
        'role'         => $s['role_name'] ?? 'Staff',
        'level'        => $s['level'] ?? 'L4',
        'department'   => $s['department_name'] ?? null,
        'photo'        => $s['photo_url'] ?: 'https://randomuser.me/api/portraits/men/45.jpg',
        'cover'        => $s['cover_url'] ?? null,
        'is_protected' => (int) ($s['is_protected'] ?? 0),
    ];
}

/** Fetch a staff member (with role + department) by email. */
function admin_find(string $email): ?array {
    return db_one(
        "SELECT s.*, r.name AS role_name, r.level, d.name AS department_name
         FROM staff s
         LEFT JOIN roles r       ON r.id = s.role_id
         LEFT JOIN departments d ON d.id = s.department_id
         WHERE s.email = ? LIMIT 1",
        [$email]
    );
}

/** Verify credentials against the database. Returns true on success. */
function admin_attempt(string $email, string $password): bool {
    $s = admin_find($email);
    if (!$s || $s['status'] !== 'active') return false;
    if (!password_verify($password, $s['password_hash'])) return false;

    admin_set_session($s);
    db_stmt("UPDATE staff SET last_login_at = NOW() WHERE id = ?", [(int) $s['id']]);
    admin_audit('admin.login', 'staff', $s['id']);
    return true;
}

function admin_logout(): void {
    if (admin_logged_in()) admin_audit('admin.logout', 'staff', current_admin()['id'] ?? null);
    unset($_SESSION['admin']);
}

/** Gate every back-office page. No staff session → bounce to login. */
function require_admin(): void {
    if (!admin_logged_in()) {
        header('Location: /admin/login.php');
        exit;
    }
}

/** Is the current account protected (owner) — cannot be deleted / do destructive ops. */
function admin_is_protected(): bool {
    return (int) (current_admin()['is_protected'] ?? 0) === 1;
}

/** Write an immutable audit-log entry for the current actor. */
function admin_audit(string $action, ?string $entityType = null, $entityId = null, array $meta = []): void {
    try {
        $a = current_admin();
        db_insert(
            "INSERT INTO audit_logs (actor_type,actor_id,actor_name,action,entity_type,entity_id,meta,ip)
             VALUES ('staff',?,?,?,?,?,?,?)",
            [
                $a['id']   ?? null,
                $a['name'] ?? null,
                $action,
                $entityType,
                $entityId !== null ? (string) $entityId : null,
                $meta ? json_encode($meta) : null,
                $_SERVER['REMOTE_ADDR'] ?? null,
            ]
        );
    } catch (Throwable $e) { /* never let auditing break a request */ }
}
