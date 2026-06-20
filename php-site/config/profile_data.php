<?php
/**
 * Editable, client-facing profile sections (per member, keyed by user_id).
 * Backs Account → Profile: packages, portfolio, services, languages,
 * certifications and business info. Reviews are read from provider_reviews
 * (client-created via the public listing) and aggregated here.
 *
 * Every save_* helper is "replace-all": it clears the member's rows then
 * re-inserts from the posted list, so editing is a single atomic submit.
 */
require_once __DIR__ . '/db.php';

/* ── Service packages (Fiverr-style tiers) ─────────────────── */
function user_packages(int $uid): array {
    return db_all("SELECT * FROM user_packages WHERE user_id=? ORDER BY sort_order, id", [$uid]);
}
function user_save_packages(int $uid, array $rows): void {
    db_stmt("DELETE FROM user_packages WHERE user_id=?", [$uid]);
    $i = 0;
    foreach ($rows as $r) {
        $title = trim($r['title'] ?? '');
        if ($title === '') continue;
        db_stmt(
          "INSERT INTO user_packages (user_id,tier,title,price,price_unit,description,delivery,revisions,features,is_featured,sort_order)
           VALUES (?,?,?,?,?,?,?,?,?,?,?)",
          [$uid,
           (trim($r['tier'] ?? '') ?: null),
           $title,
           (trim($r['price'] ?? '') ?: null),
           (trim($r['price_unit'] ?? '') ?: null),
           (trim($r['description'] ?? '') ?: null),
           (trim($r['delivery'] ?? '') ?: null),
           (trim($r['revisions'] ?? '') ?: null),
           (trim($r['features'] ?? '') ?: null),
           (!empty($r['is_featured']) ? 1 : 0),
           $i++]);
    }
}
/** Package features text (one per line) → array of non-empty lines. */
function pkg_features(?string $text): array {
    if (!$text) return [];
    return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $text)), fn($l) => $l !== ''));
}

/* ── Portfolio / past projects ─────────────────────────────── */
function user_portfolio(int $uid): array {
    return db_all("SELECT * FROM user_portfolio WHERE user_id=? ORDER BY sort_order, id", [$uid]);
}
function user_save_portfolio(int $uid, array $rows): void {
    db_stmt("DELETE FROM user_portfolio WHERE user_id=?", [$uid]);
    $i = 0;
    foreach ($rows as $r) {
        $title = trim($r['title'] ?? '');
        if ($title === '') continue;
        db_stmt(
          "INSERT INTO user_portfolio (user_id,title,category,year,image_url,sort_order) VALUES (?,?,?,?,?,?)",
          [$uid, $title, (trim($r['category'] ?? '') ?: null), (trim($r['year'] ?? '') ?: null),
           (trim($r['image_url'] ?? '') ?: null), $i++]);
    }
}
/** Upload one image from an indexed multi-file field ($_FILES[$field][...][$i]).
 *  Returns a web path under /uploads/$subdir, or null when nothing valid was sent. */
function member_upload_file_at(string $field, int $i, string $subdir = 'members'): ?string {
    if (!isset($_FILES[$field]['name'][$i])) return null;
    $name = (string) $_FILES[$field]['name'][$i];
    $err  = (int) ($_FILES[$field]['error'][$i] ?? UPLOAD_ERR_NO_FILE);
    if ($name === '' || $err !== UPLOAD_ERR_OK) return null;
    if ((int) ($_FILES[$field]['size'][$i] ?? 0) > 10 * 1024 * 1024) return null;   // 10 MB cap
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp','gif'], true)) return null;
    $dir = __DIR__ . '/../uploads/' . $subdir;
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    $fname = $field . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    if (!@move_uploaded_file($_FILES[$field]['tmp_name'][$i], "$dir/$fname")) return null;
    return '/uploads/' . $subdir . '/' . $fname;
}

