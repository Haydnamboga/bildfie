<?php
require_once __DIR__ . '/config/admin.php';
require_admin();
require_once __DIR__ . '/../config/mailer.php';
$page_title = 'Mail test';
$ap = '';
$cfg = mail_config_status();
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = trim($_POST['to'] ?? '');
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $result = ['ok' => false, 'via' => '', 'err' => 'Enter a valid email address.', 'to' => $to];
    } else {
        mail_last_error('');
        $html = mail_layout(
            'It works! 🎉',
            'If you can read this, your bildfie email delivery is configured correctly — password-reset and verification emails will reach your members. Sent from the admin Mail test tool.'
        );
        // When SMTP is configured, test SMTP directly so the mail() fallback can't hide a failure.
        if ($cfg['smtp']) { $ok = smtp_send($to, 'bildfie · mail delivery test', $html); $via = 'SMTP (' . $cfg['smtp_host'] . ':' . $cfg['smtp_port'] . ')'; }
        else              { $ok = send_mail($to, 'bildfie · mail delivery test', $html); $via = $cfg['transport']; }
        $result = ['ok' => $ok, 'via' => $via, 'err' => mail_last_error(), 'to' => $to];
    }
}
$prefill = $result['to'] ?? (current_admin()['email'] ?? '');
?>
<?php include __DIR__ . '/includes/head.php'; ?>
<div class="adm-shell">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <div class="adm-main">
    <?php include __DIR__ . '/includes/topbar.php'; ?>

    <div class="adm-body" style="max-width:760px;">
      <div class="bf-dash-h"><div>
        <div class="bf-crumb"><a href="/admin/index.php">Dashboard</a> <i class="bi bi-chevron-right"></i> <span style="color:var(--ink-2);">Mail test</span></div>
        <h1 class="bf-dash-title">Email delivery test</h1>
        <p class="bf-dash-sub">Confirm your live mail setup and send yourself a test message.</p>
      </div></div>

      <!-- result -->
      <?php if ($result): ?>
        <?php if ($result['ok']): ?>
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:12px;padding:14px 16px;margin-bottom:16px;">
          <div style="font-weight:800;font-size:14px;"><i class="bi bi-check-circle-fill me-1"></i> Test email sent via <?= htmlspecialchars($result['via']) ?></div>
          <div style="font-size:12.5px;margin-top:4px;">Check <strong><?= htmlspecialchars($result['to']) ?></strong> — including the <strong>Spam/Promotions</strong> folder. If it arrived, password resets will work.</div>
          <?php if (!$cfg['smtp'] && $cfg['env'] === 'production'): ?><div style="font-size:11.5px;margin-top:6px;color:#854d0e;">Note: this used PHP <code>mail()</code>, which Gmail often files as spam or drops. For reliable inbox delivery, set up SMTP below.</div><?php endif; ?>
          <?php if ($cfg['env'] !== 'production'): ?><div style="font-size:11.5px;margin-top:6px;color:#1e3a5f;">You're in <strong>dev</strong> — the message was written to <code>storage/mail.log</code> instead of being emailed.</div><?php endif; ?>
        </div>
        <?php else: ?>
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#c0392b;border-radius:12px;padding:14px 16px;margin-bottom:16px;">
          <div style="font-weight:800;font-size:14px;"><i class="bi bi-x-octagon-fill me-1"></i> Sending failed</div>
          <div style="font-size:12.5px;margin-top:4px;"><?= htmlspecialchars($result['err'] ?: 'Unknown error.') ?></div>
        </div>
        <?php endif; ?>
      <?php endif; ?>

      <!-- current config -->
      <div style="background:var(--white);border:1px solid var(--line);border-radius:14px;padding:18px 20px;margin-bottom:16px;">
        <div style="font-size:13px;font-weight:800;color:var(--ink);margin-bottom:12px;"><i class="bi bi-gear-fill me-1" style="color:#1e3a5f;"></i> Current mail configuration</div>
        <?php
        $rows = [
          ['Environment', $cfg['env']],
          ['Sending method', $cfg['transport']],
          ['SMTP host', $cfg['smtp_host']],
          ['SMTP port', $cfg['smtp_port'] . ' (' . $cfg['smtp_secure'] . ')'],
          ['SMTP username', $cfg['smtp_user']],
          ['SMTP password', $cfg['smtp_pass']],
          ['From', $cfg['from_name'] . ' <' . $cfg['from'] . '>'],
        ];
        foreach ($rows as [$k, $v]): ?>
        <div style="display:flex;justify-content:space-between;gap:14px;padding:7px 0;border-top:1px solid var(--line-2);font-size:12.5px;">
          <span style="color:var(--ink-4);"><?= htmlspecialchars($k) ?></span>
          <span style="color:var(--ink);font-weight:600;word-break:break-all;text-align:right;"><?= htmlspecialchars((string)$v) ?></span>
        </div>
        <?php endforeach; ?>
        <?php if (!$cfg['smtp'] && $cfg['env'] === 'production'): ?>
        <div style="background:#fffbeb;border:1px solid #fde68a;color:#92400e;border-radius:10px;padding:10px 13px;margin-top:12px;font-size:12px;line-height:1.6;">
          <strong><i class="bi bi-exclamation-triangle-fill"></i> SMTP is not configured</strong> — emails are going through PHP <code>mail()</code>, which Gmail/Outlook usually reject or spam-file. Add an SMTP block to <code>config/config.local.php</code> (create a <code>noreply@bildfie.com</code> mailbox in cPanel → Email Accounts first), then re-test here.
        </div>
        <?php endif; ?>
      </div>

      <!-- send test -->
      <div style="background:var(--white);border:1px solid var(--line);border-radius:14px;padding:18px 20px;">
        <div style="font-size:13px;font-weight:800;color:var(--ink);margin-bottom:10px;"><i class="bi bi-send-fill me-1" style="color:#1e3a5f;"></i> Send a test email</div>
        <form method="post" class="d-flex flex-wrap gap-2" style="align-items:center;">
          <input type="email" name="to" value="<?= htmlspecialchars($prefill) ?>" placeholder="you@email.com" required
            style="flex:1;min-width:240px;font-size:13px;border:1px solid var(--line);border-radius:9px;padding:10px 13px;outline:none;font-family:inherit;color:var(--ink);">
          <button type="submit" style="font-size:12.5px;font-weight:700;background:#1e3a5f;color:#fff;border:none;padding:10px 20px;border-radius:9px;cursor:pointer;white-space:nowrap;">Send test</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/foot.php'; ?>
