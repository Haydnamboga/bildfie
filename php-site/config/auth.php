<?php
/**
 * Member authentication — DB-backed (users table).
 * Members live in `users`; the session snapshot is under $_SESSION['user'].
 */
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/mailer.php';

function is_logged_in(): bool { return !empty($_SESSION['user']); }
function current_user(): array { return $_SESSION['user'] ?? []; }

/** The member's avatar URL — their uploaded photo, else a branded initials avatar (never a stranger's photo). */
function user_avatar(?array $u = null, int $size = 96): string {
    $u = $u ?? current_user();
    if (!empty($u['photo_url'])) return (string) $u['photo_url'];
    $name = trim((string) ($u['name'] ?? '')) ?: 'bildfie';
    return 'https://ui-avatars.com/api/?name=' . urlencode($name)
         . '&background=1e3a5f&color=ffffff&bold=true&size=' . max(32, $size);
}
/** The member's cover image URL, or '' when none is set (the caller then shows a gradient). */
function user_cover(?array $u = null): string {
    $u = $u ?? current_user();
    return !empty($u['cover_url']) ? (string) $u['cover_url'] : '';
}

function user_session(array $u): void {
    // photo/cover live on the member's professional profile (user_professions)
    $media = db_one("SELECT photo_url, cover_url FROM user_professions WHERE user_id = ?", [(int) $u['id']]) ?: [];
    $_SESSION['user'] = [
        'id'          => (int) $u['id'],
        'public_id'   => $u['public_id'],
        'name'        => $u['name'],
        'email'       => $u['email'],
        'phone'       => $u['phone'] ?? null,
        'role'        => 'Member',
        'region_id'   => isset($u['region_id']) ? (int) $u['region_id'] : null,
        'status'      => $u['status'] ?? 'active',
        'created_at'  => $u['created_at'] ?? null,
        'is_provider' => (int) ($u['is_provider'] ?? 0),
        'is_client'   => (int) ($u['is_client'] ?? 1),
        'photo_url'   => $media['photo_url'] ?? null,
        'cover_url'   => $media['cover_url'] ?? null,
    ];
}

/** Re-read the member from the DB and refresh the session snapshot. */
function user_reload(int $id): void {
    $u = db_one("SELECT * FROM users WHERE id = ?", [$id]);
    if ($u) user_session($u);
}

function user_find(string $email): ?array {
    return db_one("SELECT * FROM users WHERE email = ? LIMIT 1", [trim(strtolower($email))]);
}

function user_attempt(string $email, string $password): bool {
    $u = user_find($email);
    if (!$u || $u['status'] !== 'active' || empty($u['password_hash'])) return false;
    if (!password_verify($password, $u['password_hash'])) return false;
    user_session($u);
    return true;
}

/** Full login with clear outcomes (used by the login API). */
function user_login(string $email, string $password): array {
    $u = user_find($email);
    if (!$u || empty($u['password_hash']) || !password_verify($password, $u['password_hash']))
        return ['ok' => false, 'error' => 'Invalid email or password.'];
    if (($u['status'] ?? '') !== 'active')
        return ['ok' => false, 'error' => 'This account is not active. Please contact support.'];
    if (empty($u['email_verified_at']))
        return ['ok' => false, 'unverified' => true, 'email' => $u['email'], 'error' => 'Please verify your email before signing in — check your inbox for the link.'];
    user_session($u);
    return ['ok' => true, 'id' => (int) $u['id']];
}

