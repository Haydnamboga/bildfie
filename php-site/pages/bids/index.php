<?php
/**
 * Bids Received — shows all hire invites and quote requests sent to the
 * current user's provider listing.
 */
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/engage.php';

require_login();
$u   = current_user();
$uid = (int) $u['id'];

$msg = '';
if (!empty($_SESSION['flash'])) { $msg = $_SESSION['flash']; unset($_SESSION['flash']); }

// Fetch all engagements for the current user's provider listing
$all_bids = provider_engagements_for_user($uid);

// Filter by type / search
$type_filter = trim($_GET['type'] ?? '');
$q_filter    = trim($_GET['q'] ?? '');

$bids = array_filter($all_bids, function ($b) use ($type_filter, $q_filter) {
    if ($type_filter !== '' && $b['type'] !== $type_filter) return false;
    if ($q_filter !== '') {
        $hay = strtolower(($b['from_name'] ?? '') . ' ' . ($b['subject'] ?? '') . ' ' . ($b['message'] ?? '') . ' ' . ($b['location'] ?? ''));
        if (strpos($hay, strtolower($q_filter)) === false) return false;
    }
    return true;
});
$bids = array_values($bids);

// KPI counts
$total_cnt = count($all_bids);
$quote_cnt = count(array_filter($all_bids, fn($b) => $b['type'] === 'quote'));
$invite_cnt= count(array_filter($all_bids, fn($b) => $b['type'] === 'invite'));
$new_cnt   = count(array_filter($all_bids, fn($b) => ($b['status'] ?? 'new') === 'new'));

$sp           = 'bids';
$topbar_title = 'Bids Received';
$page_title   = 'Bids Received';

function bid_type_badge(string $t): string {
    if ($t === 'quote') {
        return '<span style="display:inline-block;padding:2px 10px;border-radius:20px;font-size:.72rem;font-weight:600;color:#1e40af;background:#eff6ff;">Quote Request</span>';
    }
    return '<span style="display:inline-block;padding:2px 10px;border-radius:20px;font-size:.72rem;font-weight:600;color:#166534;background:#dcfce7;">Hire Invite</span>';
}

