<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';

require_login();
$u   = current_user();
$uid = (int) $u['id'];

$tab = $_GET['tab'] ?? 'preferences';
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['_tab'] ?? $tab;

    if ($action === 'preferences') {
        user_save_preferences($uid, $_POST['currency_code'] ?? 'KES', $_POST['language'] ?? 'en', $_POST['timezone'] ?? 'Africa/Nairobi');
        $msg = 'Preferences saved.';
    }
    elseif ($action === 'notifications') {
        user_save_notifications($uid, [
            'notif_bids'       => !empty($_POST['notif_bids'])       ? 1 : 0,
            'notif_messages'   => !empty($_POST['notif_messages'])   ? 1 : 0,
            'notif_payments'   => !empty($_POST['notif_payments'])   ? 1 : 0,
            'notif_milestones' => !empty($_POST['notif_milestones']) ? 1 : 0,
            'notif_weekly'     => !empty($_POST['notif_weekly'])     ? 1 : 0,
            'notif_promos'     => !empty($_POST['notif_promos'])     ? 1 : 0,
        ]);
        $msg = 'Notification preferences saved.';
    }
    elseif ($action === 'security') {
        if (!empty($_POST['current_password'])) {
            $res = user_change_password($uid, $_POST['current_password'], $_POST['new_password'] ?? '');
            if ($res['ok']) { $msg = 'Password changed.'; }
            else { $err = $res['error']; }
        } else {
            user_save_security($uid, !empty($_POST['two_factor']) ? 1 : 0, !empty($_POST['login_alerts']) ? 1 : 0);
            $msg = 'Security settings saved.';
        }
    }
    elseif ($action === 'wallet' && !empty($_POST['deposit_amount'])) {
        $amount = (float) $_POST['deposit_amount'];
        $res = wallet_post($uid, 'deposit', $amount, 'Manual top-up', 'Manual');
        $msg = $res['ok'] ? 'Deposit of KES ' . number_format($amount, 2) . ' queued (demo).' : ($res['error'] ?? 'Error');
    }

    header('Location: /pages/account/settings.php?tab=' . urlencode($tab) . ($msg ? '&saved=1' : ''));
    exit;
}

if (isset($_GET['saved'])) $msg = 'Saved successfully.';

$prefs   = user_prefs($uid);
$wallet  = user_wallet($uid);
$wtotals = wallet_totals($uid);
$whist   = wallet_history($uid, 20);

$sp           = 'settings';
$topbar_title = 'Settings';
$page_title   = 'Settings';