// ── Email tokens (verification + password reset) ──
function auth_token_create(int $userId, string $type, int $ttlMinutes): string {
    $token = bin2hex(random_bytes(32));                                                     // 64 hex chars
    db_stmt("DELETE FROM auth_tokens WHERE user_id=? AND type=? AND used_at IS NULL", [$userId, $type]); // one live token per purpose
    db_insert("INSERT INTO auth_tokens (user_id,token,type,expires_at) VALUES (?,?,?,?)",
        [$userId, $token, $type, date('Y-m-d H:i:s', time() + $ttlMinutes * 60)]);
    return $token;
}
function auth_token_user(string $token, string $type): ?array {
    if ($token === '') return null;
    $t = db_one("SELECT * FROM auth_tokens WHERE token=? AND type=? LIMIT 1", [$token, $type]);
    if (!$t || $t['used_at'] !== null || strtotime($t['expires_at']) < time()) return null;
    return db_one("SELECT * FROM users WHERE id=?", [(int) $t['user_id']]);
}
function auth_token_consume(string $token, string $type): ?array {
    $u = auth_token_user($token, $type);
    if ($u) db_stmt("UPDATE auth_tokens SET used_at=NOW() WHERE token=?", [$token]);
    return $u;
}

// ── Verification + reset emails ──
function send_verification_email(array $u): void {
    $token = auth_token_create((int) $u['id'], 'verify', 60 * 48);                          // 48h
    $url   = APP_URL . '/pages/auth/verify-email.php?token=' . $token;
    $first = htmlspecialchars(explode(' ', trim($u['name']))[0] ?: 'there');
    $body  = "Welcome to bildfie, $first! Please confirm this is your email address to activate your account — you'll be able to sign in right after.";
    send_mail($u['email'], 'Verify your email · bildfie', mail_layout('Confirm your email address', $body, 'Verify my email', $url));
}
function resend_verification(string $email): void {
    $u = user_find($email);
    if ($u && empty($u['email_verified_at']) && ($u['status'] ?? '') === 'active') send_verification_email($u);
}
function email_verify(string $token): array {
    $u = auth_token_consume($token, 'verify');
    if (!$u) return ['ok' => false, 'error' => 'This verification link is invalid or has already been used. Request a fresh one below.'];
    db_stmt("UPDATE users SET email_verified_at=NOW(), status='active' WHERE id=?", [(int) $u['id']]);
    return ['ok' => true, 'user' => $u];
}
function password_reset_request(string $email): void {
    $u = user_find($email);
    if ($u && ($u['status'] ?? '') !== 'deleted') {
        $token = auth_token_create((int) $u['id'], 'reset', 60);                            // 1h
        $url   = APP_URL . '/pages/auth/reset-password.php?token=' . $token;
        $body  = "We received a request to reset your bildfie password. Click below to choose a new one — this link expires in 1 hour. Didn't request it? You can safely ignore this email.";
        send_mail($u['email'], 'Reset your password · bildfie', mail_layout('Reset your password', $body, 'Choose a new password', $url));
    }
    // always silent → no account-enumeration
}
function password_reset(string $token, string $newPassword): array {
    if (strlen($newPassword) < 6) return ['ok' => false, 'error' => 'New password must be at least 6 characters.'];
    $u = auth_token_consume($token, 'reset');
    if (!$u) return ['ok' => false, 'error' => 'This reset link is invalid or has expired. Please request a new one.'];
    db_stmt("UPDATE users SET password_hash=?, status='active', email_verified_at=COALESCE(email_verified_at, NOW()) WHERE id=?",
        [password_hash($newPassword, PASSWORD_DEFAULT), (int) $u['id']]);
    return ['ok' => true, 'email' => $u['email']];
}

/** Register a new member. Returns ['ok'=>bool,'error'=>?string,'id'=>?int]. */
function user_register(string $name, string $email, string $password, ?string $phone = null): array {
    $name  = trim($name);
    $email = trim(strtolower($email));
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
        return ['ok' => false, 'error' => 'Enter a name, a valid email and a password of at least 6 characters.'];
    }
    if (user_find($email)) {
        return ['ok' => false, 'error' => 'An account with that email already exists — try signing in.'];
    }
    $d = random_bytes(16); $d[6] = chr(ord($d[6]) & 0x0f | 0x40); $d[8] = chr(ord($d[8]) & 0x3f | 0x80);
    $uuid = vsprintf('%s%s-%s-%s-%s%s%s', str_split(bin2hex($d), 4));
    $id = db_insert(
        "INSERT INTO users (public_id,name,email,phone,password_hash,is_provider,is_client,status,email_verified_at)
         VALUES (?,?,?,?,?,0,1,'active',NULL)",
        [$uuid, $name, $email, ($phone ?: null), password_hash($password, PASSWORD_DEFAULT)]
    );
    // require email verification before the first sign-in (no auto-login)
    send_verification_email(db_one("SELECT * FROM users WHERE id = ?", [$id]));
    return ['ok' => true, 'id' => $id, 'verify' => true, 'email' => $email];
}

