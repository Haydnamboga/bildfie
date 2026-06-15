<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_login();
$page_title = 'Messages'; $sp = 'messages';
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<div class="bf-shell">
  <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
  <div class="bf-main">

    <?php $topbar_action='<button class="bf-topbar-new" style="border:none;cursor:pointer;"><i class="bi bi-pencil-square me-1"></i>New Message</button>'; include __DIR__ . '/../../includes/topbar.php'; ?>

    <div class="bf-body" style="padding:28px;">
      <div style="background:var(--white);border:1px solid var(--line);border-radius:14px;overflow:hidden;min-height:560px;">
        <div class="row g-0" style="min-height:560px;">

          <!-- thread list -->
          <div class="col-md-4" style="border-right:1px solid var(--line);">
            <div style="padding:14px 16px;border-bottom:1px solid var(--line);">
              <div class="bf-login-field" style="padding:0 12px;">
                <i class="bi bi-search"></i>
                <input type="text" placeholder="Search conversations…" style="padding:9px 0;font-size:13px;">
              </div>
            </div>
            <?php foreach ([
              ['AO','Amina Osei','Revit drawings for Block B?','2h ago',true,'#1e3a5f','#eaf0f6'],
              ['JM','John Mwangi','Rebar specs confirmed ✓','5h ago',false,'#166534','#f0fdf4'],
              ['PN','Peter Njoroge','Site access tomorrow at 7am','Yesterday',false,'#b45309','#fffbeb'],
              ['GW','Grace Wanjiku','Updated BOQ attached','2 days ago',false,'#1e40af','#eff6ff'],
            ] as $i => [$av,$n,$p,$t,$active,$col,$bg]): ?>
            <div class="d-flex gap-3 align-items-center" style="padding:13px 16px;border-bottom:1px solid var(--line-2);cursor:pointer;<?= $active?'background:#fcfdfe;border-left:2px solid #1e3a5f;':'border-left:2px solid transparent;' ?>">
              <div style="width:38px;height:38px;border-radius:50%;background:<?=$bg?>;color:<?=$col?>;font-size:12px;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><?=$av?></div>
              <div style="flex:1;min-width:0;">
                <div class="d-flex justify-content-between align-items-center">
                  <span style="font-size:13px;font-weight:<?=$active?'800':'700'?>;color:var(--ink);"><?=$n?></span>
                  <span style="font-size:10.5px;color:var(--ink-4);flex-shrink:0;"><?=$t?></span>
                </div>
                <div style="font-size:12px;color:var(--ink-3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?=$p?></div>
              </div>
              <?php if ($active): ?><span style="width:8px;height:8px;border-radius:50%;background:#c0392b;flex-shrink:0;"></span><?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>

          <!-- chat panel -->
          <div class="col-md-8 d-flex flex-column">
            <!-- chat header -->
            <div class="d-flex align-items-center gap-3" style="padding:13px 20px;border-bottom:1px solid var(--line);">
              <div style="width:36px;height:36px;border-radius:50%;background:#eaf0f6;color:#1e3a5f;font-size:12px;font-weight:800;display:flex;align-items:center;justify-content:center;">AO</div>
              <div style="flex:1;">
                <div style="font-size:13px;font-weight:800;color:var(--ink);">Amina Osei</div>
                <div style="font-size:11px;color:#22c55e;font-weight:600;"><span style="width:6px;height:6px;border-radius:50%;background:#22c55e;display:inline-block;margin-right:4px;"></span>Online</div>
              </div>
              <button class="btn btn-sm border-0 p-1" style="color:var(--ink-4);"><i class="bi bi-telephone"></i></button>
              <button class="btn btn-sm border-0 p-1" style="color:var(--ink-4);"><i class="bi bi-three-dots"></i></button>
            </div>

            <!-- messages -->
            <div class="flex-grow-1" style="padding:20px;overflow-y:auto;max-height:380px;background:var(--surface);">
              <div class="d-flex gap-2 mb-3">
                <div style="width:28px;height:28px;border-radius:50%;background:#eaf0f6;color:#1e3a5f;font-size:10px;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;">AO</div>
                <div style="background:var(--white);border:1px solid var(--line);border-radius:4px 12px 12px 12px;padding:10px 14px;font-size:13px;color:var(--ink-2);max-width:72%;">
                  Hi, can you share the Revit drawings for Block B? We need them for the structural check.
                  <div style="font-size:10px;color:var(--ink-4);margin-top:4px;">2:14 PM</div>
                </div>
              </div>
              <div class="d-flex justify-content-end mb-3">
                <div style="background:#1e3a5f;color:#fff;border-radius:12px 4px 12px 12px;padding:10px 14px;font-size:13px;max-width:72%;">
                  Hi Amina! Yes, I'll upload them to the project documents now.
                  <div style="font-size:10px;color:rgba(255,255,255,.6);margin-top:4px;">2:19 PM <i class="bi bi-check2-all ms-1"></i></div>
                </div>
              </div>
            </div>

            <!-- input -->
            <div style="padding:14px 20px;border-top:1px solid var(--line);">
              <div class="bf-login-field" style="padding:0 6px 0 14px;">
                <input type="text" placeholder="Type a message…" style="padding:11px 0;font-size:13px;">
                <button class="btn btn-sm border-0 p-1" style="color:var(--ink-4);"><i class="bi bi-paperclip"></i></button>
                <button style="background:#c0392b;color:#fff;border:none;border-radius:8px;width:34px;height:34px;flex-shrink:0;cursor:pointer;"><i class="bi bi-send"></i></button>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
