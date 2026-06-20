<?php
/**
 * Companies / organizations + employment affirmation helpers (mysqli).
 */
require_once __DIR__ . '/db.php';

function company_uuid(): string {
    $d = random_bytes(16); $d[6] = chr(ord($d[6]) & 0x0f | 0x40); $d[8] = chr(ord($d[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s%s%s', str_split(bin2hex($d), 4));
}
function company_logo(array $c): string {
    if (!empty($c['logo_url'])) return $c['logo_url'];
    return 'https://ui-avatars.com/api/?name=' . urlencode($c['name']) . '&background=1e3a5f&color=fff&size=120&bold=true&format=png';
}

function company_find($key): ?array {
    $sql = "SELECT c.*, r.name AS region_name FROM companies c LEFT JOIN regions r ON r.id = c.region_id WHERE ";
    return is_numeric($key)
        ? db_one($sql . "c.id = ? LIMIT 1", [(int)$key])
        : db_one($sql . "c.slug = ? LIMIT 1", [$key]);
}

function company_all(string $q = '', string $industry = ''): array {
    $w = ["c.status <> 'suspended'"]; $p = [];
    if ($q !== '')        { $w[] = "(c.name LIKE ? OR c.industry LIKE ? OR c.hq_location LIKE ?)"; $l = "%$q%"; array_push($p, $l, $l, $l); }
    if ($industry !== '') { $w[] = "c.industry = ?"; $p[] = $industry; }
    $ws = 'WHERE ' . implode(' AND ', $w);
    return db_all(
        "SELECT c.*, r.name AS region_name,
                (SELECT COUNT(*) FROM company_members m WHERE m.company_id=c.id AND m.status='affirmed' AND m.is_current=1) AS emp_count
         FROM companies c LEFT JOIN regions r ON r.id=c.region_id $ws
         ORDER BY c.is_verified DESC, c.name", $p);
}

function company_specialties(int $cid): array {
    return array_column(db_all("SELECT specialty FROM company_specialties WHERE company_id=? ORDER BY sort_order, id", [$cid]), 'specialty');
}

/** Affirmed, public employees of a company (current first). */
function company_people(int $cid): array {
    return db_all(
        "SELECT m.*, u.name, u.public_id AS user_public, up.title AS prof_title, up.photo_url, up.trade, pr.id AS provider_id
         FROM company_members m
         JOIN users u ON u.id = m.user_id
         LEFT JOIN user_professions up ON up.user_id = u.id
         LEFT JOIN providers pr ON pr.user_id = u.id AND pr.status='active'
         WHERE m.company_id = ? AND m.status='affirmed' AND m.is_public=1
         ORDER BY m.is_current DESC, m.id", [$cid]);
}
function company_employee_count(int $cid): int {
    return (int) db_value("SELECT COUNT(*) FROM company_members WHERE company_id=? AND status='affirmed' AND is_current=1", [$cid]);
}

/** A member's employment (for their public profile). */
function user_employment(int $uid, bool $affirmedOnly = true): array {
    $cond = $affirmedOnly ? "AND m.status='affirmed'" : '';
    return db_all(
        "SELECT m.*, c.name AS company_name, c.slug, c.logo_url, c.industry, c.is_verified AS company_verified
         FROM company_members m JOIN companies c ON c.id = m.company_id
         WHERE m.user_id = ? $cond ORDER BY m.is_current DESC, m.id DESC", [$uid]);
}

/** A member claims a position at a company → pending until the org affirms. */
function company_claim(int $cid, int $uid, string $position, string $empType = 'Full-time'): array {
    $position = trim($position);
    if ($position === '') return ['ok' => false, 'error' => 'Enter your position / title.'];
    if (db_value("SELECT id FROM company_members WHERE company_id=? AND user_id=? AND position=? LIMIT 1", [$cid, $uid, $position])) {
        return ['ok' => false, 'error' => 'You already have a claim for this role here.'];
    }
    db_insert("INSERT INTO company_members (company_id,user_id,position,employment_type,is_current,status) VALUES (?,?,?,?,1,'pending')",
        [$cid, $uid, $position, $empType]);
    return ['ok' => true];
}

function company_member_affirm(int $mid, ?int $by = null): void {
    db_stmt("UPDATE company_members SET status='affirmed', affirmed_at=NOW(), affirmed_by=? WHERE id=?", [$by, $mid]);
}
function company_member_reject(int $mid): void {
    db_stmt("UPDATE company_members SET status='rejected' WHERE id=?", [$mid]);
}
function company_pending_claims(): array {
    return db_all(
        "SELECT m.*, c.name AS company_name, u.name AS user_name, u.email AS user_email
         FROM company_members m JOIN companies c ON c.id=m.company_id JOIN users u ON u.id=m.user_id
         WHERE m.status='pending' ORDER BY m.id DESC");
}

/* ── Vacancies + direct applications ─────────────────────────── */

/** Open vacancies a company is advertising (newest first). */
function company_vacancies(int $cid, bool $openOnly = true): array {
    $w = $openOnly ? "AND status='open'" : '';
    return db_all("SELECT * FROM company_vacancies WHERE company_id=? $w ORDER BY created_at DESC, id DESC", [$cid]);
}

/** Vacancy ids the given user has already applied to. */
function user_applied_vacancy_ids(int $uid): array {
    return array_map('intval', array_column(db_all("SELECT vacancy_id FROM vacancy_applications WHERE user_id=?", [$uid]), 'vacancy_id'));
}

/** A member applies directly to a vacancy. One application per user per vacancy. */
function vacancy_apply(int $vid, int $uid, string $name, ?string $email, string $msg = ''): array {
    $v = db_one("SELECT id, status FROM company_vacancies WHERE id=? LIMIT 1", [$vid]);
    if (!$v || $v['status'] !== 'open') return ['ok' => false, 'error' => 'This position is no longer open.'];
    if (db_value("SELECT id FROM vacancy_applications WHERE vacancy_id=? AND user_id=? LIMIT 1", [$vid, $uid])) {
        return ['ok' => false, 'error' => 'You have already applied for this position.'];
    }
    db_insert("INSERT INTO vacancy_applications (vacancy_id,user_id,applicant_name,applicant_email,message) VALUES (?,?,?,?,?)",
        [$vid, $uid, $name, $email, trim($msg) ?: null]);
    db_stmt("UPDATE company_vacancies SET applications_count = applications_count + 1 WHERE id=?", [$vid]);
    return ['ok' => true];
}