/** Update a member's own profile (name, email, phone, region). Returns ['ok'=>bool,'error'=>?string]. */
function user_update_profile(int $id, string $name, string $email, ?string $phone, $regionId): array {
    $name  = trim($name);
    $email = trim(strtolower($email));
    if ($name === '')                                  return ['ok' => false, 'error' => 'Your name cannot be empty.'];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))    return ['ok' => false, 'error' => 'Enter a valid email address.'];
    if (db_value("SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1", [$email, $id])) {
        return ['ok' => false, 'error' => 'That email is already used by another account.'];
    }
    $regionId = ($regionId === '' || $regionId === null) ? null : (int) $regionId;
    db_stmt("UPDATE users SET name=?, email=?, phone=?, region_id=? WHERE id=?",
            [$name, $email, ($phone ?: null), $regionId, $id]);
    user_reload($id);
    return ['ok' => true];
}

/** Toggle whether the member also offers services (users.is_provider). */
function user_set_provider(int $id, int $on): void {
    db_stmt("UPDATE users SET is_provider=? WHERE id=?", [$on ? 1 : 0, $id]);
    user_reload($id);
}

/** Change a member's password — verifies the current one first. */
function user_change_password(int $id, string $current, string $new): array {
    if (strlen($new) < 6) return ['ok' => false, 'error' => 'New password must be at least 6 characters.'];
    $hash = db_value("SELECT password_hash FROM users WHERE id = ?", [$id]);
    if (!$hash || !password_verify($current, $hash)) {
        return ['ok' => false, 'error' => 'Your current password is incorrect.'];
    }
    db_stmt("UPDATE users SET password_hash=? WHERE id=?", [password_hash($new, PASSWORD_DEFAULT), $id]);
    return ['ok' => true];
}

/** Set the member's own account status (e.g. self-deactivate). */
function user_set_status(int $id, string $status): void {
    if (in_array($status, ['active','pending','suspended'], true)) {
        db_stmt("UPDATE users SET status=? WHERE id=?", [$status, $id]);
    }
}

// ─────────────────────────────────────────────────────────────
// Member professional profile (user_professions / skills / areas)
// ─────────────────────────────────────────────────────────────
function user_profession(int $id): array {
    return db_one("SELECT * FROM user_professions WHERE user_id = ?", [$id]) ?? [];
}
function user_save_profession(int $id, array $d): void {
    db_stmt(
        "INSERT INTO user_professions (user_id,trade,title,years_experience,day_rate,rate_unit,currency_code,availability,company_name,nca_number,nca_category)
         VALUES (?,?,?,?,?,?,?,?,?,?,?)
         ON DUPLICATE KEY UPDATE trade=VALUES(trade),title=VALUES(title),years_experience=VALUES(years_experience),
           day_rate=VALUES(day_rate),rate_unit=VALUES(rate_unit),currency_code=VALUES(currency_code),
           availability=VALUES(availability),company_name=VALUES(company_name),nca_number=VALUES(nca_number),nca_category=VALUES(nca_category)",
        [$id, $d['trade'], $d['title'], $d['years_experience'], $d['day_rate'], $d['rate_unit'] ?? 'day',
         $d['currency_code'] ?? 'KES', $d['availability'] ?? 'available', $d['company_name'], $d['nca_number'], $d['nca_category']]
    );
}
function user_skills(int $id): array {
    return array_column(db_all("SELECT skill FROM user_skills WHERE user_id=? ORDER BY sort_order, id", [$id]), 'skill');
}
function user_set_skills(int $id, array $skills): void {
    db_stmt("DELETE FROM user_skills WHERE user_id=?", [$id]);
    $i = 0;
    foreach ($skills as $s) { $s = trim($s); if ($s === '') continue; db_stmt("INSERT INTO user_skills (user_id,skill,sort_order) VALUES (?,?,?)", [$id, $s, $i++]); }
}
function user_areas(int $id): array {
    return array_column(db_all("SELECT area FROM user_service_areas WHERE user_id=? ORDER BY sort_order, id", [$id]), 'area');
}
function user_set_areas(int $id, array $areas): void {
    db_stmt("DELETE FROM user_service_areas WHERE user_id=?", [$id]);
    $i = 0;
    foreach ($areas as $a) { $a = trim($a); if ($a === '') continue; db_stmt("INSERT INTO user_service_areas (user_id,area,sort_order) VALUES (?,?,?)", [$id, $a, $i++]); }
}

