<?php
/**
 * Tiny mailer. In production sends real email via mail(); in dev it writes the
 * message to storage/mail.log so the verify/reset flows are testable without a
 * mail server. Swap send_mail() for SMTP later without touching callers.
 */
require_once __DIR__ . '/settings.php';

/** Populate mail config from admin Settings (DB) for anything config.local.php didn't
 *  define. config.local.php constants always win. Runs once per request. */
function mail_boot(): void {
    static $done = false; if ($done) return; $done = true;
    $ms = function_exists('settings_group') ? settings_group('mail') : [];
    foreach ([
        'MAIL_TRANSPORT' => 'mail_transport', 'SMTP_HOST' => 'smtp_host', 'SMTP_PORT' => 'smtp_port',
        'SMTP_SECURE' => 'smtp_secure', 'SMTP_USER' => 'smtp_user', 'SMTP_PASS' => 'smtp_pass',
        'MAIL_FROM' => 'mail_from', 'MAIL_FROM_NAME' => 'mail_from_name',
    ] as $const => $key) {
        if (!defined($const) && isset($ms[$key]) && $ms[$key] !== '') define($const, $ms[$key]);
    }
    if (!defined('MAIL_FROM'))      define('MAIL_FROM', 'noreply@' . (parse_url(defined('APP_URL') ? APP_URL : 'http://bildfie.com', PHP_URL_HOST) ?: 'bildfie.com'));
    if (!defined('MAIL_FROM_NAME')) define('MAIL_FROM_NAME', defined('APP_NAME') ? APP_NAME : 'bildfie');
}

/** Last mailer error (for the admin diagnostic). Pass a string to set it. */
function mail_last_error(?string $set = null): string {
    static $e = '';
    if ($set !== null) $e = $set;
    return $e;
}
/** Current mail configuration (password masked) — for the admin diagnostic. */
function mail_config_status(): array {
    mail_boot();
    $smtp = defined('MAIL_TRANSPORT') && MAIL_TRANSPORT === 'smtp' && defined('SMTP_HOST') && SMTP_HOST;
    $prod = defined('APP_ENV') && APP_ENV === 'production';
    return [
        'env'        => defined('APP_ENV') ? APP_ENV : 'local',
        'transport'  => $smtp ? 'SMTP' : ($prod ? 'PHP mail()' : 'dev log (storage/mail.log)'),
        'smtp'       => $smtp,
        'smtp_host'  => defined('SMTP_HOST')   ? SMTP_HOST   : '(not set)',
        'smtp_port'  => defined('SMTP_PORT')   ? SMTP_PORT   : '(default 587)',
        'smtp_secure'=> defined('SMTP_SECURE') ? SMTP_SECURE : '(auto)',
        'smtp_user'  => defined('SMTP_USER')   ? SMTP_USER   : (defined('MAIL_FROM') ? MAIL_FROM : '(not set)'),
        'smtp_pass'  => (defined('SMTP_PASS') && SMTP_PASS) ? 'set' : 'NOT set',
        'from'       => MAIL_FROM,
        'from_name'  => MAIL_FROM_NAME,
    ];
}

/** Send an HTML email. SMTP when configured, else mail() in prod, else logs in dev. */
function send_mail(string $to, string $subject, string $html): bool {
    mail_boot();
    // 1) SMTP when configured (reliable inbox delivery; works in dev + prod)
    if (defined('MAIL_TRANSPORT') && MAIL_TRANSPORT === 'smtp' && defined('SMTP_HOST') && SMTP_HOST) {
        if (smtp_send($to, $subject, $html)) return true;
        // SMTP failed → fall through to a backup so mail is never silently dropped
    }
    // 2) production fallback → PHP mail()
    if (defined('APP_ENV') && APP_ENV === 'production') {
        $headers = implode("\r\n", [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM . '>',
            'Reply-To: ' . MAIL_FROM,
            'X-Mailer: bildfie',
        ]);
        $ok = @mail($to, $subject, $html, $headers, '-f' . MAIL_FROM);
        mail_last_error($ok ? '' : 'PHP mail() failed — the server has no working sendmail. Configure SMTP (recommended).');
        return $ok;
    }
    // 3) dev with no SMTP → log so the link is testable locally
    $dir = __DIR__ . '/../storage';
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    @file_put_contents($dir . '/mail.log', "===== " . date('c') . " =====\nTo: $to\nSubject: $subject\n\n$html\n\n", FILE_APPEND);
    mail_last_error('');
    return true;
}