/* ── Other services & rates ────────────────────────────────── */
function user_services(int $uid): array {
    return db_all("SELECT * FROM user_services WHERE user_id=? ORDER BY sort_order, id", [$uid]);
}
function user_save_services(int $uid, array $rows): void {
    db_stmt("DELETE FROM user_services WHERE user_id=?", [$uid]);
    $i = 0;
    foreach ($rows as $r) {
        $name = trim($r['name'] ?? '');
        if ($name === '') continue;
        db_stmt(
          "INSERT INTO user_services (user_id,icon,name,description,rate,rate_unit,sort_order) VALUES (?,?,?,?,?,?,?)",
          [$uid, (trim($r['icon'] ?? '') ?: null), $name, (trim($r['description'] ?? '') ?: null),
           (trim($r['rate'] ?? '') ?: null), (trim($r['rate_unit'] ?? '') ?: null), $i++]);
    }
}

/* ── Languages ─────────────────────────────────────────────── */
function user_languages(int $uid): array {
    return db_all("SELECT * FROM user_languages WHERE user_id=? ORDER BY sort_order, id", [$uid]);
}
function user_save_languages(int $uid, array $rows): void {
    db_stmt("DELETE FROM user_languages WHERE user_id=?", [$uid]);
    $i = 0;
    foreach ($rows as $r) {
        $lang = trim($r['language'] ?? '');
        if ($lang === '') continue;
        db_stmt("INSERT INTO user_languages (user_id,language,level,sort_order) VALUES (?,?,?,?)",
          [$uid, $lang, (trim($r['level'] ?? '') ?: null), $i++]);
    }
}

/* ── Certifications & licences ─────────────────────────────── */
function user_certifications(int $uid): array {
    return db_all("SELECT * FROM user_certifications WHERE user_id=? ORDER BY sort_order, id", [$uid]);
}
function user_save_certifications(int $uid, array $rows): void {
    db_stmt("DELETE FROM user_certifications WHERE user_id=?", [$uid]);
    $i = 0;
    foreach ($rows as $r) {
        $name = trim($r['name'] ?? '');
        if ($name === '') continue;
        db_stmt("INSERT INTO user_certifications (user_id,name,issuer,status,sort_order) VALUES (?,?,?,?,?)",
          [$uid, $name, (trim($r['issuer'] ?? '') ?: null), (trim($r['status'] ?? '') ?: null), $i++]);
    }
}

/* ── Business info (extra columns on user_professions) ─────── */
function user_save_business(int $uid, array $d): void {
    db_stmt("INSERT IGNORE INTO user_professions (user_id) VALUES (?)", [$uid]);
    db_stmt(
      "UPDATE user_professions
          SET years_in_business=?, team_size=?, working_hours=?, serving_area=?, website=?, public_phone=?
        WHERE user_id=?",
      [(trim($d['years_in_business'] ?? '') ?: null),
       (trim($d['team_size'] ?? '') ?: null),
       (trim($d['working_hours'] ?? '') ?: null),
       (trim($d['serving_area'] ?? '') ?: null),
       (trim($d['website'] ?? '') ?: null),
       (trim($d['public_phone'] ?? '') ?: null),
       $uid]);
}

/* ── Reviews (client-created, read-only for the owner) ─────── */
function provider_for_user(int $uid): ?array {
    return db_one("SELECT * FROM providers WHERE user_id=? LIMIT 1", [$uid]);
}
function user_reviews(int $uid, int $limit = 0): array {
    $pid = (int) db_value("SELECT id FROM providers WHERE user_id=? LIMIT 1", [$uid]);
    if (!$pid) return [];
    $lim = $limit > 0 ? ' LIMIT ' . (int) $limit : '';
    return db_all("SELECT * FROM provider_reviews WHERE provider_id=? AND status='published' ORDER BY created_at DESC, id DESC" . $lim, [$pid]);
}
function user_review_stats(int $uid): array {
    $out = ['count' => 0, 'avg' => 0.0, 'breakdown' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0]];
    $pid = (int) db_value("SELECT id FROM providers WHERE user_id=? LIMIT 1", [$uid]);
    if (!$pid) return $out;
    $tot = 0; $sum = 0;
    foreach (db_all("SELECT rating, COUNT(*) AS c FROM provider_reviews WHERE provider_id=? AND status='published' GROUP BY rating", [$pid]) as $r) {
        $st = (int) $r['rating']; $c = (int) $r['c'];
        if ($st >= 1 && $st <= 5) { $out['breakdown'][$st] = $c; $tot += $c; $sum += $st * $c; }
    }
    $out['count'] = $tot;
    $out['avg']   = $tot ? round($sum / $tot, 1) : 0.0;
    return $out;
}