// ─────────────────────────────────────────────────────────────
// Member preferences / notifications / security toggles
// ─────────────────────────────────────────────────────────────
function user_prefs(int $id): array {
    db_stmt("INSERT IGNORE INTO user_preferences (user_id) VALUES (?)", [$id]);
    return db_one("SELECT * FROM user_preferences WHERE user_id = ?", [$id]) ?? [];
}
function user_save_preferences(int $id, string $currency, string $language, string $timezone): void {
    db_stmt("INSERT IGNORE INTO user_preferences (user_id) VALUES (?)", [$id]);
    db_stmt("UPDATE user_preferences SET currency_code=?, language=?, timezone=? WHERE user_id=?", [$currency, $language, $timezone, $id]);
}
function user_save_notifications(int $id, array $f): void {
    db_stmt("INSERT IGNORE INTO user_preferences (user_id) VALUES (?)", [$id]);
    db_stmt("UPDATE user_preferences SET notif_bids=?, notif_messages=?, notif_payments=?, notif_milestones=?, notif_weekly=?, notif_promos=? WHERE user_id=?",
        [$f['notif_bids'], $f['notif_messages'], $f['notif_payments'], $f['notif_milestones'], $f['notif_weekly'], $f['notif_promos'], $id]);
}
function user_save_security(int $id, int $twoFactor, int $loginAlerts): void {
    db_stmt("INSERT IGNORE INTO user_preferences (user_id) VALUES (?)", [$id]);
    db_stmt("UPDATE user_preferences SET two_factor=?, login_alerts=? WHERE user_id=?", [$twoFactor, $loginAlerts, $id]);
}

/** Store the member's public-listing media (only overwrites the fields provided). */
/** Save an uploaded image (jpg/png/webp/gif) to /uploads/members and return its web path, or null. */
function member_upload_image(string $field): ?string {
    if (empty($_FILES[$field]['name']) || ($_FILES[$field]['error'] ?? 1) !== UPLOAD_ERR_OK) return null;
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp','gif'], true)) return null;
    $dir = __DIR__ . '/../uploads/members';
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    $fname = $field . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], "$dir/$fname")) return null;
    return '/uploads/members/' . $fname;
}

function user_set_media(int $id, ?string $photo, ?string $cover): void {
    db_stmt("INSERT IGNORE INTO user_professions (user_id) VALUES (?)", [$id]);
    if ($photo !== null) db_stmt("UPDATE user_professions SET photo_url=? WHERE user_id=?", [$photo, $id]);
    if ($cover !== null) db_stmt("UPDATE user_professions SET cover_url=? WHERE user_id=?", [$cover, $id]);
    user_reload($id); // refresh the session snapshot so the new photo shows everywhere immediately
}

// ─────────────────────────────────────────────────────────────
// Member → public Providers listing
// ─────────────────────────────────────────────────────────────

