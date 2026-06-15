<?php
// Site-wide engagement modals: Request a Quote, Leave a Review, Hire / Invite.
// Opened via [data-engage="quote|review|hire"] with data-ctx="Label" and data-pid="<provider id>".
// Submits POST to /api/engage.php. All engagement happens on bildfie — no contact details are exchanged.
require_once __DIR__ . '/../../config/projects.php';
$bfMyProjects  = (function_exists('is_logged_in') && is_logged_in()) ? user_projects((int) current_user()['id']) : [];
$bfProjOptions = '';
foreach ($bfMyProjects as $bp) { $bfProjOptions .= '<option value="' . (int) $bp['id'] . '">' . htmlspecialchars($bp['name']) . '</option>'; }
?>
<!-- ═══ Request a Quote ═══ -->
<div class="modal fade" id="quoteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bf-modal">
      <div class="bf-modal-head">
        <div>
          <div class="bf-modal-title"><i class="bi bi-receipt me-2" style="color:#1e3a5f;"></i>Request a quote</div>
          <div class="bf-modal-sub">From <b class="js-ctx">this provider</b></div>
        </div>
        <button type="button" class="bf-modal-x" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
      </div>
      <form class="js-engage-form">
        <input type="hidden" name="type" value="quote">
        <input type="hidden" name="provider_id" value="">
        <div class="bf-modal-body">
          <div class="js-engage-err" style="display:none;background:#fef2f2;border:1px solid #f3c9c2;color:#c0392b;border-radius:9px;padding:9px 12px;font-size:12.5px;margin-bottom:12px;"></div>
          <label class="bf-f-lbl">Link to a project <span style="font-weight:400;color:var(--ink-4);">(optional)</span></label>
          <select class="bf-f-input" name="project_id">
            <option value="">— Not linked to a project —</option>
            <?= $bfProjOptions ?>
          </select>
          <label class="bf-f-lbl mt-2">What do you need?</label>
          <textarea class="bf-f-input" name="message" rows="3" placeholder="Describe the work, materials or service you need a price for…" required></textarea>
          <div class="row g-2 mt-1">
            <div class="col-6">
              <label class="bf-f-lbl">Location</label>
              <input class="bf-f-input" name="location" placeholder="County / town">
            </div>
            <div class="col-6">
              <label class="bf-f-lbl">Budget (optional)</label>
              <input class="bf-f-input" name="budget" placeholder="KES">
            </div>
            <div class="col-12">
              <label class="bf-f-lbl">Needed by</label>
              <select class="bf-f-input" name="needed_by"><option>As soon as possible</option><option>Within 1 week</option><option>Within 1 month</option><option>Flexible</option></select>
            </div>
          </div>
        </div>
        <div class="bf-modal-foot">
          <button type="button" class="bf-btn-ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="bf-btn-navy"><i class="bi bi-send me-1"></i>Send request</button>
        </div>
      </form>
      <div class="bf-modal-success js-engage-success">
        <div class="bf-modal-success-ic"><i class="bi bi-check-lg"></i></div>
        <div class="bf-modal-success-t">Quote request sent</div>
        <div class="bf-modal-success-s">You'll get a notification on bildfie when they respond. Keep all conversation here so it's protected.</div>
        <button type="button" class="bf-btn-navy" data-bs-dismiss="modal">Done</button>
      </div>
    </div>
  </div>
</div>