function bid_status_badge(string $s): string {
    $map = [
        'new'       => ['New',       '#b45309', '#fffbeb'],
        'seen'      => ['Seen',      '#1e40af', '#eff6ff'],
        'replied'   => ['Replied',   '#166534', '#dcfce7'],
        'declined'  => ['Declined',  '#b91c1c', '#fef2f2'],
        'accepted'  => ['Accepted',  '#15803d', '#f0fdf4'],
    ];
    [$l, $c, $bg] = $map[$s] ?? [ucfirst($s), '#6b6b6b', '#f4f4f2'];
    return '<span style="display:inline-block;padding:2px 10px;border-radius:20px;font-size:.72rem;font-weight:600;color:'.$c.';background:'.$bg.';">'.htmlspecialchars($l).'</span>';
}
?>
<?php require_once __DIR__ . '/../../includes/head.php'; ?>
<style>
.bf-layout{display:flex;min-height:100vh;}
.bf-main{flex:1;display:flex;flex-direction:column;min-width:0;margin-left:var(--sidebar-w);}
@media(max-width:991px){.bf-main{margin-left:0;}}
.bf-body{flex:1;padding:28px 24px;background:var(--surface);}
.kpi-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;margin-bottom:24px;}
.kpi-tile{background:#fff;border:1px solid var(--line);border-radius:12px;padding:18px;}
.kpi-val{font-size:1.3rem;font-weight:800;color:#0d0d0d;letter-spacing:-.04em;line-height:1.1;}
.kpi-lbl{font-size:.72rem;font-weight:600;color:var(--ink-3);text-transform:uppercase;letter-spacing:.05em;margin-top:4px;}
.bf-pf-card{background:#fff;border:1px solid var(--line);border-radius:12px;overflow:hidden;}
.bid-card{border-bottom:1px solid var(--line);padding:18px 20px;transition:background .12s;}
.bid-card:last-child{border-bottom:none;}
.bid-card:hover{background:#fafaf8;}
</style>

<div class="bf-layout">
<?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
<div class="bf-main">
<?php require_once __DIR__ . '/../../includes/topbar.php'; ?>
<div class="bf-body">

  <?php if ($msg): ?>
    <div class="alert alert-success py-2 px-3 mb-3" style="font-size:.85rem;">
      <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($msg) ?>
    </div>
  <?php endif; ?>

  <!-- KPI Row -->
  <div class="kpi-grid">
    <div class="kpi-tile">
      <div class="kpi-val"><?= $total_cnt ?></div>
      <div class="kpi-lbl">Total Received</div>
    </div>
    <div class="kpi-tile">
      <div class="kpi-val" style="color:#b45309;"><?= $new_cnt ?></div>
      <div class="kpi-lbl">Unread / New</div>
    </div>
    <div class="kpi-tile">
      <div class="kpi-val" style="color:#166534;"><?= $invite_cnt ?></div>
      <div class="kpi-lbl">Hire Invites</div>
    </div>
    <div class="kpi-tile">
      <div class="kpi-val" style="color:#1e40af;"><?= $quote_cnt ?></div>
      <div class="kpi-lbl">Quote Requests</div>
    </div>
  </div>

  <!-- Filter -->
  <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
    <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
      <input type="text" name="q" class="form-control form-control-sm"
             placeholder="Search bids…" value="<?= htmlspecialchars($q_filter) ?>" style="width:220px;">
      <select name="type" class="form-select form-select-sm" style="width:150px;">
        <option value="">All Types</option>
        <option value="invite" <?= $type_filter === 'invite' ? 'selected' : '' ?>>Hire Invites</option>
        <option value="quote"  <?= $type_filter === 'quote'  ? 'selected' : '' ?>>Quote Requests</option>
      </select>
      <button type="submit" class="btn btn-sm" style="background:#1e3a5f;color:#fff;border-radius:8px;">Filter</button>
      <?php if ($q_filter || $type_filter): ?>
        <a href="/pages/bids/" class="btn btn-sm btn-light" style="border-radius:8px;">Clear</a>
      <?php endif; ?>
    </form>
  </div>

  <!-- Bids List -->
  <?php if (!$all_bids): ?>
    <!-- No provider listing yet or no bids -->
    <div class="bf-pf-card">
      <div class="text-center py-5">
        <i class="bi bi-inbox" style="font-size:2.5rem;color:var(--line);display:block;margin-bottom:12px;"></i>
        <p style="color:var(--ink-3);margin-bottom:4px;font-weight:600;">No bids yet</p>
        <p style="color:var(--ink-4);font-size:.875rem;margin-bottom:20px;">
          When clients send you hire invitations or quote requests, they'll appear here.
        </p>
        <a href="/pages/account/" class="btn btn-sm" style="background:#1e3a5f;color:#fff;border-radius:8px;padding:6px 18px;font-weight:600;text-decoration:none;">
          <i class="bi bi-person-lines-fill me-1"></i>Complete Your Profile
        </a>
      </div>
    </div>
  <?php elseif (empty($bids)): ?>
    <div class="bf-pf-card">
      <div class="text-center py-5">
        <i class="bi bi-search" style="font-size:2rem;color:var(--line);display:block;margin-bottom:12px;"></i>
        <p style="color:var(--ink-3);">No bids match your filters.</p>
        <a href="/pages/bids/" class="btn btn-sm btn-light" style="border-radius:8px;">Clear filters</a>
      </div>
    </div>
  <?php else: ?>
    <div class="bf-pf-card">
      <?php foreach ($bids as $bid): ?>
      <div class="bid-card">
        <div class="d-flex align-items-start justify-content-between gap-3">
          <div style="flex:1;min-width:0;">
            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
              <?= bid_type_badge($bid['type']) ?>
              <?= bid_status_badge($bid['status'] ?? 'new') ?>
              <?php if ($bid['budget']): ?>
                <span style="font-size:.75rem;color:var(--ink-3);background:#f4f4f2;border-radius:20px;padding:2px 10px;font-weight:600;">
                  Budget: <?= htmlspecialchars($bid['budget']) ?>
                </span>
              <?php endif; ?>
            </div>
            <div style="font-weight:700;color:#0d0d0d;font-size:.95rem;margin-bottom:2px;">
              <?= htmlspecialchars($bid['subject'] ?: '(No subject)') ?>
            </div>
            <div style="font-size:.8rem;color:var(--ink-3);margin-bottom:8px;">
              From: <strong style="color:var(--ink-2);"><?= htmlspecialchars($bid['from_name'] ?? 'Anonymous') ?></strong>
              <?php if ($bid['location']): ?>
                &nbsp;·&nbsp;<i class="bi bi-geo-alt" style="color:var(--ink-4);"></i> <?= htmlspecialchars($bid['location']) ?>
              <?php endif; ?>
              <?php if ($bid['needed_by']): ?>
                &nbsp;·&nbsp;<i class="bi bi-calendar3" style="color:var(--ink-4);"></i> Needed by <?= htmlspecialchars($bid['needed_by']) ?>
              <?php endif; ?>
            </div>
            <?php if ($bid['message']): ?>
            <div style="font-size:.875rem;color:var(--ink-2);background:#f8f8f6;border-left:3px solid #1e3a5f;border-radius:0 6px 6px 0;padding:10px 14px;margin-bottom:0;max-width:720px;">
              <?= nl2br(htmlspecialchars(mb_substr($bid['message'], 0, 400))) ?><?= mb_strlen($bid['message']) > 400 ? '…' : '' ?>
            </div>
            <?php endif; ?>
          </div>
          <div style="flex-shrink:0;text-align:right;">
            <div style="font-size:.75rem;color:var(--ink-4);">
              <?= $bid['created_at'] ? date('d M Y', strtotime($bid['created_at'])) : '' ?>
            </div>
            <div style="margin-top:8px;">
              <a href="/pages/messages/" class="btn btn-xs btn-light" style="font-size:.75rem;padding:4px 10px;border-radius:6px;">
                <i class="bi bi-reply me-1"></i>Reply
              </a>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div></div></div>
<?php require_once __DIR__ . '/../../includes/footer-dashboard.php'; ?>