/** Is the member's professional profile complete enough to be listed publicly? */
function member_provider_ready(int $id): bool {
    // Every active member can offer services — they become a public listing once
    // their professional profile is complete (no separate "are you a provider?" switch).
    $u = db_one("SELECT status FROM users WHERE id=?", [$id]);
    if (!$u || $u['status'] !== 'active') return false;
    $p = db_one("SELECT trade,title,day_rate,photo_url FROM user_professions WHERE user_id=?", [$id]);
    if (!$p) return false;
    if (empty($p['trade']) || empty($p['title']) || $p['day_rate'] === null || $p['day_rate'] === '' || empty($p['photo_url'])) return false;
    return (int) db_value("SELECT COUNT(*) FROM user_skills WHERE user_id=?", [$id]) >= 1;
}

/** Create / refresh the member's row in `providers` from their self-service profile.
 *  Auto-publishes (status=active) when ready; hides (pending) otherwise.
 *  Respects an admin suspension (won't auto-reactivate a suspended listing). */
function member_provider_sync(int $id): void {
    $u = db_one("SELECT * FROM users WHERE id=?", [$id]);
    if (!$u) return;
    $prof  = db_one("SELECT * FROM user_professions WHERE user_id=?", [$id]) ?? [];
    $cur   = db_one("SELECT id,status FROM providers WHERE user_id=?", [$id]);
    $ready = member_provider_ready($id);
    // keep users.is_provider as a derived flag (no manual toggle)
    db_stmt("UPDATE users SET is_provider=? WHERE id=?", [$ready ? 1 : 0, $id]);

    $regionName = $u['region_id'] ? (db_value("SELECT name FROM regions WHERE id=?", [(int)$u['region_id']]) ?: '') : '';
    $areas = array_column(db_all("SELECT area FROM user_service_areas WHERE user_id=? ORDER BY sort_order", [$id]), 'area');
    $location = $areas ? trim($areas[0] . ($regionName ? ', ' . $regionName : '')) : ($regionName ?: null);
    $rate = (isset($prof['day_rate']) && $prof['day_rate'] !== null && $prof['day_rate'] !== '')
          ? (($prof['currency_code'] ?? 'KES') . ' ' . number_format((float)$prof['day_rate']) . '/' . ($prof['rate_unit'] ?? 'day'))
          : null;
    $vId   = db_value("SELECT id FROM verticals WHERE slug='construction-built-environment'") ?: db_value("SELECT id FROM verticals ORDER BY sort_order LIMIT 1");
    $avail = (($prof['availability'] ?? 'available') === 'available') ? 1 : 0;
    $status = ($cur && $cur['status'] === 'suspended') ? 'suspended' : ($ready ? 'active' : 'pending');

    if (!$cur) {
        $d = random_bytes(16); $d[6] = chr(ord($d[6]) & 0x0f | 0x40); $d[8] = chr(ord($d[8]) & 0x3f | 0x80);
        $uuid = vsprintf('%s%s-%s-%s-%s%s%s', str_split(bin2hex($d), 4));
        $pid = db_insert(
            "INSERT INTO providers (public_id,user_id,name,headline,vertical_id,bio,location,region_id,photo_url,cover_url,day_rate,is_available,is_verified,is_featured,status)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,0,0,?)",
            [$uuid, $id, $u['name'], $prof['title'] ?? null, $vId, $prof['bio'] ?? null, $location, $u['region_id'] ?: null,
             $prof['photo_url'] ?? null, $prof['cover_url'] ?? null, $rate, $avail, $status]
        );
    } else {
        $pid = (int) $cur['id'];
        db_stmt(
            "UPDATE providers SET name=?,headline=?,vertical_id=?,bio=?,location=?,region_id=?,photo_url=?,cover_url=?,day_rate=?,is_available=?,status=? WHERE id=?",
            [$u['name'], $prof['title'] ?? null, $vId, $prof['bio'] ?? null, $location, $u['region_id'] ?: null,
             $prof['photo_url'] ?? null, $prof['cover_url'] ?? null, $rate, $avail, $status, $pid]
        );
    }

    // mirror specialisations → provider_skills
    db_stmt("DELETE FROM provider_skills WHERE provider_id=?", [$pid]);
    $i = 0;
    foreach (db_all("SELECT skill FROM user_skills WHERE user_id=? ORDER BY sort_order", [$id]) as $r) {
        db_stmt("INSERT INTO provider_skills (provider_id,skill,sort_order) VALUES (?,?,?)", [$pid, $r['skill'], $i++]);
    }
}

