<?php
/**
 * Messages — internal messaging / conversations hub.
 * This page is a placeholder while the full messaging system is being built.
 * It still requires a logged-in session and renders within the dashboard layout.
 */
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/engage.php';

require_login();
$u   = current_user();
$uid = (int) $u['id'];

// Show recent engagements as a temporary stand-in for a real message thread
$engagements = provider_engagements_for_user($uid);

$sp           = 'messages';
$topbar_title = 'Messages';
$page_title   = 'Messages';
?>
<?php require_once __DIR__ . '/../../includes/head.php'; ?>
<style>
.bf-layout{display:flex;min-height:100vh;}
.bf-main{flex:1;display:flex;flex-direction:column;min-width:0;margin-left:var(--sidebar-w);}
@media(max-width:991px){.bf-main{margin-left:0;}}
.bf-body{flex:1;padding:28px 24px;background:var(--surface);}
.bf-pf-card{background:#fff;border:1px solid var(--line);border-radius:12px;overflow:hidden;}
.msg-row{display:flex;align-items:flex-start;gap:14px;padding:16px 20px;border-bottom:1px solid var(--line);transition:background .12s;}
.msg-row:last-child{border-bottom:none;}
.msg-row:hover{background:#fafaf8;}
.msg-avatar{width:40px;height:40px;border-radius:50%;background:#1e3a5f;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-weight:700;font-size:.95rem;color:#fff;}
.msg-body{flex:1;min-width:0;}
.msg-name{font-weight:700;font-size:.875rem;color:#0d0d0d;}
.msg-preview{font-size:.8rem;color:var(--ink-3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.msg-time{font-size:.72rem;color:var(--ink-4);flex-shrink:0;}
.coming-soon-badge{display:inline-flex;align-items:center;gap:6px;background:#fffbeb;border:1px solid #fde68a;border-radius:20px;padding:4px 12px;font-size:.75rem;font-weight:600;color:#b45309;margin-bottom:20px;}
</style>

<div class="bf-layout">
<?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
<div class="bf-main">
<?php require_once __DIR__ . '/../../includes/topbar.php'; ?>
<div class="bf-body">

  <!-- Coming Soon Notice -->
  <div class="coming-soon-badge"><i class="bi bi-tools"></i>Full messaging is coming soon</div>

  <div class="d-flex align-items-start gap-4 flex-lg-row flex-column" style="max-width:900px;">

    <!-- Left pane: contact list / recent bids -->
    <div style="width:100%;max-width:340px;flex-shrink:0;">
      <div class="bf-pf-card" style="height:calc(100vh - 180px);display:flex;flex-direction:column;">
        <div style="padding:14px 16px;border-bottom:1px solid var(--line);background:#fff;">
          <input type="text" class="form-control form-control-sm" placeholder="Search conversations…" disabled style="background:#f4f4f2;">
        </div>
        <div style="flex:1;overflow-y:auto;">
          <?php if (empty($engagements)): ?>
            <div class="text-center py-5">
              <i class="bi bi-chat-dots" style="font-size:2rem;color:var(--line);display:block;margin-bottom:10px;"></i>
              <p style="font-size:.8rem;color:var(--ink-4);">No conversations yet.</p>
            </div>
          <?php else: ?>
            <?php foreach (array_slice($engagements, 0, 30) as $eng): ?>
              <?php
              $initials = strtoupper(substr($eng['from_name'] ?? 'A', 0, 1));
              $preview  = mb_substr($eng['message'] ?? $eng['subject'] ?? '—', 0, 80);
              ?>
              <div class="msg-row">
                <div class="msg-avatar"><?= htmlspecialchars($initials) ?></div>
                <div class="msg-body">
                  <div class="msg-name"><?= htmlspecialchars($eng['from_name'] ?? 'Anonymous') ?></div>
                  <div class="msg-preview"><?= htmlspecialchars($preview) ?></div>
                </div>
                <div class="msg-time"><?= $eng['created_at'] ? date('d M', strtotime($eng['created_at'])) : '' ?></div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Right pane: empty thread / call to action -->
    <div style="flex:1;min-width:0;">
      <div class="bf-pf-card d-flex flex-column align-items-center justify-content-center text-center"
           style="height:calc(100vh - 180px);padding:40px;">
        <div style="width:72px;height:72px;background:#eaf0f6;border-radius:50%;display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
          <i class="bi bi-chat-square-dots" style="font-size:2rem;color:#1e3a5f;"></i>
        </div>
        <h5 style="font-weight:800;color:#0d0d0d;margin-bottom:8px;">Your messages will appear here</h5>
        <p style="font-size:.875rem;color:var(--ink-3);max-width:360px;margin-bottom:24px;">
          When you receive hire invitations or quote requests, you can reply to them directly in your conversations feed. Full messaging is launching soon.
        </p>
        <div class="d-flex gap-2 flex-wrap justify-content-center">
          <a href="/pages/bids/" class="btn btn-sm" style="background:#1e3a5f;color:#fff;border-radius:8px;padding:7px 18px;font-weight:600;text-decoration:none;">
            <i class="bi bi-inbox me-1"></i>View Bids Received
          </a>
          <a href="/pages/professionals/" class="btn btn-sm btn-light" style="border-radius:8px;padding:7px 18px;font-weight:600;text-decoration:none;">
            <i class="bi bi-search me-1"></i>Find Professionals
          </a>
        </div>
        <p style="font-size:.75rem;color:var(--ink-4);margin-top:28px;">
          Want to contact a professional? Visit their profile and click <strong>Request Quote</strong> or <strong>Send Invite</strong>.
        </p>
      </div>
    </div>

  </div>

</div></div></div>
<?php require_once __DIR__ . '/../../includes/footer-dashboard.php'; ?>
