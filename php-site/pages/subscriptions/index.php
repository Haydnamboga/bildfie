<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_login();
$page_title = 'Subscriptions'; $sp = 'subscriptions';
$topbar_crumb = 'Billing';
$topbar_action = '<a href="/pages/account/subscription.php" class="bf-topbar-new"><i class="bi bi-stars me-1"></i>Change plan</a>';
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<div class="bf-shell">
  <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
  <div class="bf-main">
    <?php include __DIR__ . '/../../includes/topbar.php'; ?>
    <div class="bf-body" style="padding:24px 28px;">
      <div class="bf-dash-h"><div>
        <div class="bf-eyebrow2"><span>—</span> Billing</div>
        <h1 class="bf-dash-title">Subscriptions</h1>
        <p class="bf-dash-sub">Manage your bildfie plan, usage and billing history.</p>
      </div></div>

      <div class="row g-3">
        <!-- Current plan -->
        <div class="col-lg-8">
          <div class="bf-pf-card" style="margin-bottom:0;">
            <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-stars"></i> Current plan</div><span class="bf-badge green">Active</span></div>
            <div class="bf-pf-card-b">
              <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:14px;align-items:flex-start;">
                <div>
                  <div style="font-size:22px;font-weight:900;color:var(--ink);">Pro <span style="font-size:13px;font-weight:700;color:#1e3a5f;">· KES 2,500/mo</span></div>
                  <div style="font-size:12.5px;color:var(--ink-3);margin-top:4px;">Renews on <b style="color:var(--ink);">28 Jun 2026</b> · Visa •••• 4242</div>
                </div>
                <div style="display:flex;gap:8px;">
                  <a href="/pages/account/subscription.php" class="bf-btn-navy" style="text-decoration:none;">Upgrade</a>
                  <button class="bf-btn-ghost">Cancel plan</button>
                </div>
              </div>
              <div style="border-top:1px solid var(--line-2);margin-top:16px;padding-top:16px;">
                <div style="font-size:12px;font-weight:800;color:var(--ink);margin-bottom:12px;">This month's usage</div>
                <?php foreach ([
                  ['Bid applications','Unlimited','—',0,'#166534'],
                  ['Active project posts','2 of 3 used',null,66,'#1e3a5f'],
                  ['Featured listings','1 of 1 used',null,100,'#c0392b'],
                  ['Team seats','4 of 10 used',null,40,'#1e3a5f'],
                ] as [$k,$v,$x,$pct,$c]): ?>
                <div style="margin-bottom:12px;">
                  <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:5px;"><span style="font-weight:700;color:var(--ink);"><?= $k ?></span><span style="color:var(--ink-3);"><?= $v ?></span></div>
                  <div style="height:6px;border-radius:99px;background:var(--line-2);overflow:hidden;"><div style="height:100%;width:<?= $pct ?>%;background:<?= $c ?>;"></div></div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <!-- Billing history -->
          <div class="bf-pf-card" style="margin-bottom:0;">
            <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-clock-history"></i> Billing history</div></div>
            <div class="bf-tbl-head"><div style="width:120px;">Invoice</div><div style="flex:1;">Plan</div><div style="width:110px;" class="d-none d-md-block">Date</div><div style="width:110px;">Amount</div><div style="width:90px;">Status</div></div>
            <?php foreach ([
              ['BL-2026-06','Pro — Monthly','28 May 2026','KES 2,500'],
              ['BL-2026-05','Pro — Monthly','28 Apr 2026','KES 2,500'],
              ['BL-2026-04','Pro — Monthly','28 Mar 2026','KES 2,500'],
              ['BL-2026-03','Free → Pro','28 Feb 2026','KES 2,500'],
            ] as [$num,$plan,$date,$amt]): ?>
            <div class="bf-tbl-row">
              <div style="width:120px;font-weight:800;color:#1e3a5f;"><?= $num ?></div>
              <div style="flex:1;" class="pri"><?= $plan ?></div>
              <div style="width:110px;" class="d-none d-md-block"><?= $date ?></div>
              <div style="width:110px;font-weight:800;color:var(--ink);"><?= $amt ?></div>
              <div style="width:90px;"><span class="bf-badge green">Paid</span></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Plan perks -->
        <div class="col-lg-4">
          <div class="bf-pf-card" style="margin-bottom:0;">
            <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-check2-circle"></i> Your Pro benefits</div></div>
            <div class="bf-pf-card-b">
              <?php foreach (['Unlimited bid applications','Priority search placement','NCA verification badge','Client review system','Analytics dashboard','WhatsApp alerts','3 active project posts'] as $f): ?>
              <div style="display:flex;gap:9px;font-size:12.5px;color:var(--ink-2);padding:5px 0;"><i class="bi bi-check-circle-fill" style="color:#16a34a;font-size:13px;margin-top:2px;"></i><?= $f ?></div>
              <?php endforeach; ?>
              <a href="/pages/account/subscription.php" style="display:block;text-align:center;margin-top:12px;font-size:12px;font-weight:700;color:#c0392b;text-decoration:none;">Compare all plans →</a>
            </div>
          </div>
          <div class="bf-pf-card" style="margin-bottom:0;">
            <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-credit-card"></i> Payment method</div></div>
            <div class="bf-pf-card-b" style="padding-top:14px;">
              <div style="display:flex;align-items:center;gap:12px;"><span style="width:40px;height:40px;border-radius:9px;background:#eaf0f6;color:#1e3a5f;display:flex;align-items:center;justify-content:center;font-size:18px;"><i class="bi bi-credit-card-2-front"></i></span><div><div style="font-size:13px;font-weight:700;color:var(--ink);">Visa •••• 4242</div><div style="font-size:11px;color:var(--ink-4);">Expires 09/27</div></div></div>
              <button class="bf-btn-ghost" style="width:100%;margin-top:12px;">Update card</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