/** Return the member's provider-listing id, creating the row (status 'pending' if the
 *  profile isn't complete enough to be publicly listed) when one doesn't exist yet.
 *  Guarantees ANY member can receive reviews / invites / quotes. */
function provider_ensure(int $uid): int {
    $id = (int) db_value("SELECT id FROM providers WHERE user_id=? LIMIT 1", [$uid]);
    if ($id) return $id;
    member_provider_sync($uid);
    return (int) db_value("SELECT id FROM providers WHERE user_id=? LIMIT 1", [$uid]);
}

// Provider trust badges — single source of truth (provider_trust_defs() / provider_badges()).
require_once __DIR__ . '/provider_badges.php';

// ─────────────────────────────────────────────────────────────
// Member wallet
// ─────────────────────────────────────────────────────────────
function user_wallet(int $id): array {
    db_stmt("INSERT IGNORE INTO wallets (user_id) VALUES (?)", [$id]);
    return db_one("SELECT * FROM wallets WHERE user_id=?", [$id]) ?? ['user_id'=>$id,'balance'=>0,'currency_code'=>'KES'];
}
function wallet_history(int $id, int $limit = 100): array {
    return db_all("SELECT * FROM wallet_transactions WHERE user_id=? ORDER BY id DESC LIMIT " . (int)$limit, [$id]);
}
/** Totals by direction (credits vs debits) for the summary cards. */
function wallet_totals(int $id): array {
    $row = db_one(
        "SELECT
           COALESCE(SUM(CASE WHEN type IN ('deposit','refund','payout') AND status='completed' THEN amount END),0) AS credits,
           COALESCE(SUM(CASE WHEN type IN ('withdrawal','payment','fee') AND status='completed' THEN amount END),0) AS debits,
           COALESCE(SUM(CASE WHEN status='pending' THEN amount END),0) AS pending
         FROM wallet_transactions WHERE user_id=?", [$id]);
    return $row ?: ['credits'=>0,'debits'=>0,'pending'=>0];
}
/** Post a wallet movement. Credits: deposit/refund/payout. Debits: withdrawal/payment/fee. */
function wallet_post(int $id, string $type, float $amount, string $desc = '', string $method = '', string $status = 'completed'): array {
    if ($amount <= 0) return ['ok'=>false,'error'=>'Enter an amount greater than zero.'];
    $w = user_wallet($id);
    $bal = (float) $w['balance'];
    $credit = in_array($type, ['deposit','refund','payout'], true);
    if (!$credit && $amount > $bal) return ['ok'=>false,'error'=>'Insufficient wallet balance.'];
    $newBal = $credit ? $bal + $amount : $bal - $amount;
    if ($status === 'completed') db_stmt("UPDATE wallets SET balance=? WHERE user_id=?", [$newBal, $id]);
    $ref = strtoupper(substr($type, 0, 3)) . '-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
    db_insert("INSERT INTO wallet_transactions (user_id,type,amount,balance_after,status,method,reference,description) VALUES (?,?,?,?,?,?,?,?)",
        [$id, $type, $amount, ($status==='completed'?$newBal:$bal), $status, $method ?: null, $ref, $desc ?: null]);
    return ['ok'=>true, 'balance'=>$newBal, 'reference'=>$ref];
}

/** Gate member-only pages. */
function require_login(): void {
    if (!is_logged_in()) {
        $back = urlencode($_SERVER['REQUEST_URI'] ?? '/pages/dashboard/index.php');
        header('Location: /pages/auth/login.php?redirect=' . $back);
        exit;
    }
}

function logout(): void { unset($_SESSION['user']); }