$timezones = ['Africa/Nairobi', 'Africa/Lagos', 'Africa/Dar_es_Salaam', 'Africa/Kampala', 'Africa/Johannesburg', 'UTC', 'Europe/London', 'America/New_York', 'Asia/Dubai'];
?>
<?php require_once __DIR__ . '/../../includes/head.php'; ?>
<style>
.bf-layout{display:flex;min-height:100vh;}
.bf-main{flex:1;display:flex;flex-direction:column;min-width:0;margin-left:var(--sidebar-w);}
@media(max-width:991px){.bf-main{margin-left:0;}}
.bf-body{flex:1;padding:28px 24px;background:var(--surface);}
.section-card{background:#fff;border:1px solid var(--line);border-radius:12px;padding:22px;margin-bottom:16px;}
.set-tab{font-size:.875rem;font-weight:600;padding:8px 16px;text-decoration:none;border-bottom:2px solid transparent;color:var(--ink-3);}
.set-tab.active{color:#1e3a5f;border-bottom-color:#1e3a5f;}
</style>

<div class="bf-layout">
<?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
<div class="bf-main">
<?php require_once __DIR__ . '/../../includes/topbar.php'; ?>
<div class="bf-body">

  <h2 style="font-size:1.2rem;font-weight:800;margin:0 0 20px;">Settings</h2>

  <!-- Tabs -->
  <div style="border-bottom:1px solid var(--line);margin-bottom:20px;display:flex;gap:0;overflow-x:auto;">
    <?php foreach (['preferences'=>'Preferences','notifications'=>'Notifications','security'=>'Security','wallet'=>'Wallet'] as $tk=>$tl): ?>
      <a href="?tab=<?= $tk ?>" class="set-tab <?= $tab===$tk?'active':'' ?>"><?= $tl ?></a>
    <?php endforeach; ?>
  </div>

  <?php if ($msg): ?><div class="alert alert-success py-2 px-3 mb-3" style="font-size:.85rem;"><i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-danger py-2 px-3 mb-3" style="font-size:.85rem;"><i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($err) ?></div><?php endif; ?>

  <?php if ($tab === 'preferences'): ?>
  <div class="section-card">
    <h3 style="font-size:.95rem;font-weight:700;margin-bottom:18px;">Preferences</h3>
    <form method="POST">
      <input type="hidden" name="_tab" value="preferences">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label" style="font-size:.8rem;font-weight:600;">Currency</label>
          <select name="currency_code" class="form-select">
            <?php foreach (['KES','USD','EUR','GBP','NGN','TZS','UGX'] as $c): ?>
              <option value="<?= $c ?>" <?= ($prefs['currency_code'] ?? 'KES') === $c ? 'selected' : '' ?>><?= $c ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label" style="font-size:.8rem;font-weight:600;">Language</label>
          <select name="language" class="form-select">
            <option value="en" <?= ($prefs['language'] ?? 'en') === 'en' ? 'selected' : '' ?>>English</option>
            <option value="sw" <?= ($prefs['language'] ?? '') === 'sw' ? 'selected' : '' ?>>Kiswahili</option>
            <option value="fr" <?= ($prefs['language'] ?? '') === 'fr' ? 'selected' : '' ?>>Français</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label" style="font-size:.8rem;font-weight:600;">Timezone</label>
          <select name="timezone" class="form-select">
            <?php foreach ($timezones as $tz): ?>
              <option value="<?= $tz ?>" <?= ($prefs['timezone'] ?? 'Africa/Nairobi') === $tz ? 'selected' : '' ?>><?= $tz ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <button type="submit" class="bf-btn-dark mt-3">Save Preferences</button>
    </form>
  </div>

  <?php elseif ($tab === 'notifications'): ?>
  <div class="section-card">
    <h3 style="font-size:.95rem;font-weight:700;margin-bottom:18px;">Email Notifications</h3>
    <form method="POST">
      <input type="hidden" name="_tab" value="notifications">
      <?php
      $notif_opts = [
        'notif_bids'       => ['Bids &amp; Quotes', 'When someone sends you a bid or quote request'],
        'notif_messages'   => ['Messages', 'When you receive a new message'],
        'notif_payments'   => ['Payments', 'Payment confirmations and wallet updates'],
        'notif_milestones' => ['Milestones', 'When project milestones are updated'],
        'notif_weekly'     => ['Weekly Summary', 'A weekly digest of your activity'],
        'notif_promos'     => ['Promotions', 'Offers, tips and product updates from bildfie'],
      ];
      ?>
      <?php foreach ($notif_opts as $key => [$title, $desc]): ?>
        <div class="d-flex align-items-start justify-content-between py-3" style="border-bottom:1px solid var(--line-2);">
          <div>
            <div style="font-weight:600;font-size:.875rem;"><?= $title ?></div>
            <div style="font-size:.8rem;color:var(--ink-3);"><?= $desc ?></div>
          </div>
          <div class="form-check form-switch ms-3 mt-1">
            <input type="checkbox" class="form-check-input" name="<?= $key ?>" id="<?= $key ?>"
                   role="switch" style="cursor:pointer;"
                   <?= !empty($prefs[$key]) ? 'checked' : '' ?>>
          </div>
        </div>
      <?php endforeach; ?>
      <button type="submit" class="bf-btn-dark mt-3">Save Notifications</button>
    </form>
  </div>

  <?php elseif ($tab === 'security'): ?>
  <div class="section-card mb-3">
    <h3 style="font-size:.95rem;font-weight:700;margin-bottom:18px;">Change Password</h3>
    <form method="POST">
      <input type="hidden" name="_tab" value="security">
      <div class="row g-3" style="max-width:460px;">
        <div class="col-12">
          <label class="form-label" style="font-size:.8rem;font-weight:600;">Current Password</label>
          <input type="password" name="current_password" class="form-control" placeholder="Your current password">
        </div>
        <div class="col-12">
          <label class="form-label" style="font-size:.8rem;font-weight:600;">New Password</label>
          <input type="password" name="new_password" class="form-control" placeholder="Min. 6 characters">
        </div>
      </div>
      <button type="submit" class="bf-btn-dark mt-3">Change Password</button>
    </form>
  </div>
  <div class="section-card">
    <h3 style="font-size:.95rem;font-weight:700;margin-bottom:18px;">Security Options</h3>
    <form method="POST">
      <input type="hidden" name="_tab" value="security">
      <div class="d-flex align-items-start justify-content-between py-3" style="border-bottom:1px solid var(--line-2);">
        <div>
          <div style="font-weight:600;font-size:.875rem;">Two-Factor Authentication</div>
          <div style="font-size:.8rem;color:var(--ink-3);">Add an extra layer of security to your account (coming soon)</div>
        </div>
        <div class="form-check form-switch ms-3 mt-1">
          <input type="checkbox" class="form-check-input" name="two_factor" role="switch" <?= !empty($prefs['two_factor']) ? 'checked' : '' ?> disabled>
        </div>
      </div>
      <div class="d-flex align-items-start justify-content-between py-3">
        <div>
          <div style="font-weight:600;font-size:.875rem;">Login Alerts</div>
          <div style="font-size:.8rem;color:var(--ink-3);">Get notified when your account is signed in from a new device</div>
        </div>
        <div class="form-check form-switch ms-3 mt-1">
          <input type="checkbox" class="form-check-input" name="login_alerts" role="switch" <?= !empty($prefs['login_alerts']) ? 'checked' : '' ?>>
        </div>
      </div>
      <button type="submit" class="bf-btn-dark mt-2">Save Settings</button>
    </form>
  </div>

  <?php elseif ($tab === 'wallet'): ?>
  <!-- Balance card -->
  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <div class="section-card text-center">
        <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;font-weight:700;color:var(--ink-3);margin-bottom:8px;">Balance</div>
        <div style="font-size:2rem;font-weight:800;color:#0d0d0d;"><?= htmlspecialchars($wallet['currency_code'] ?? 'KES') ?> <?= number_format((float)$wallet['balance'], 2) ?></div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="section-card text-center">
        <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;font-weight:700;color:var(--ink-3);margin-bottom:8px;">Total Credits</div>
        <div style="font-size:1.4rem;font-weight:800;color:#16a34a;">KES <?= number_format((float)$wtotals['credits'], 2) ?></div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="section-card text-center">
        <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;font-weight:700;color:var(--ink-3);margin-bottom:8px;">Total Debits</div>
        <div style="font-size:1.4rem;font-weight:800;color:#b91c1c;">KES <?= number_format((float)$wtotals['debits'], 2) ?></div>
      </div>
    </div>
  </div>

  <!-- Deposit form (demo) -->
  <div class="section-card mb-3">
    <h3 style="font-size:.95rem;font-weight:700;margin-bottom:12px;">Top Up Wallet (Demo)</h3>
    <form method="POST" class="d-flex gap-2 align-items-end">
      <input type="hidden" name="_tab" value="wallet">
      <div>
        <label class="form-label" style="font-size:.8rem;font-weight:600;">Amount (KES)</label>
        <input type="number" name="deposit_amount" class="form-control" placeholder="0.00" min="1" step="0.01" required style="width:180px;">
      </div>
      <button type="submit" class="bf-btn-dark">Add Funds</button>
    </form>
    <p style="font-size:.75rem;color:var(--ink-3);margin-top:8px;">Demo mode: funds are credited immediately. Real M-Pesa / card integration coming soon.</p>
  </div>

  <!-- Transaction history -->
  <div class="section-card">
    <h3 style="font-size:.95rem;font-weight:700;margin-bottom:14px;">Transaction History</h3>
    <?php if (empty($whist)): ?>
      <p style="font-size:.875rem;color:var(--ink-3);">No transactions yet.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle mb-0" style="font-size:.85rem;">
          <thead style="background:var(--surface);">
            <tr>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);padding:8px 12px;">Date</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);">Type</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);">Description</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);">Amount</th>
              <th style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-3);">Balance After</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($whist as $tx):
              $credit = in_array($tx['type'], ['deposit','refund','payout'], true);
            ?>
            <tr>
              <td style="padding:9px 12px;color:var(--ink-3);"><?= date('d M Y', strtotime($tx['created_at'])) ?></td>
              <td><span style="display:inline-block;padding:1px 8px;border-radius:20px;font-size:.72rem;font-weight:600;
                               color:<?= $credit ? '#166534' : '#b91c1c' ?>;background:<?= $credit ? '#dcfce7' : '#fef2f2' ?>;">
                    <?= ucfirst($tx['type']) ?></span></td>
              <td style="color:var(--ink-2);"><?= htmlspecialchars($tx['description'] ?? '—') ?></td>
              <td style="font-weight:700;color:<?= $credit ? '#16a34a' : '#b91c1c' ?>;">
                <?= $credit ? '+' : '-' ?> KES <?= number_format((float)$tx['amount'], 2) ?>
              </td>
              <td style="color:var(--ink-2);">KES <?= number_format((float)$tx['balance_after'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

</div>
</div>
</div>

<?php require_once __DIR__ . '/../../includes/footer-dashboard.php'; ?>
