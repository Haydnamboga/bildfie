<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
$page_title = 'Post a Project';
$nav = 'projects';
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>

<div style="background:var(--surface);border-bottom:1px solid var(--line);padding:32px 0 24px;">
  <div class="container" style="max-width:860px;">
    <div class="bf-section-eyebrow mb-2"><i class="bi bi-plus-circle" style="color:#c0392b;"></i> Post a Project</div>
    <h1 style="font-size:clamp(20px,3vw,28px);font-weight:800;color:var(--ink);margin:0 0 6px;">Tell us about your project</h1>
    <p style="font-size:13px;color:var(--ink-3);margin:0;">Verified professionals will submit proposals. Review, compare and hire — no middlemen.</p>

    <!-- Steps -->
    <div class="d-flex gap-0 mt-4" style="max-width:560px;">
      <?php foreach ([['01','Project details'],['02','Requirements'],['03','Budget & timeline'],['04','Review & publish']] as $i=>[$n,$l]): ?>
      <div style="flex:1;display:flex;align-items:center;gap:0;">
        <div style="display:flex;flex-direction:column;align-items:center;min-width:0;">
          <div style="width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;flex-shrink:0;
            background:<?= $i===0?'var(--ink)':'var(--white)' ?>;color:<?= $i===0?'#fff':'var(--ink-4)' ?>;border:2px solid <?= $i===0?'var(--ink)':'var(--line)' ?>;"><?= $n ?></div>
          <div style="font-size:9.5px;font-weight:<?= $i===0?'700':'500' ?>;color:<?= $i===0?'var(--ink)':'var(--ink-4)' ?>;margin-top:4px;text-align:center;"><?= $l ?></div>
        </div>
        <?php if ($i<3): ?><div style="flex:1;height:2px;background:var(--line);margin:0 4px;margin-bottom:16px;"></div><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="container" style="max-width:860px;padding-top:36px;padding-bottom:72px;">
  <form>
    <div class="row g-4">

      <!-- Main form -->
      <div class="col-lg-8">

        <!-- Step 1: Project details -->
        <div style="background:var(--white);border:1px solid var(--line);border-radius:16px;padding:28px;margin-bottom:20px;">
          <h3 style="font-size:15px;font-weight:800;color:var(--ink);margin-bottom:20px;display:flex;align-items:center;gap:8px;">
            <span style="width:24px;height:24px;border-radius:50%;background:#1e3a5f;color:#fff;font-size:11px;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;">1</span> Project details
          </h3>
          <div class="mb-3">
            <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:6px;">Project title <span style="color:#c0392b;">*</span></label>
            <input type="text" placeholder="e.g. 3-bedroom residential build in Kilimani" class="form-control" style="font-size:13px;border-color:var(--line);border-radius:8px;padding:10px 14px;">
            <div style="font-size:11px;color:var(--ink-4);margin-top:4px;">Be specific — clear titles attract better proposals.</div>
          </div>
          <div class="mb-3">
            <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:6px;">Project category <span style="color:#c0392b;">*</span></label>
            <select class="form-select" style="font-size:13px;border-color:var(--line);border-radius:8px;padding:10px 14px;">
              <option value="">Select a category</option>
              <?php foreach (['New Build — Residential','New Build — Commercial','Renovation / Refurbishment','Civil & Infrastructure','Fit-out & Interior','MEP Works','Structural Works','Roofing','Landscaping','Demolition','Other'] as $c): ?>
              <option><?= $c ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:6px;">Description <span style="color:#c0392b;">*</span></label>
            <textarea rows="5" placeholder="Describe the scope of work, site conditions, access, any existing structures, etc." class="form-control" style="font-size:13px;border-color:var(--line);border-radius:8px;padding:10px 14px;resize:vertical;"></textarea>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:6px;">County / Region <span style="color:#c0392b;">*</span></label>
              <select class="form-select" style="font-size:13px;border-color:var(--line);border-radius:8px;padding:10px 14px;">
                <option value="">Select county</option>
                <?php foreach (['Nairobi','Mombasa','Kisumu','Nakuru','Eldoret','Thika','Machakos','Kiambu','Kajiado','Nyeri','Meru','Kisii','Lagos, Nigeria','Accra, Ghana','Dubai, UAE'] as $c): ?>
                <option><?= $c ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:6px;">Site address / Area</label>
              <input type="text" placeholder="e.g. Kilimani, off Argwings Kodhek Rd" class="form-control" style="font-size:13px;border-color:var(--line);border-radius:8px;padding:10px 14px;">
            </div>
          </div>
        </div>

        <!-- Step 2: Requirements -->
        <div style="background:var(--white);border:1px solid var(--line);border-radius:16px;padding:28px;margin-bottom:20px;">
          <h3 style="font-size:15px;font-weight:800;color:var(--ink);margin-bottom:20px;display:flex;align-items:center;gap:8px;">
            <span style="width:24px;height:24px;border-radius:50%;background:var(--surface);color:var(--ink-3);font-size:11px;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;">2</span> Professionals required
          </h3>
          <div class="mb-3">
            <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:8px;">Trades / professions needed</label>
            <div class="d-flex flex-wrap gap-2">
              <?php foreach (['Architect','Structural Engineer','Civil Engineer','Quantity Surveyor','General Contractor','MEP Engineer','Interior Designer','Land Surveyor','Project Manager','Plumber','Electrician','Mason'] as $t): ?>
              <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer;">
                <input type="checkbox" style="accent-color:#1e3a5f;"> <?= $t ?>
              </label>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="mb-3">
            <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:6px;">Minimum accreditation required</label>
            <select class="form-select" style="font-size:13px;border-color:var(--line);border-radius:8px;padding:10px 14px;">
              <option>None — open to all verified professionals</option>
              <option>NCA registered (any grade)</option>
              <option>NCA Grade 1–3 (large works)</option>
              <option>CIOB / AAK member</option>
              <option>PE licensed</option>
            </select>
          </div>
          <div>
            <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:6px;">Attach documents (drawings, BOQ, brief)</label>
            <div style="border:2px dashed var(--line);border-radius:10px;padding:28px;text-align:center;cursor:pointer;">
              <i class="bi bi-cloud-upload" style="font-size:24px;color:var(--ink-4);display:block;margin-bottom:8px;"></i>
              <div style="font-size:13px;color:var(--ink-3);">Drag & drop files here or <span style="color:#1e3a5f;font-weight:700;">browse</span></div>
              <div style="font-size:11px;color:var(--ink-4);margin-top:4px;">PDF, DWG, XLS, DOC — max 50MB per file</div>
              <input type="file" multiple style="display:none;">
            </div>
          </div>
        </div>

        <!-- Step 3: Budget & timeline -->
        <div style="background:var(--white);border:1px solid var(--line);border-radius:16px;padding:28px;">
          <h3 style="font-size:15px;font-weight:800;color:var(--ink);margin-bottom:20px;display:flex;align-items:center;gap:8px;">
            <span style="width:24px;height:24px;border-radius:50%;background:var(--surface);color:var(--ink-3);font-size:11px;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;">3</span> Budget & timeline
          </h3>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:6px;">Budget range (KES) <span style="color:#c0392b;">*</span></label>
              <select class="form-select" style="font-size:13px;border-color:var(--line);border-radius:8px;padding:10px 14px;">
                <option>Under KES 500,000</option>
                <option>KES 500K – 2M</option>
                <option>KES 2M – 10M</option>
                <option>KES 10M – 50M</option>
                <option>KES 50M – 200M</option>
                <option>Over KES 200M</option>
                <option>Budget not disclosed</option>
              </select>
            </div>
            <div class="col-md-6">
              <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:6px;">Payment structure</label>
              <select class="form-select" style="font-size:13px;border-color:var(--line);border-radius:8px;padding:10px 14px;">
                <option>Milestone payments (recommended)</option>
                <option>Fixed lump sum</option>
                <option>Monthly retainer</option>
                <option>Escrow-managed</option>
              </select>
            </div>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:6px;">Proposed start date</label>
              <input type="date" class="form-control" style="font-size:13px;border-color:var(--line);border-radius:8px;padding:10px 14px;">
            </div>
            <div class="col-md-6">
              <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:6px;">Estimated duration</label>
              <select class="form-select" style="font-size:13px;border-color:var(--line);border-radius:8px;padding:10px 14px;">
                <option>Under 1 month</option>
                <option>1–3 months</option>
                <option>3–6 months</option>
                <option>6–12 months</option>
                <option>1–2 years</option>
                <option>Over 2 years</option>
              </select>
            </div>
          </div>
          <div>
            <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:6px;">Bid deadline <span style="color:#c0392b;">*</span></label>
            <input type="date" class="form-control" style="font-size:13px;border-color:var(--line);border-radius:8px;padding:10px 14px;max-width:260px;">
            <div style="font-size:11px;color:var(--ink-4);margin-top:4px;">Professionals can submit bids until this date.</div>
          </div>
        </div>

      </div><!-- /col-8 -->

      <!-- Sidebar -->
      <div class="col-lg-4">
        <!-- Visibility options -->
        <div style="background:var(--white);border:1px solid var(--line);border-radius:16px;padding:22px;margin-bottom:16px;">
          <div style="font-size:13px;font-weight:800;color:var(--ink);margin-bottom:14px;">Visibility & posting</div>
          <div class="mb-3">
            <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:6px;">Who can see this project?</label>
            <select class="form-select form-select-sm" style="font-size:12px;border-color:var(--line);">
              <option>All verified professionals</option>
              <option>Invited professionals only</option>
              <option>NCA registered only</option>
            </select>
          </div>
          <div>
            <label style="font-size:12px;font-weight:700;color:var(--ink);display:block;margin-bottom:8px;">Options</label>
            <?php foreach (['Feature this project (priority placement)','Enable bildfie escrow','Require NDA before viewing full brief'] as $opt): ?>
            <label style="display:flex;align-items:center;gap:8px;font-size:12px;cursor:pointer;margin-bottom:8px;">
              <input type="checkbox" style="accent-color:#1e3a5f;"> <?= $opt ?>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Tips -->
        <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:16px;padding:22px;margin-bottom:16px;">
          <div style="font-size:12px;font-weight:800;color:#0369a1;margin-bottom:10px;"><i class="bi bi-lightbulb me-2"></i>Tips for a great listing</div>
          <?php foreach (['Include drawings or a BOQ to get more accurate bids','Set a realistic timeline — rushed timelines reduce quality proposals','Enable escrow for peace of mind on payments','Respond quickly to professional questions'] as $tip): ?>
          <div style="font-size:11.5px;color:#0369a1;margin-bottom:7px;padding-left:12px;border-left:2px solid #7dd3fc;"><?= $tip ?></div>
          <?php endforeach; ?>
        </div>

        <!-- CTA -->
        <button type="submit" class="bf-btn-accent w-100" style="padding:14px;font-size:14px;justify-content:center;">
          <i class="bi bi-send me-2"></i>Publish project
        </button>
        <button type="button" class="bf-btn-outline w-100 mt-2" style="padding:13px;font-size:13px;justify-content:center;">Save as draft</button>
        <div style="font-size:11px;color:var(--ink-4);text-align:center;margin-top:10px;">Your project is reviewed within 2 hours before going live.</div>
      </div>

    </div>
  </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
