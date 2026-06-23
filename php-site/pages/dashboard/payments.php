<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_login();
$uid = (int) current_user()['id'];

// ── withdraw / top-up (POST → redirect) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (float) str_replace([',', ' '], '', $_POST['amount'] ?? '0');
    $method = trim($_POST['method'] ?? '') ?: 'Wallet';
    if (($_POST['action'] ?? '') === 'withdraw') {
        $r = wallet_post($uid, 'withdrawal', $amount, 'Withdrawal to ' . $method, $method);
        $_SESSION['pay_flash'] = $r['ok'] ? ['ok', 'Withdrawal of ' . number_format($amount) . ' processed (' . $r['reference'] . ').'] : ['err', $r['error']];
    } elseif (($_POST['action'] ?? '') === 'deposit') {
        $r = wallet_post($uid, 'deposit', $amount, 'Wallet top-up via ' . $method, $method);
        $_SESSION['pay_flash'] = $r['ok'] ? ['ok', 'Top-up of ' . number_format($amount) . ' received.'] : ['err', $r['error']];
    }
    header('Location: /pages/dashboard/payments.php'); exit;
}

$page_title = 'Payments'; $sp = 'payments';
$flash  = $_SESSION['pay_flash'] ?? null; unset($_SESSION['pay_flash']);
$wallet = user_wallet($uid);
$totals = wallet_totals($uid);
$txns   = wallet_history($uid, 100);
$cur    = $wallet['currency_code'] ?: 'KES';

