<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/projects.php';
require_login();
$uid = (int) current_user()['id'];

// edit mode when ?id= points to a project the member owns
$proj = isset($_GET['id']) ? project_get($_GET['id'], $uid) : null;
$editing = (bool) $proj;
$page_title = $editing ? 'Edit Project' : 'New Project';
$sp = 'projects';

$v  = fn($k, $d = '') => htmlspecialchars((string) ($proj[$k] ?? $d), ENT_QUOTES);
$pct = (int) ($proj['progress'] ?? 0);
$selTrades = $proj && !empty($proj['trades']) ? array_map('trim', explode(',', $proj['trades'])) : [];
$on = fn($k, $default = 1) => (int) ($proj[$k] ?? $default) ? 'checked' : '';
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<div class="bf-shell">
  <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
  <div class="bf-main">
    <div class="bf-body" style="padding:28px;">
      <div class="row justify-content-center">
        <div class="col-xl-10">

          <div class="d-flex align-items-end justify-content-between flex-wrap gap-2 mb-4">
            <div>
              <div style="font-size:10px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:#1e3a5f;margin-bottom:8px;"><span style="opacity:.4;font-weight:400;">—</span> <?= $editing ? 'Edit' : 'Create' ?></div>
              <h1 style="font-size:clamp(20px,3vw,26px);font-weight:800;color:var(--ink);margin:0 0 4px;letter-spacing:-.02em;"><?= $editing ? 'Edit project' : 'New project' ?></h1>
              <p style="font-size:13px;color:var(--ink-3);margin:0;">Set up the project, how you'll bill, the timeline, and how you'll work with your client.</p>
            </div>
            <a href="/pages/projects/index.php" class="bf-btn-outline" style="text-decoration:none;"><i class="bi bi-arrow-left me-1"></i>Back to projects</a>
          </div>

          <form action="/api/projects/create.php" method="POST">
            <?php if ($editing): ?><input type="hidden" name="id" value="<?= (int) $proj['id'] ?>"><?php endif; ?>
            <div class="row g-3">
              <div class="col-lg-8">

                <!-- Basics -->
                <div class="bf-pf-card">
                  <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-info-circle"></i> Project basics</div></div>
                  <div class="bf-pf-card-b">
                    <div class="mb-3">
                      <label class="bf-fl">Project name <span style="color:#c0392b;">*</span></label>
                      <input type="text" name="name" value="<?= $v('name') ?>" class="bf-fi" placeholder="e.g. Westlands Office Block" required>
                    </div>
                    <div class="row g-3 mb-3">
                      <div class="col-md-6"><label class="bf-fl">Customer / client name</label><input type="text" name="customer_name" value="<?= $v('customer_name') ?>" class="bf-fi" placeholder="e.g. Skyline Developers Ltd"></div>
                      <div class="col-md-6"><label class="bf-fl">Project type</label>
                        <select name="type" class="bf-fi">
                          <option value="">Select type</option>
                          <?php foreach (['Residential','Commercial','Industrial','Institutional','Infrastructure','Renovation','Fit-out','MEP','Other'] as $t): ?>
                          <option <?= ($proj['type'] ?? '')===$t?'selected':'' ?>><?= $t ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="col-md-6"><label class="bf-fl">Location</label><input type="text" name="location" value="<?= $v('location') ?>" class="bf-fi" placeholder="County / Town / Area"></div>
                    </div>
                    <div>
                      <label class="bf-fl">Project description</label>
                      <textarea name="description" rows="4" class="bf-fi" placeholder="Scope, objectives, key requirements…" style="resize:vertical;"><?= $v('description') ?></textarea>
                    </div>
                  </div>
                </div>

                <!-- Billing & budget -->
                <div class="bf-pf-card">
                  <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-cash-coin"></i> Billing &amp; budget</div></div>
                  <div class="bf-pf-card-b">
                    <label class="bf-fl">How will this project be billed?</label>
                    <div class="bf-bill-grid mb-3">
                      <?php
                        $billCur = $proj['billing_type'] ?? 'milestone';
                        $billIcons = ['milestone'=>'bi-flag','fixed'=>'bi-cash','hourly'=>'bi-clock-history','retainer'=>'bi-arrow-repeat'];
                        $billHints = ['milestone'=>'Pay as each stage completes','fixed'=>'One agreed price for the whole job','hourly'=>'Billed by time / rate','retainer'=>'A recurring monthly fee'];
                        foreach (project_billing_defs() as $bv => $bl): ?>
                      <label class="bf-bill <?= $billCur===$bv?'on':'' ?>">
                        <input type="radio" name="billing_type" value="<?= $bv ?>" <?= $billCur===$bv?'checked':'' ?>>
                        <i class="bi <?= $billIcons[$bv] ?>"></i>
                        <span class="t"><?= $bl ?></span>
                        <span class="h"><?= $billHints[$bv] ?></span>
                      </label>
                      <?php endforeach; ?>
                    </div>
                    <div class="row g-3">
                      <div class="col-md-4"><label class="bf-fl">Currency</label>
                        <select name="currency" class="bf-fi">
                          <?php foreach (['KES','USD','EUR','GBP','NGN','TZS','UGX'] as $cur): ?>
                          <option <?= ($proj['currency'] ?? 'KES')===$cur?'selected':'' ?>><?= $cur ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="col-md-4"><label class="bf-fl">Total budget</label><input type="number" name="budget" value="<?= $v('budget') ?>" min="0" step="any" class="bf-fi" placeholder="0"></div>
                      <div class="col-md-4"><label class="bf-fl">Payment terms</label><input type="text" name="payment_terms" value="<?= $v('payment_terms') ?>" class="bf-fi" placeholder="e.g. 30% deposit, rest per milestone"></div>
                    </div>
                  </div>
                </div>

                <!-- Collaboration settings -->
                <div class="bf-pf-card" style="margin-bottom:0;">
                  <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-people"></i> How you'll work with your client</div></div>
                  <div class="bf-pf-card-b" style="padding-top:8px;">
                    <p class="bf-modal-hint">These control what your client can see and do on this project.</p>
                    <?php foreach ([
                      ['client_can_comment','Let the client comment & message on the project',1],
                      ['client_can_view_budget','Share the budget &amp; spend with the client',1],
                      ['client_can_view_documents','Share documents &amp; drawings with the client',1],
                      ['require_milestone_approval','Require client approval before a milestone is paid',1],
                      ['use_escrow','Use bildfie escrow to protect payments',0],
                      ['notify_client_updates','Email the client when there are updates',1],
                    ] as [$k,$lbl,$def]): ?>
                    <label class="bf-toggle-row">
                      <input type="checkbox" name="<?= $k ?>" <?= $on($k,$def) ?>>
                      <span><?= $lbl ?></span>
                    </label>
                    <?php endforeach; ?>
                  </div>
                </div>

              </div>

              <!-- Right: status, timeline, progress -->
              <div class="col-lg-4">
                <div class="bf-pf-card">
                  <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-flag"></i> Status &amp; progress</div></div>
                  <div class="bf-pf-card-b">
                    <div class="bf-ring-wrap">
                      <svg viewBox="0 0 120 120" class="bf-ring">
                        <circle cx="60" cy="60" r="52" fill="none" stroke="var(--line-2)" stroke-width="12"></circle>
                        <circle id="ringFill" cx="60" cy="60" r="52" fill="none" stroke="#1e3a5f" stroke-width="12" stroke-linecap="round"
                                stroke-dasharray="326.7" stroke-dashoffset="<?= 326.7 - 326.7*$pct/100 ?>" transform="rotate(-90 60 60)"></circle>
                        <text id="ringText" x="60" y="67" text-anchor="middle" font-size="26" font-weight="800" fill="var(--ink)"><?= $pct ?>%</text>
                      </svg>
                    </div>
                    <label class="bf-fl">Progress</label>
                    <input type="range" id="progRange" name="progress" min="0" max="100" value="<?= $pct ?>" step="5" style="width:100%;accent-color:#1e3a5f;">
                    <div class="mt-3">
                      <label class="bf-fl">Status</label>
                      <select name="status" class="bf-fi">
                        <?php $curStatus = $proj ? project_status_key($proj['status']) : 'started'; foreach (project_status_defs() as $sv => [$sl]): ?>
                        <option value="<?= $sv ?>" <?= $curStatus===$sv?'selected':'' ?>><?= $sl ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="mt-3">
                      <label class="bf-fl">Priority</label>
                      <select name="priority" class="bf-fi">
                        <?php foreach (project_priority_defs() as $pv => [$pl]): ?>
                        <option value="<?= $pv ?>" <?= ($proj['priority'] ?? 'normal')===$pv?'selected':'' ?>><?= $pl ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                </div>

                <div class="bf-pf-card">
                  <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-calendar3"></i> Timeline</div></div>
                  <div class="bf-pf-card-b">
                    <div class="mb-3"><label class="bf-fl">Start date</label><input type="date" name="start_date" value="<?= $v('start_date') ?>" class="bf-fi"></div>
                    <div class="mb-3"><label class="bf-fl">Estimated completion</label><input type="date" name="est_completion" value="<?= $v('est_completion') ?>" class="bf-fi"></div>
                    <div><label class="bf-fl">Hard deadline</label><input type="date" name="deadline" value="<?= $v('deadline') ?>" class="bf-fi"></div>
                  </div>
                </div>

                <div class="bf-pf-card"<?= $editing ? ' style="margin-bottom:0;"' : '' ?>>
                  <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-eye"></i> Visibility</div></div>
                  <div class="bf-pf-card-b" style="padding-top:12px;">
                    <label class="bf-toggle-row"><input type="radio" name="visibility" value="private" <?= ($proj['visibility'] ?? 'private')==='private'?'checked':'' ?>><span>Private — invite only</span></label>
                    <label class="bf-toggle-row"><input type="radio" name="visibility" value="public" <?= ($proj['visibility'] ?? '')==='public'?'checked':'' ?>><span>Public — open for bids</span></label>
                  </div>
                </div>

                <?php if (!$editing): ?>
                <div class="bf-pf-card" style="margin-bottom:0;">
                  <div class="bf-pf-card-h"><div class="bf-pf-card-t"><i class="bi bi-list-check"></i> Task breakdown</div></div>
                  <div class="bf-pf-card-b" style="padding-top:12px;">
                    <label class="bf-toggle-row"><input type="checkbox" name="seed_tasks" checked><span>Start with the default construction breakdown — 12 sections (Site Survey, Foundation, Structure, Roofing…) with subtasks &amp; milestones you can edit.</span></label>
                  </div>
                </div>
                <?php endif; ?>
              </div>
            </div>

            <div class="d-flex align-items-center gap-3 mt-3">
              <button type="submit" class="bf-btn-accent" style="padding:12px 28px;"><i class="bi bi-check-lg me-1"></i><?= $editing ? 'Save changes' : 'Create project' ?></button>
              <a href="/pages/projects/index.php" style="font-size:13px;font-weight:600;color:var(--ink-3);text-decoration:none;">Cancel</a>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</div>
<script>
(function(){
  var r=document.getElementById('progRange'), fill=document.getElementById('ringFill'), txt=document.getElementById('ringText'), C=326.7;
  function paint(){ var p=+r.value; fill.setAttribute('stroke-dashoffset', (C - C*p/100).toFixed(1)); txt.textContent=p+'%'; fill.setAttribute('stroke', p>=80?'#16a34a':(p<25?'#f59e0b':'#1e3a5f')); }
  if(r){ r.addEventListener('input', paint); paint(); }
  // billing card selection
  document.querySelectorAll('.bf-bill input[type=radio]').forEach(function(inp){
    inp.addEventListener('change', function(){
      document.querySelectorAll('.bf-bill').forEach(function(c){ c.classList.remove('on'); });
      if(inp.checked) inp.closest('.bf-bill').classList.add('on');
    });
  });
})();
</script>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