<!-- ═══ Leave a Review ═══ -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bf-modal">
      <div class="bf-modal-head">
        <div>
          <div class="bf-modal-title"><i class="bi bi-star me-2" style="color:#f59e0b;"></i>Leave a review</div>
          <div class="bf-modal-sub">For <b class="js-ctx">this provider</b></div>
        </div>
        <button type="button" class="bf-modal-x" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
      </div>
      <form class="js-engage-form">
        <input type="hidden" name="type" value="review">
        <input type="hidden" name="provider_id" value="">
        <div class="bf-modal-body">
          <div class="js-engage-err" style="display:none;background:#fef2f2;border:1px solid #f3c9c2;color:#c0392b;border-radius:9px;padding:9px 12px;font-size:12.5px;margin-bottom:12px;"></div>
          <label class="bf-f-lbl">Your rating</label>
          <div class="bf-stars" data-rating="0">
            <?php for ($i=1;$i<=5;$i++): ?><i class="bi bi-star" data-v="<?= $i ?>"></i><?php endfor; ?>
          </div>
          <label class="bf-f-lbl mt-3">Which project was this for?</label>
          <input class="bf-f-input" name="project" placeholder="e.g. Westlands Office Block">
          <label class="bf-f-lbl mt-2">Your review</label>
          <textarea class="bf-f-input" name="message" rows="4" placeholder="Share details of your experience — quality, communication, timeliness…" required></textarea>
        </div>
        <div class="bf-modal-foot">
          <button type="button" class="bf-btn-ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="bf-btn-navy"><i class="bi bi-check-lg me-1"></i>Post review</button>
        </div>
      </form>
      <div class="bf-modal-success js-engage-success">
        <div class="bf-modal-success-ic"><i class="bi bi-check-lg"></i></div>
        <div class="bf-modal-success-t">Thanks for your review</div>
        <div class="bf-modal-success-s">Your feedback helps the bildfie community hire with confidence — it's now on the profile.</div>
        <button type="button" class="bf-btn-navy" data-bs-dismiss="modal">Done</button>
      </div>
    </div>
  </div>
</div>

<!-- ═══ Hire / Invite ═══ -->
<div class="modal fade" id="hireModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bf-modal">
      <div class="bf-modal-head">
        <div>
          <div class="bf-modal-title"><i class="bi bi-person-plus me-2" style="color:#1e3a5f;"></i>Hire / invite</div>
          <div class="bf-modal-sub">Invite <b class="js-ctx">this professional</b> to a project</div>
        </div>
        <button type="button" class="bf-modal-x" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
      </div>
      <form class="js-engage-form">
        <input type="hidden" name="type" value="invite">
        <input type="hidden" name="provider_id" value="">
        <div class="bf-modal-body">
          <div class="js-engage-err" style="display:none;background:#fef2f2;border:1px solid #f3c9c2;color:#c0392b;border-radius:9px;padding:9px 12px;font-size:12.5px;margin-bottom:12px;"></div>
          <label class="bf-f-lbl">Link to one of your projects</label>
          <?php if ($bfMyProjects): ?>
          <select class="bf-f-input" name="project_id">
            <option value="">— Select a project —</option>
            <?= $bfProjOptions ?>
          </select>
          <?php else: ?>
          <select class="bf-f-input" name="project_id" disabled><option>No projects yet</option></select>
          <div style="font-size:11px;color:var(--ink-4);margin-top:4px;">Create a project first under <a href="/pages/projects/new.php" style="color:#1e3a5f;font-weight:700;">Projects → New project</a>, then invite them to it.</div>
          <?php endif; ?>
          <label class="bf-f-lbl mt-2">Invite as</label>
          <select class="bf-f-input" name="role"><option>Lead Contractor</option><option>Sub-Contractor</option><option>Consultant</option><option>Supplier</option><option>Site Supervisor</option><option>Engineer</option><option>Foreman</option></select>
          <label class="bf-f-lbl mt-2">Message (optional)</label>
          <textarea class="bf-f-input" name="message" rows="3" placeholder="Briefly describe the scope and timeline…"></textarea>
        </div>
        <div class="bf-modal-foot">
          <button type="button" class="bf-btn-ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="bf-btn-navy"><i class="bi bi-send me-1"></i>Send invite</button>
        </div>
      </form>
      <div class="bf-modal-success js-engage-success">
        <div class="bf-modal-success-ic"><i class="bi bi-check-lg"></i></div>
        <div class="bf-modal-success-t">Invite sent</div>
        <div class="bf-modal-success-s">We've notified the professional. Track their response under Messages and Notifications — everything stays on bildfie.</div>
        <button type="button" class="bf-btn-navy" data-bs-dismiss="modal">Done</button>
      </div>
    </div>
  </div>
</div>