$txnMap = [
  'deposit'    => ['Received','#166534','#f0fdf4','bi-arrow-down-left','+'],
  'refund'     => ['Refund','#166534','#f0fdf4','bi-arrow-counterclockwise','+'],
  'payout'     => ['Payout','#166534','#f0fdf4','bi-arrow-down-left','+'],
  'withdrawal' => ['Withdrawal','#1e3a5f','#eaf0f6','bi-arrow-up-right','−'],
  'payment'    => ['Payment','#1e3a5f','#eaf0f6','bi-arrow-up-right','−'],
  'fee'        => ['Platform fee','#6b6b6b','#f4f4f2','bi-percent','−'],
];
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<div class="bf-shell">
  <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
  <div class="bf-main">

    <?php $topbar_crumb='Sales'; $topbar_action='<button class="bf-topbar-new" data-bs-toggle="modal" data-bs-target="#withdrawModal" style="border:none;cursor:pointer;"><i class="bi bi-cash-stack me-1"></i>Withdraw Funds</button>'; include __DIR__ . '/../../includes/topbar.php'; ?>

    <div class="bf-body" style="padding:28px;">

      <div class="mb-4">
        <div style="font-size:10px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:#1e3a5f;margin-bottom:8px;"><span style="opacity:.4;font-weight:400;">—</span> Wallet</div>
        <h1 style="font-size:clamp(20px,3vw,26px);font-weight:800;color:var(--ink);margin:0 0 4px;letter-spacing:-.02em;">Payments &amp; wallet</h1>
        <p style="font-size:13px;color:var(--ink-3);margin:0;">Track every movement in and out of your bildfie wallet.</p>
      </div>

      <?php if ($flash): ?>
      <div data-ms="6000" style="background:<?= $flash[0]==='ok'?'#f0fdf4':'#fef2f2' ?>;border:1px solid <?= $flash[0]==='ok'?'#bbf7d0':'#f3c9c4' ?>;color:<?= $flash[0]==='ok'?'#166534':'#c0392b' ?>;border-radius:11px;padding:11px 15px;font-size:13px;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
        <i class="bi bi-<?= $flash[0]==='ok'?'check-circle-fill':'exclamation-triangle-fill' ?>"></i> <?= htmlspecialchars($flash[1]) ?>
      </div>
      <?php endif; ?>

      <div class="row g-3">
        <div class="col-lg-8">
          <!-- balance cards -->
          <div class="row g-3 mb-3">
            <?php foreach ([
              ['Available balance', $cur.' '.number_format((float)$wallet['balance']),'bi-wallet2','#166534','#f0fdf4'],
              ['Total received',    $cur.' '.number_format((float)$totals['credits']),'bi-arrow-down-left','#1e3a5f','#eaf0f6'],
              ['Total paid out',    $cur.' '.number_format((float)$totals['debits']),'bi-arrow-up-right','#b45309','#fffbeb'],
            ] as [$label,$value,$icon,$iconCol,$iconBg]): ?>
            <div class="col-md-4">
              <div style="background:var(--white);border:1px solid var(--line);border-radius:14px;padding:18px;height:100%;">
                <div style="width:38px;height:38px;border-radius:10px;background:<?=$iconBg?>;display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
                  <i class="bi <?=$icon?>" style="font-size:17px;color:<?=$iconCol?>;"></i>
                </div>
                <div style="font-size:20px;font-weight:900;color:var(--ink);line-height:1;letter-spacing:-.02em;"><?=$value?></div>
                <div style="font-size:11px;color:var(--ink-3);margin-top:6px;"><?=$label?></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>

          <!-- transactions -->
          <div style="background:var(--white);border:1px solid var(--line);border-radius:14px;overflow:hidden;">
            <div class="d-flex align-items-center justify-content-between" style="padding:16px 20px;border-bottom:1px solid var(--line);">
              <div style="font-size:13px;font-weight:800;color:var(--ink);">Transaction history</div>
              <a href="/pages/dashboard/payments-export.php" style="font-size:12px;font-weight:700;color:#c0392b;text-decoration:none;">Export CSV →</a>
            </div>

            <?php if (!$txns): ?>
            <div style="padding:44px 20px;text-align:center;color:var(--ink-4);">
              <i class="bi bi-wallet2" style="font-size:32px;opacity:.4;"></i>
              <p style="margin:10px 0 14px;font-size:13px;">No transactions yet.</p>
              <button class="bf-topbar-new" data-bs-toggle="modal" data-bs-target="#depositModal" style="border:none;cursor:pointer;"><i class="bi bi-plus-lg me-1"></i>Top up wallet</button>
            </div>
            <?php endif; ?>

            <?php foreach ($txns as $t):
              [$tLabel,$tCol,$tBg,$tIcon,$sign] = $txnMap[$t['type']] ?? ['Movement','#6b6b6b','#f4f4f2','bi-dot','−'];
              $isCredit = $sign === '+';
              $date = date('d M Y', strtotime($t['created_at'])); ?>
            <div class="d-flex align-items-center gap-3" style="padding:14px 20px;border-top:1px solid var(--line-2);">
              <span style="width:38px;height:38px;border-radius:10px;background:<?=$tBg?>;color:<?=$tCol?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:15px;"><i class="bi <?=$tIcon?>"></i></span>
              <div style="flex:1;min-width:0;">
                <div style="font-size:13px;font-weight:700;color:var(--ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($t['description'] ?: $tLabel) ?></div>
                <div style="font-size:11px;color:var(--ink-4);margin-top:2px;"><?= $tLabel ?> · <?= htmlspecialchars($t['method'] ?: '—') ?> · <?= $date ?><?= $t['status']!=='completed' ? ' · <span style="color:#b45309;font-weight:700;">'.ucfirst($t['status']).'</span>' : '' ?></div>
              </div>
              <div style="font-size:13.5px;font-weight:800;white-space:nowrap;color:<?= $isCredit?'#166534':'var(--ink)' ?>;"><?= $sign ?> <?= $cur ?> <?= number_format((float)$t['amount']) ?></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- right -->
        <div class="col-lg-4">
          <div style="background:var(--white);border:1px solid var(--line);border-radius:14px;padding:20px;margin-bottom:16px;">
            <div style="font-size:13px;font-weight:800;color:var(--ink);margin-bottom:4px;">Move money</div>
            <p style="font-size:11.5px;color:var(--ink-3);margin:0 0 14px;line-height:1.5;">Top up your wallet or withdraw your available balance to M-Pesa or bank.</p>
            <button class="bf-topbar-new w-100 mb-2" data-bs-toggle="modal" data-bs-target="#depositModal" style="border:none;cursor:pointer;justify-content:center;display:flex;"><i class="bi bi-plus-lg me-1"></i>Top up wallet</button>
            <button class="bf-btn-ghost w-100" data-bs-toggle="modal" data-bs-target="#withdrawModal" style="border:1px solid var(--line);background:#fff;cursor:pointer;justify-content:center;display:flex;"><i class="bi bi-cash-stack me-1"></i>Withdraw funds</button>
          </div>

          <div style="background:#eaf0f6;border:1px solid #d6e2ee;border-radius:14px;padding:20px;">
            <div style="display:flex;gap:11px;align-items:flex-start;">
              <i class="bi bi-shield-fill-check" style="font-size:20px;color:#1e3a5f;flex-shrink:0;margin-top:1px;"></i>
              <div>
                <div style="font-size:12.5px;font-weight:800;color:#1e3a5f;">Secure wallet</div>
                <div style="font-size:11.5px;color:var(--ink-3);line-height:1.55;margin-top:3px;">Every movement is recorded in your ledger with a unique reference. Withdrawals are checked against your available balance.</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Withdraw modal -->