/* ── Work history ──────────────────────────────────────────── */
function user_work_history(int $uid): array {
    return db_all("SELECT * FROM user_work_history WHERE user_id=? ORDER BY sort_order, id", [$uid]);
}
function user_save_work_history(int $uid, array $rows): void {
    db_stmt("DELETE FROM user_work_history WHERE user_id=?", [$uid]);
    $i = 0;
    foreach ($rows as $r) {
        $role = trim($r['role'] ?? '');
        if ($role === '') continue;
        db_stmt("INSERT INTO user_work_history (user_id,role,organization,period,description,sort_order) VALUES (?,?,?,?,?,?)",
          [$uid, $role, (trim($r['organization'] ?? '') ?: null), (trim($r['period'] ?? '') ?: null),
           (trim($r['description'] ?? '') ?: null), $i++]);
    }
}

/* ── Education & training ──────────────────────────────────── */
function user_education(int $uid): array {
    return db_all("SELECT * FROM user_education WHERE user_id=? ORDER BY sort_order, id", [$uid]);
}
function user_save_education(int $uid, array $rows): void {
    db_stmt("DELETE FROM user_education WHERE user_id=?", [$uid]);
    $i = 0;
    foreach ($rows as $r) {
        $title = trim($r['title'] ?? '');
        if ($title === '') continue;
        db_stmt("INSERT INTO user_education (user_id,title,institution,sort_order) VALUES (?,?,?,?)",
          [$uid, $title, (trim($r['institution'] ?? '') ?: null), $i++]);
    }
}

/* ── Self-reported performance highlights ──────────────────── */
function user_save_performance(int $uid, array $d): void {
    db_stmt("INSERT IGNORE INTO user_professions (user_id) VALUES (?)", [$uid]);
    db_stmt(
      "UPDATE user_professions SET jobs_completed=?, on_time_pct=?, repeat_pct=?, response_time=?, availability_note=? WHERE user_id=?",
      [(trim($d['jobs_completed'] ?? '') ?: null),
       (trim($d['on_time_pct'] ?? '') ?: null),
       (trim($d['repeat_pct'] ?? '') ?: null),
       (trim($d['response_time'] ?? '') ?: null),
       (trim($d['availability_note'] ?? '') ?: null),
       $uid]);
}

/* ── Honest profile-completion score (from what's actually filled) ── */
function profile_completion(int $uid, array $prof): array {
    $items = [
        ['Profile photo',         !empty($prof['photo_url'])],
        ['Professional headline', !empty($prof['title'])],
        ['About / bio',           !empty($prof['bio'])],
        ['A service package',     (bool) db_value("SELECT 1 FROM user_packages WHERE user_id=? LIMIT 1", [$uid])],
        ['A portfolio project',   (bool) db_value("SELECT 1 FROM user_portfolio WHERE user_id=? LIMIT 1", [$uid])],
        ['Skills added',          (bool) db_value("SELECT 1 FROM user_skills WHERE user_id=? LIMIT 1", [$uid])],
        ['A certification',       (bool) db_value("SELECT 1 FROM user_certifications WHERE user_id=? LIMIT 1", [$uid])],
        ['Business info',         !empty($prof['working_hours']) || !empty($prof['serving_area']) || !empty($prof['years_in_business'])],
    ];
    $done = count(array_filter($items, fn($i) => $i[1]));
    return ['pct' => (int) round($done * 100 / count($items)), 'items' => $items];
}
