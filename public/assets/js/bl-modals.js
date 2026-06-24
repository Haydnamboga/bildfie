/*
 * BuildLink — Modals & Actions
 * Professional modal, invite modal, new project form
 */
'use strict';

let _currentPro = null;

/* ── PROFESSIONAL MODAL ─────────────────────────────────────────────── */
function openProfModal(id) {
  _currentPro = (BL.professionals || []).find(p => p.id === id);
  window._currentProfId = id;
  if (!_currentPro) return;
  const p = _currentPro;
  const modal = document.getElementById('profModal');
  if (!modal) return;

  const tags = Array.isArray(p.tags) ? p.tags : (p.tags || '').split(',');

  modal.querySelector('#modal-name').textContent      = `${p.name} — ${p.trade}`;
  modal.querySelector('#modal-avatar').src            = p.avatar || 'https://i.pravatar.cc/100?img=1';
  modal.querySelector('#modal-fullname').textContent  = p.name;
  modal.querySelector('#modal-title').textContent     = `${p.trade} · ${p.location}`;
  modal.querySelector('#modal-rating').textContent    = `★ ${p.rating} (${p.review_count} reviews)`;

  const badgeEl = modal.querySelector('#modal-badge');
  if (badgeEl) {
    badgeEl.textContent    = p.verification_badge || '';
    badgeEl.style.display  = p.verification_badge ? '' : 'none';
  }

  const set = (id, val) => { const el = modal.querySelector('#' + id); if (el) el.textContent = val; };
  set('modal-jobs',   p.jobs_done);
  set('modal-rat',    p.rating);
  set('modal-ontime', `${p.on_time_percent || 98}%`);
  set('modal-exp',    `${p.experience_years || '?'}yr`);
  set('modal-bio',    p.bio || 'Experienced professional with a proven track record.');
  set('modal-rate',   `${p.rate_currency || 'KES'} ${(p.hourly_rate || 0).toLocaleString()} / hr`);

  modal.querySelector('#modal-tags').innerHTML =
    tags.map(t => `<span class="bl-tag">${t.trim()}</span>`).join('');

  const availEl = modal.querySelector('#modal-avail');
  if (availEl) {
    availEl.textContent  = p.available ? '● Available Now' : '● Currently Busy';
    availEl.style.color  = p.available ? '#2E7D32' : '#9E9E9E';
  }

  const statsEl = modal.querySelector('#modal-stats');
  if (statsEl) {
    statsEl.innerHTML = [
      ['Jobs Done', p.jobs_done],
      ['Rating', p.rating],
      ['On-time', `${p.on_time_percent || 98}%`],
      ['Experience', `${p.experience_years || '?'} yr`],
    ].map(([lbl, val]) => `
      <div class="col-6">
        <div class="p-2 rounded-3 text-center" style="background:var(--bs-light)">
          <div class="fw-bold" style="font-size:15px;color:var(--bl-navy)">${val}</div>
          <div class="text-muted" style="font-size:9px;text-transform:uppercase;letter-spacing:.06em">${lbl}</div>
        </div>
      </div>`).join('');
  }

  new bootstrap.Modal(modal).show();
}

/* ── INVITE MODAL ───────────────────────────────────────────────────── */
function openInviteModal(proId) {
  const pro = (BL.professionals || []).find(p => p.id === proId);
  if (!pro) return showToast('Professional not found', 'error');
  const modal = document.getElementById('inviteModal');
  if (!modal) { showToast('Invite feature: open from dashboard', 'warning'); return; }
  const nameEl = modal.querySelector('#invite-pro-name');
  if (nameEl) nameEl.textContent = pro.name;
  new bootstrap.Modal(modal).show();
}

/* ── SUBMIT INVITE ──────────────────────────────────────────────────── */
function submitInvite() {
  const project = document.getElementById('invite-project-select')?.value;
  const role    = (document.getElementById('invite-role-select') || document.getElementById('invite-role'))?.value;
  if (!project || !role) { showToast('Please select project and role', 'warning'); return; }
  showToast(`Invitation sent to ${_currentPro?.name}!`);
  bootstrap.Modal.getInstance(document.getElementById('inviteModal'))?.hide();
}

/* ── NEW PROJECT ────────────────────────────────────────────────────── */
async function submitNewProject() {
  const val = id => (document.getElementById(id)?.value || '').trim();
  const name = val('np-name') || val('new-proj-name');
  if (!name) { showToast('Please enter a project name', 'warning'); return; }

  const type        = val('np-type')       || val('new-proj-type')     || 'Residential';
  const location    = val('np-location')   || val('new-proj-location') || 'TBD';
  const description = val('np-desc');
  const due_date    = val('np-due') || null;
  const budgetMin   = Number(val('np-budget-min') || val('new-proj-budget') || 0);
  const budgetMax   = Number(val('np-budget-max') || budgetMin);
  const budgetStr   = budgetMin ? `KES ${budgetMin.toLocaleString()}–${budgetMax.toLocaleString()}` : 'TBD';

  const btn = document.querySelector('#newProjectModal .btn-primary');
  if (btn) { btn.disabled = true; btn.textContent = 'Creating…'; }

  const result = await window.BLApi?.createProject({
    name, type, description, location,
    budget_min: budgetMin, budget_max: budgetMax, due_date,
  });

  if (btn) { btn.disabled = false; btn.textContent = 'Create Project'; }

  if (!result) {
    showToast('Could not create project — please log in and try again', 'error');
    return;
  }

  const newProj = {
    id: result.id, name, type, status: 'planning', progress: 0,
    budget: budgetStr, spent: 'KES 0', contractor: 'Pending Award',
    phase: 'Planning', location, due_date: due_date || 'TBD',
    thumb_url: `https://picsum.photos/seed/${result.id}/300/200`,
  };

  BL.userProjects.unshift(newProj);
  renderDashProjects();
  showToast(`Project "${name}" created!`);
  bootstrap.Modal.getInstance(document.getElementById('newProjectModal'))?.hide();

  ['np-name', 'new-proj-name', 'np-desc', 'np-location', 'new-proj-location',
   'np-budget-min', 'np-budget-max', 'new-proj-budget', 'np-due'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });
}