<div class="modal fade" id="withdrawModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" method="post" style="border:none;border-radius:16px;">
      <input type="hidden" name="action" value="withdraw">
      <div class="modal-header" style="border-bottom:1px solid var(--line);"><h5 class="modal-title" style="font-size:15px;font-weight:800;">Withdraw funds</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body" style="padding:18px 20px;">
        <p style="font-size:12px;color:var(--ink-3);margin:0 0 14px;">Available balance: <b style="color:#166534;"><?= $cur ?> <?= number_format((float)$wallet['balance']) ?></b></p>
        <label class="bf-lbl">Amount (<?= $cur ?>)</label>
        <input name="amount" type="number" step="0.01" min="1" required class="form-control bf-inp mb-3" placeholder="50000">
        <label class="bf-lbl">Withdraw to</label>
        <select name="method" class="form-select bf-inp"><option>M-Pesa</option><option>Bank transfer</option><option>Equity Bank</option></select>
      </div>
      <div class="modal-footer" style="border-top:1px solid var(--line);">
        <button type="button" class="bf-btn-ghost" data-bs-dismiss="modal" style="border:1px solid var(--line);background:#fff;">Cancel</button>
        <button type="submit" class="bf-topbar-new" style="border:none;cursor:pointer;"><i class="bi bi-cash-stack me-1"></i>Withdraw</button>
      </div>
    </form>
  </div>
</div>

<!-- Top-up modal -->
<div class="modal fade" id="depositModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" method="post" style="border:none;border-radius:16px;">
      <input type="hidden" name="action" value="deposit">
      <div class="modal-header" style="border-bottom:1px solid var(--line);"><h5 class="modal-title" style="font-size:15px;font-weight:800;">Top up wallet</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body" style="padding:18px 20px;">
        <label class="bf-lbl">Amount (<?= $cur ?>)</label>
        <input name="amount" type="number" step="0.01" min="1" required class="form-control bf-inp mb-3" placeholder="100000">
        <label class="bf-lbl">Pay with</label>
        <select name="method" class="form-select bf-inp"><option>M-Pesa</option><option>Card</option><option>Bank transfer</option></select>
      </div>
      <div class="modal-footer" style="border-top:1px solid var(--line);">
        <button type="button" class="bf-btn-ghost" data-bs-dismiss="modal" style="border:1px solid var(--line);background:#fff;">Cancel</button>
        <button type="submit" class="bf-topbar-new" style="border:none;cursor:pointer;"><i class="bi bi-plus-lg me-1"></i>Top up</button>
      </div>
    </form>
  </div>
</div>
<style>.bf-lbl{font-size:11.5px;font-weight:700;color:var(--ink-2);display:block;margin-bottom:5px;}.bf-inp{font-size:13px;border-color:var(--line);border-radius:9px;padding:9px 12px;}</style>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
