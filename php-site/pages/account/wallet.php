<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_login();
$page_title = 'Wallet'; $sp = 'wallet';
$uid = (int) current_user()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amt = (float) str_replace([',', ' '], '', $_POST['amount'] ?? '0');
    $method = in_array($_POST['method'] ?? '', ['M-Pesa','Bank transfer','Card'], true) ? $_POST['method'] : 'M-Pesa';
    if (($_POST['action'] ?? '') === 'deposit') {
        $r = wallet_post($uid, 'deposit', $amt, 'Wallet top-up', $method);
        $_SESSION['w_flash'] = $r['ok'] ? ['ok','Deposited KES ' . number_format($amt) . ' via ' . $method . '.'] : ['err',$r['error']];
    } elseif (($_POST['action'] ?? '') === 'withdraw') {
        $r = wallet_post($uid, 'withdrawal', $amt, 'Withdrawal', $method);
        $_SESSION['w_flash'] = $r['ok'] ? ['ok','Withdrew KES ' . number_format($amt) . ' to ' . $method . '.'] : ['err',$r['error']];
    }
    header('Location: /pages/account/wallet.php'); exit;
}
$flash = $_SESSION['w_flash'] ?? null; unset($_SESSION['w_flash']);
$w   = user_wallet($uid);
$tot = wallet_totals($uid);
$tx  = wallet_history($uid, 100);
$cur = $w['currency_code'] ?? 'KES';