/** Minimal SMTP sender — AUTH LOGIN; SSL on 465, STARTTLS otherwise. Returns true on a 250 accept. */
function smtp_send(string $to, string $subject, string $html): bool {
    mail_boot();
    $host   = SMTP_HOST;
    $port   = defined('SMTP_PORT')   ? (int) SMTP_PORT : 587;
    $secure = defined('SMTP_SECURE') ? strtolower(SMTP_SECURE) : ($port === 465 ? 'ssl' : 'tls');
    $user   = defined('SMTP_USER')   ? SMTP_USER : MAIL_FROM;
    $pass   = defined('SMTP_PASS')   ? SMTP_PASS : '';
    $ehlo   = preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? 'bildfie.com');

    $ctx = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]]);
    $remote = ($secure === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;
    $fp = @stream_socket_client($remote, $errno, $errstr, 25, STREAM_CLIENT_CONNECT, $ctx);
    if (!$fp) { mail_last_error("Can't reach $remote ($errstr). Wrong SMTP_HOST/SMTP_PORT, or the server blocks outbound SMTP."); return false; }
    stream_set_timeout($fp, 25);

    $get  = function () use ($fp) { $d = ''; while (($l = fgets($fp, 515)) !== false) { $d .= $l; if (strlen($l) < 4 || $l[3] === ' ') break; } return $d; };
    $put  = function ($c) use ($fp) { fwrite($fp, $c . "\r\n"); };
    $code = fn($r) => (int) substr($r, 0, 3);

    if ($code($get()) !== 220) { fclose($fp); return false; }
    $put('EHLO ' . $ehlo); $get();
    if ($secure === 'tls') {
        $put('STARTTLS');
        if ($code($get()) !== 220 || !@stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) { fclose($fp); mail_last_error('STARTTLS failed on port ' . $port . ' — try port 465 with SMTP_SECURE=ssl.'); return false; }
        $put('EHLO ' . $ehlo); $get();
    }
    $put('AUTH LOGIN'); $get();
    $put(base64_encode($user)); $get();
    $put(base64_encode($pass));
    if ($code($get()) !== 235) { fclose($fp); mail_last_error('SMTP login rejected — SMTP_USER must be the FULL email address and SMTP_PASS its mailbox password.'); return false; }
    $put('MAIL FROM:<' . MAIL_FROM . '>'); $get();
    $put('RCPT TO:<' . $to . '>'); $get();
    $put('DATA');
    if ($code($get()) !== 354) { fclose($fp); return false; }
    $headers = "Date: " . date('r') . "\r\n"
             . "From: =?UTF-8?B?" . base64_encode(MAIL_FROM_NAME) . "?= <" . MAIL_FROM . ">\r\n"
             . "To: <" . $to . ">\r\n"
             . "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n"
             . "MIME-Version: 1.0\r\n"
             . "Content-Type: text/html; charset=UTF-8\r\n";
    $msg = preg_replace('/^\./m', '..', $headers . "\r\n" . $html);   // dot-stuffing
    fwrite($fp, $msg . "\r\n.\r\n");
    $ok = $code($get()) === 250;
    mail_last_error($ok ? '' : 'Mail server did not accept the message (no final 250).');
    $put('QUIT'); fclose($fp);
    return $ok;
}

/** Branded HTML wrapper with an optional call-to-action button. */
function mail_layout(string $title, string $bodyHtml, string $btnText = '', string $btnUrl = ''): string {
    $btn = $btnUrl
        ? '<tr><td style="padding:10px 0 4px;"><a href="' . htmlspecialchars($btnUrl) . '" style="display:inline-block;background:#c0392b;color:#ffffff;text-decoration:none;font-weight:700;font-size:14px;padding:13px 28px;border-radius:9px;">' . htmlspecialchars($btnText) . '</a></td></tr>'
        : '';
    $raw = $btnUrl
        ? '<tr><td style="padding:16px 0 0;font-size:11px;color:#9b9b9b;line-height:1.5;">Or paste this link into your browser:<br><span style="color:#1e3a5f;word-break:break-all;">' . htmlspecialchars($btnUrl) . '</span></td></tr>'
        : '';
    return '<!doctype html><html><body style="margin:0;background:#f1f1ee;font-family:Arial,Helvetica,sans-serif;">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f1ee;padding:28px 12px;"><tr><td align="center">'
        . '<table role="presentation" width="520" cellpadding="0" cellspacing="0" style="background:#ffffff;border:1px solid #e8e8e8;border-radius:14px;overflow:hidden;max-width:520px;width:100%;">'
        . '<tr><td style="background:#0d1f36;padding:18px 26px;color:#ffffff;font-size:18px;font-weight:800;letter-spacing:-.02em;">&#9638; bildfie</td></tr>'
        . '<tr><td style="padding:26px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0">'
        . '<tr><td style="font-size:19px;font-weight:800;color:#0d0d0d;padding-bottom:10px;">' . htmlspecialchars($title) . '</td></tr>'
        . '<tr><td style="font-size:14px;color:#3a3a3a;line-height:1.65;padding-bottom:8px;">' . $bodyHtml . '</td></tr>'
        . $btn . $raw
        . '</table></td></tr>'
        . '<tr><td style="padding:16px 26px;border-top:1px solid #f2f2f0;font-size:11px;color:#9b9b9b;">&copy; ' . date('Y') . ' bildfie &middot; Build Smarter. Connect Better.</td></tr>'
        . '</table></td></tr></table></body></html>';
}