$meta = [
  'deposit'    => ['bi-arrow-down-circle-fill','#166534','#f0fdf4','+'],
  'payout'     => ['bi-cash-coin','#166534','#f0fdf4','+'],
  'refund'     => ['bi-arrow-counterclockwise','#166534','#f0fdf4','+'],
  'withdrawal' => ['bi-arrow-up-circle-fill','#c0392b','#fef2f2','−'],
  'payment'    => ['bi-bag-check-fill','#c0392b','#fef2f2','−'],
  'fee'        => ['bi-percent','#b45309','#fffbeb','−'],
];
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<div class="bf-shell">
  <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
  <div class="bf-main">
    <?php $topbar_crumb='Account'; $topbar_action='<a href="#add-money" class="bf-topbar-new"><i class="bi bi-plus-lg me-1"></i>Add money</a>'; include __DIR__ . '/../../includes/topbar.php'; ?>

    <div class="bf-body" style="padding:28px;">
     <div style="max-width:1040px;margin:0 auto;">

      <div class="mb-4">
        <div style="font-size:10px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:#1e3a5f;margin-bottom:8px;"><span style="opacity:.4;font-weight:400;">—</span> Account</div>
        <h1 style="font-size:clamp(20px,3vw,26px);font-weight:800;color:var(--ink);margin:0 0 4px;letter-spacing:-.02em;">Wallet</h1>
        <p style="font-size:13px;color:var(--ink-3);margin:0;">Your bildfie balance, top-ups, withdrawals and full transaction history.</p>
      </div>

      <?php if ($flash): ?>
      <div style="border-radius:11px;padding:12px 16px;font-size:13px;margin-bottom:18px;<?= $flash[0]==='ok' ? 'background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;' : 'background:#fef2f2;border:1px solid #f3c9c2;color:#c0392b;' ?>">
        <i class="bi <?= $flash[0]==='ok'?'bi-check-circle-fill':'bi-exclamation-triangle-fill' ?> me-1"></i><?= htmlspecialchars($flash[1]) ?>
      </div>
      <?php endif; ?>

      <div class="row g-3">
        <!-- Balance hero -->
        <div class="col-lg-5">
          <div style="background:linear-gradient(135deg,#1e3a5f,#0d1f36);border-radius:16px;padding:24px;color:#fff;height:100%;display:flex;flex-direction:column;justify-content:space-between;min-height:188px;">
            <div style="display:flex;align-items:center;justify-content:space-between;">
              <span style="font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;opacity:.7;">Available balance</span>
              <i class="bi bi-wallet2" style="font-size:20px;opacity:.6;"></i>
            </div>
            <div>
              <div style="font-size:34px;font-weight:900;letter-spacing:-.02em;line-height:1;"><span style="font-size:15px;font-weight:700;opacity:.7;"><?= $cur ?></span> <?= number_format((float)$w['balance']) ?></div>
              <div style="font-size:11.5px;opacity:.65;margin-top:8px;"><i class="bi bi-shield-lock-fill me-1"></i>Secured by bildfie · escrow-ready</div>
            </div>
            <?php if ((float)$tot['pending'] > 0): ?>
            <div style="font-size:11px;background:rgba(255,255,255,.12);border-radius:8px;padding:7px 11px;"><i class="bi bi-hourglass-split me-1"></i><?= $cur ?> <?= number_format((float)$tot['pending']) ?> pending</div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Add money / Withdraw -->
        <div class="col-lg-7" id="add-money">
          <div style="background:var(--white);border:1px solid var(--line);border-radius:16px;padding:20px;height:100%;">
            <div style="font-size:13px;font-weight:800;color:var(--ink);margin-bottom:14px;">Move money</div>
            <div class="row g-3">
              <div class="col-md-6">
                <form method="post" style="border:1px solid var(--line);border-radius:12px;padding:14px;height:100%;">
                  <input type="hidden" name="action" value="deposit">
                  <div style="font-size:12px;font-weight:700;color:#166534;margin-bottom:9px;"><i class="bi bi-arrow-down-circle-fill me-1"></i>Deposit</div>
                  <input name="amount" inputmode="numeric" placeholder="Amount" required style="width:100%;font-size:13px;border:1px solid var(--line);border-radius:8px;padding:9px 12px;margin-bottom:8px;outline:none;font-family:inherit;">
                  <select name="method" style="width:100%;font-size:12.5px;border:1px solid var(--line);border-radius:8px;padding:9px 12px;margin-bottom:10px;outline:none;font-family:inherit;color:var(--ink);"><option>M-Pesa</option><option>Bank transfer</option><option>Card</option></select>
                  <button type="submit" style="width:100%;font-size:12.5px;font-weight:700;color:#fff;background:#166534;border:none;border-radius:9px;padding:9px;cursor:pointer;">Add money</button>
                </form>
              </div>
              <div class="col-md-6">
                <form method="post" style="border:1px solid var(--line);border-radius:12px;padding:14px;height:100%;">
                  <input type="hidden" name="action" value="withdraw">
                  <div style="font-size:12px;font-weight:700;color:#c0392b;margin-bottom:9px;"><i class="bi bi-arrow-up-circle-fill me-1"></i>Withdraw</div>
                  <input name="amount" inputmode="numeric" placeholder="Amount" required style="width:100%;font-size:13px;border:1px solid var(--line);border-radius:8px;padding:9px 12px;margin-bottom:8px;outline:none;font-family:inherit;">
                  <select name="method" style="width:100%;font-size:12.5px;border:1px solid var(--line);border-radius:8px;padding:9px 12px;margin-bottom:10px;outline:none;font-family:inherit;color:var(--ink);"><option>M-Pesa</option><option>Bank transfer</option></select>
                  <button type="submit" style="width:100%;font-size:12.5px;font-weight:700;color:#fff;background:#c0392b;border:none;border-radius:9px;padding:9px;cursor:pointer;">Withdraw</button>
                </form>
              </div>
            </div>
            <div style="font-size:10.5px;color:var(--ink-4);margin-top:10px;"><i class="bi bi-info-circle me-1"></i>Demo movements update your balance instantly. Live M-Pesa / card settlement arrives with payments integration.</div>
          </div>
        </div>
      </div>

      <!-- Summary -->
      <div class="row g-3 mt-1">
        <?php foreach ([
          ['Total in', $tot['credits'], '#166534', '#f0fdf4', 'bi-arrow-down-left'],
          ['Total out', $tot['debits'], '#c0392b', '#fef2f2', 'bi-arrow-up-right'],
          ['Pending', $tot['pending'], '#b45309', '#fffbeb', 'bi-hourglass-split'],
        ] as [$l,$v,$c,$bg,$ic]): ?>
        <div class="col-md-4">
          <div style="background:var(--white);border:1px solid var(--line);border-radius:14px;padding:16px 18px;display:flex;align-items:center;gap:13px;">
            <div style="width:38px;height:38px;border-radius:10px;background:<?=$bg?>;color:<?=$c?>;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;"><i class="bi <?=$ic?>"></i></div>
            <div><div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-4);"><?=$l?></div><div style="font-size:17px;font-weight:800;color:var(--ink);"><?=$cur?> <?= number_format((float)$v) ?></div></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- History -->
      <div style="background:var(--white);border:1px solid var(--line);border-radius:16px;overflow:hidden;margin-top:18px;">
        <div style="padding:15px 20px;border-bottom:1px solid var(--line);font-size:13.5px;font-weight:800;color:var(--ink);"><i class="bi bi-clock-history me-1" style="color:#1e3a5f;"></i> Transaction history</div>
        <?php if (!$tx): ?>
          <div style="padding:34px;text-align:center;color:var(--ink-4);font-size:13px;">No transactions yet — add money to get started.</div>
        <?php else: ?>
        <div style="overflow-x:auto;">
          <table style="width:100%;border-collapse:collapse;font-size:12.5px;min-width:620px;">
            <thead><tr style="background:var(--surface);color:var(--ink-4);font-size:10px;text-transform:uppercase;letter-spacing:.06em;">
              <th style="text-align:left;padding:10px 20px;font-weight:700;">Transaction</th>
              <th style="text-align:left;padding:10px 12px;font-weight:700;">Method</th>
              <th style="text-align:left;padding:10px 12px;font-weight:700;">Date</th>
              <th style="text-align:right;padding:10px 12px;font-weight:700;">Amount</th>
              <th style="text-align:right;padding:10px 20px;font-weight:700;">Balance</th>
            </tr></thead>
            <tbody>
              <?php foreach ($tx as $t): [$ic,$c,$bg,$sign] = $meta[$t['type']] ?? ['bi-dot','#6b6b6b','#f4f4f2','']; ?>
              <tr style="border-top:1px solid var(--line-2);">
                <td style="padding:11px 20px;">
                  <div style="display:flex;align-items:center;gap:11px;">
                    <div style="width:32px;height:32px;border-radius:9px;background:<?=$bg?>;color:<?=$c?>;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;"><i class="bi <?=$ic?>"></i></div>
                    <div style="min-width:0;">
                      <div style="font-weight:700;color:var(--ink);text-transform:capitalize;"><?= htmlspecialchars($t['description'] ?: $t['type']) ?></div>
                      <div style="font-size:10.5px;color:var(--ink-4);font-family:monospace;"><?= htmlspecialchars($t['reference']) ?> · <?= htmlspecialchars(ucfirst($t['type'])) ?></div>
                    </div>
                  </div>
                </td>
                <td style="padding:11px 12px;color:var(--ink-3);"><?= htmlspecialchars($t['method'] ?: '—') ?></td>
                <td style="padding:11px 12px;color:var(--ink-3);white-space:nowrap;"><?= date('d M Y', strtotime($t['created_at'])) ?><div style="font-size:10px;color:var(--ink-4);"><?= date('H:i', strtotime($t['created_at'])) ?></div></td>
                <td style="padding:11px 12px;text-align:right;font-weight:800;color:<?=$c?>;white-space:nowrap;"><?= $sign ?> <?= $cur ?> <?= number_format((float)$t['amount']) ?>
                  <?php if ($t['status'] !== 'completed'): ?><div style="font-size:9px;"><span class="bf-badge <?= $t['status']==='pending'?'amber':'red' ?>" style="font-size:8px;"><?= ucfirst($t['status']) ?></span></div><?php endif; ?>
                </td>
                <td style="padding:11px 20px;text-align:right;color:var(--ink-2);white-space:nowrap;"><?= $cur ?> <?= number_format((float)$t['balance_after']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>

     </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
