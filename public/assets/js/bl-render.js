/*
 * BuildLink — Render Functions
 * All card/list rendering: professionals, materials, bids, projects,
 * equipment, transport, and dashboard widgets.
 */
'use strict';

/* ── PROFESSIONALS GRID ─────────────────────────────────────────────── */
function renderProfessionals(container, data, limit = 8) {
  data = (data || BL.professionals).slice(0, limit);
  if (!data.length) {
    container.innerHTML = `<div class="col-12 text-center py-5 text-muted">
      <i class="bi bi-people" style="font-size:2.5rem;opacity:.25"></i>
      <div class="mt-2 fw-bold">No professionals listed yet</div>
      <div class="small mt-1"><a href="/register?role=professional">Join as a professional</a> to be the first listed here.</div>
    </div>`;
    return;
  }
  container.innerHTML = data.map(p => {
    const tags = Array.isArray(p.tags) ? p.tags
      : (typeof p.tags === 'string' ? p.tags.split(',') : []);
    const badgeHtml = p.verification_badge
      ? `<span class="bl-pav-pill ms-auto" style="background:${
          p.verification_badge === 'Elite Pro' ? 'var(--bl-navy)' : 'var(--bl-accent-light)'
        };color:${
          p.verification_badge === 'Elite Pro' ? '#fff' : 'var(--bl-accent)'
        }">${p.verification_badge}</span>` : '';
    return `<div class="col-sm-6 col-md-4 col-lg-3">
      <div class="bl-pro-card bl-rv" onclick="openProfModal(${p.id})">
        <div class="bl-pc-top"></div>
        <div class="p-3">
          <div class="d-flex align-items-start justify-content-between mb-2">
            <div class="bl-pav position-relative">
              <img src="${p.avatar || 'https://i.pravatar.cc/100?img=1'}" alt="${p.name}" loading="lazy">
              <div class="bl-pavb" style="background:${p.available ? '#4CAF50' : '#9E9E9E'}">
                <svg viewBox="0 0 24 24" width="6" height="6" fill="none" stroke="#fff" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
              </div>
            </div>
            ${badgeHtml}
          </div>
          ${p.verified ? `<div class="bl-vbadge"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg><span>ID &amp; Licence Verified</span></div>` : ''}
          <div class="fw-bold" style="font-size:13px;color:var(--bl-navy)">${p.name}</div>
          <div class="text-muted mb-2" style="font-size:10px">${p.trade}</div>
          <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
            <span style="font-size:11px;font-weight:700;color:var(--bl-accent)">&#9733; ${p.rating}</span>
            <span class="text-muted" style="font-size:10px">(${p.review_count})</span>
            <span class="text-muted" style="font-size:10px">&middot; ${p.jobs_done} jobs</span>
          </div>
          <div class="d-flex flex-wrap gap-1 mb-2">${tags.slice(0, 3).map(t => `<span class="bl-tag">${t}</span>`).join('')}</div>
          <div class="text-muted mb-3" style="font-size:10px">${p.location}</div>
          <div class="d-flex justify-content-between align-items-center pt-2" style="border-top:1px solid var(--bl-rule)">
            <div>
              <span class="fw-bold" style="font-size:14px">${p.rate_currency || 'KES'} ${(p.hourly_rate || 0).toLocaleString()}</span>
              <small class="text-muted">/hr</small>
            </div>
            <div class="d-flex gap-1">
              <button class="btn btn-outline-secondary btn-sm-xs" onclick="event.stopPropagation();showToast('Saving profile…')">Save</button>
              <button class="btn btn-dark btn-sm-xs" onclick="event.stopPropagation();showToast('Opening message…')">Message</button>
            </div>
          </div>
        </div>
      </div>
    </div>`;
  }).join('');
  initScrollReveal();
}

/* ── MATERIALS GRID ─────────────────────────────────────────────────── */
function renderMaterials(container, data, limit = 9) {
  data = (data || BL.materials).slice(0, limit);
  if (!data.length) {
    container.innerHTML = `<div class="col-12 text-center py-5 text-muted">
      <i class="bi bi-box-seam" style="font-size:2.5rem;opacity:.25"></i>
      <div class="mt-2 fw-bold">No materials listed yet</div>
      <div class="small mt-1">Suppliers can list materials after registering.</div>
    </div>`;
    return;
  }
  const stockCls = { in: 'bl-stock-in', low: 'bl-stock-low', out: 'bl-stock-out' };
  const stockLbl = { in: 'In Stock', low: 'Low Stock', out: 'Out of Stock' };
  const badgeCls = { bulk: 'bl-badge-bulk', new: 'bl-badge-new', sale: 'bl-badge-sale' };
  container.innerHTML = data.map(m => `
    <div class="col-sm-6 col-md-4">
      <div class="bl-mat-card bl-rv" onclick="showToast('Opening ${m.name}…')">
        <div class="bl-mat-img">
          <img src="${m.image_url}" alt="${m.name}" loading="lazy">
          <span class="bl-mat-stock ${stockCls[m.stock_status] || ''}">${stockLbl[m.stock_status] || ''}</span>
          ${m.badge ? `<span class="bl-mat-badge ${badgeCls[m.badge] || ''}">${m.badge}</span>` : ''}
        </div>
        <div class="p-3 d-flex flex-column flex-grow-1">
          <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--bl-green)" class="mb-1">${m.category}</div>
          <div class="fw-bold mb-1" style="font-size:13px;line-height:1.3">${m.name}</div>
          <div class="text-muted mb-2" style="font-size:10px;line-height:1.4">${m.specification}</div>
          <div class="d-flex align-items-center gap-1 mb-2" style="font-size:11px">
            <div class="d-flex align-items-center justify-content-center fw-bold text-white rounded"
              style="width:22px;height:22px;background:var(--bl-green);font-size:9px;flex-shrink:0">${m.supplier_name.charAt(0)}</div>
            <span style="font-size:10px;color:var(--bl-muted)">${m.supplier_name}</span>
            ${m.supplier_verified ? '<span style="color:var(--bl-green);font-size:10px">&#10003;</span>' : ''}
            <span class="ms-auto" style="font-size:10px">${m.supplier_rating || ''}</span>
          </div>
          <div class="d-flex align-items-baseline gap-2 mb-2">
            <span class="bl-mat-price">KES ${m.price.toLocaleString()}</span>
            <span class="text-muted" style="font-size:10px">${m.unit}</span>
          </div>
          <div class="d-flex justify-content-between align-items-center mt-auto pt-2" style="border-top:1px solid var(--bl-rule)">
            <span class="text-muted" style="font-size:10px">${m.location || ''} &middot; Min ${m.min_order}</span>
            <div class="d-flex gap-1">
              <button class="btn btn-outline-secondary btn-sm-xs" onclick="event.stopPropagation();showToast('Added to compare')">Compare</button>
              <button class="btn btn-dark btn-sm-xs" onclick="event.stopPropagation();showToast('Added to cart ✓')">Order</button>
            </div>
          </div>
        </div>
      </div>
    </div>`).join('');
  initScrollReveal();
}

/* ── BIDS GRID ──────────────────────────────────────────────────────── */
function renderBids(container, data) {
  data = data || BL.bids;
  if (!data.length) {
    container.innerHTML = `<div class="col-12 text-center py-5 text-muted">
      <i class="bi bi-megaphone" style="font-size:2.5rem;opacity:.25"></i>
      <div class="mt-2 fw-bold">No open bids right now</div>
      <div class="small mt-1">Post a project to invite professionals to bid.</div>
    </div>`;
    return;
  }
  const urgCls = { hot: 'bl-bid-hot', new: 'bl-bid-new', warm: 'bl-bid-warm' };
  const urgLbl = { hot: 'Hot', new: 'New', warm: 'Closing' };
  container.innerHTML = data.map(b => `
    <div class="col-sm-6 col-md-4">
      <div class="bl-bid-card bl-rv" onclick="showToast('Opening bid: ${b.title.substring(0, 30)}…')">
        <span class="bl-bid-urgency ${urgCls[b.urgency] || ''}">${urgLbl[b.urgency] || 'New'}</span>
        <div class="fw-bold mb-1" style="font-size:13px;padding-right:60px;line-height:1.35">${b.title}</div>
        <div class="text-muted mb-2" style="font-size:10px">${b.owner_name} &middot; ${b.location}</div>
        <div class="row g-1 mb-2">
          <div class="col-6"><div class="rounded p-2" style="background:var(--bl-bg)">
            <div style="font-size:9px;color:var(--bl-faint);text-transform:uppercase;letter-spacing:.06em">Budget</div>
            <div class="fw-bold" style="font-size:11px">${fmtKES(b.budget_min)}&ndash;${fmtKES(b.budget_max)}</div>
          </div></div>
          <div class="col-6"><div class="rounded p-2" style="background:var(--bl-bg)">
            <div style="font-size:9px;color:var(--bl-faint);text-transform:uppercase;letter-spacing:.06em">Deadline</div>
            <div class="fw-bold" style="font-size:11px">${b.deadline_days} days</div>
          </div></div>
        </div>
        <div class="d-flex flex-wrap gap-1 mb-2">${(b.trades || []).map(t => `<span class="bl-bid-trade">${t}</span>`).join('')}</div>
        <div class="d-flex justify-content-between align-items-center pt-2" style="border-top:1px solid var(--bl-rule)">
          <span style="font-size:10px;color:var(--bl-muted)"><strong>${b.applications_count}</strong> bids placed</span>
          <button class="btn btn-dark btn-sm-xs" onclick="event.stopPropagation();showToast('Opening bid form…')">Place Bid</button>
        </div>
      </div>
    </div>`).join('');
  initScrollReveal();
}

/* ── PROJECTS GRID ──────────────────────────────────────────────────── */
function renderProjects(container, data) {
  data = data || BL.projects;
  if (!data.length) {
    container.innerHTML = `<div class="col-12 text-center py-5 text-muted">
      <i class="bi bi-building" style="font-size:2.5rem;opacity:.25"></i>
      <div class="mt-2 fw-bold">No projects showcased yet</div>
      <div class="small mt-1">Completed projects from your dashboard will appear here.</div>
    </div>`;
    return;
  }
  container.innerHTML = data.map(p => {
    const tags = Array.isArray(p.tags) ? p.tags
      : (typeof p.tags === 'string' ? JSON.parse(p.tags || '[]') : []);
    const team = Array.isArray(p.team) ? p.team : [];
    return `<div class="col-sm-6 col-md-4 col-lg-3">
      <div class="bl-proj-card bl-rv" onclick="showToast('Opening: ${p.name}')">
        <div class="bl-proj-thumb">
          <img src="${p.thumb_url}" alt="${p.name}" loading="lazy">
          <span class="position-absolute bottom-0 end-0 m-2 badge"
            style="background:rgba(0,0,0,.55);backdrop-filter:blur(4px)">${p.status}</span>
        </div>
        <div class="p-3">
          <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.09em;color:var(--bl-accent)" class="mb-1">${p.type}</div>
          <div class="fw-bold mb-1" style="font-size:13px;line-height:1.35">${p.name}</div>
          <div class="text-muted mb-2" style="font-size:10px">${p.location}</div>
          <div class="d-flex gap-3 flex-wrap">
            <div style="font-size:10px;color:var(--bl-muted)">Value <strong style="color:var(--bl-navy)">${p.value || ''}</strong></div>
            <div style="font-size:10px;color:var(--bl-muted)">Duration <strong style="color:var(--bl-navy)">${p.duration || ''}</strong></div>
          </div>
        </div>
        <div class="d-flex justify-content-between align-items-center px-3 pb-3 pt-0"
          style="border-top:1px solid var(--bl-rule);padding-top:8px!important">
          <div class="d-flex">${team.slice(0, 4).map(av => `<div class="bl-proj-team-av"><img src="${av}" loading="lazy"></div>`).join('')}</div>
          <span class="text-muted" style="font-size:10px">${tags[0] || ''}</span>
        </div>
      </div>
    </div>`;
  }).join('');
  initScrollReveal();
}

/* ── EQUIPMENT CARDS ────────────────────────────────────────────────── */
function renderEquipment(container, data) {
  data = data || BL.equipment;
  if (!data.length) {
    container.innerHTML = `<div class="col-12 text-center py-5 text-muted">
      <i class="bi bi-truck" style="font-size:2.5rem;opacity:.25"></i>
      <div class="mt-2 fw-bold">No equipment listed yet</div>
      <div class="small mt-1">Equipment owners can list their machinery after registering.</div>
    </div>`;
    return;
  }
  container.innerHTML = data.map(e => {
    let specs = {};
    try { specs = typeof e.specs === 'string' ? JSON.parse(e.specs) : (e.specs || {}); } catch {}
    return `<div class="bl-eq-card bl-rv">
      <div class="bl-eq-accent"></div>
      <div class="p-3">
        <div class="d-flex align-items-center gap-3 mb-3">
          <div class="rounded-3 d-flex align-items-center justify-content-center"
            style="width:52px;height:52px;border:1px solid var(--bl-rule);font-size:24px;flex-shrink:0">${e.icon || '<i class="bi bi-building-fill-gear"></i>'}</div>
          <div>
            <div class="fw-bold" style="font-size:14px">${e.name}</div>
            <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.07em">${e.category}</div>
          </div>
        </div>
        <div class="row g-1 mb-3">
          ${Object.entries(specs).map(([k, v]) => `
            <div class="col-6"><div class="bl-eq-spec">
              <div style="font-size:9px;color:var(--bl-faint);text-transform:uppercase;letter-spacing:.07em">${k}</div>
              <div class="fw-bold" style="font-size:11px">${v}</div>
            </div></div>`).join('')}
        </div>
        <div class="bl-eq-rates mb-3">
          <div class="d-flex justify-content-between" style="font-size:11px;margin-bottom:3px"><span class="text-muted">Hourly</span><span class="fw-bold">KES ${e.hourly_rate.toLocaleString()}</span></div>
          <div class="d-flex justify-content-between" style="font-size:11px;margin-bottom:3px"><span class="text-muted">Daily</span><span class="fw-bold">KES ${e.daily_rate.toLocaleString()}</span></div>
          <div class="d-flex justify-content-between" style="font-size:11px"><span class="text-muted">Weekly</span><span class="fw-bold">KES ${e.weekly_rate.toLocaleString()}</span></div>
        </div>
        <div class="d-flex align-items-center gap-2 mb-3">
          <div class="rounded-circle" style="width:6px;height:6px;background:${e.available ? '#4CAF50' : '#FF9800'};animation:bl-blink 2s infinite"></div>
          <span class="text-muted" style="font-size:10px">${e.available ? 'Available Now' : 'Currently Hired'}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center pt-2" style="border-top:1px solid var(--bl-rule)">
          <span class="text-muted" style="font-size:10px"><i class="bi bi-star-fill text-warning"></i> ${e.rating} &middot; ${e.review_count} reviews</span>
          <div class="d-flex gap-1">
            <button class="btn btn-outline-secondary btn-sm-xs" onclick="showToast('Sending RFQ…')">RFQ</button>
            <button class="btn btn-primary btn-sm-xs" onclick="showToast('Booking ${e.name}…')">Book</button>
          </div>
        </div>
      </div>
    </div>`;
  }).join('');
  initScrollReveal();
}

/* ── TRANSPORT CARDS ────────────────────────────────────────────────── */
function renderTransport(container, data) {
  data = data || BL.transport;
  if (!data.length) {
    container.innerHTML = `<div class="col-12 text-center py-5 text-muted">
      <i class="bi bi-signpost-2" style="font-size:2.5rem;opacity:.25"></i>
      <div class="mt-2 fw-bold">No transport providers listed yet</div>
      <div class="small mt-1">Logistics companies can list their services after registering.</div>
    </div>`;
    return;
  }
  const modeLabel = { available: 'Available', contract: 'Contract', tripbased: 'Trip Based' };
  const modeColor = { available: '#E8F5E9,#2E7D32', contract: '#E3F2FD,#1565C0', tripbased: '#FEF0EB,#C43100' };
  container.innerHTML = data.map(t => `
    <div class="bl-trc bl-rv">
      <div class="d-flex gap-2 align-items-start mb-2">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
          style="width:42px;height:42px;font-size:18px;background:var(--bl-bg)">${t.icon}</div>
        <div>
          <div class="fw-bold" style="font-size:12px">${t.name}</div>
          <div class="text-muted" style="font-size:10px">${t.type}</div>
          <div class="fw-bold" style="font-size:13px;color:var(--bl-accent);margin-top:2px">${t.rate_text}</div>
        </div>
      </div>
      <div class="d-flex flex-wrap gap-1 mb-2">
        ${(Array.isArray(t.modes) ? t.modes : JSON.parse(t.modes || '[]')).map(m => {
          const [bg, col] = (modeColor[m] || '#F0F0F0,#6E6E78').split(',');
          return `<span style="font-size:9px;padding:2px 7px;border-radius:100px;font-weight:700;background:${bg};color:${col}">${modeLabel[m] || m}</span>`;
        }).join('')}
      </div>
      <div class="d-flex justify-content-between align-items-center pt-2" style="border-top:1px solid var(--bl-rule)">
        <div>
          <span class="text-muted" style="font-size:10px"><i class="bi bi-star-fill text-warning"></i> ${t.rating} (${t.review_count})</span>
          <span class="text-muted" style="font-size:10px"> &middot; ${t.location} &middot; ${t.capacity}</span>
        </div>
        <button class="btn btn-primary btn-sm-xs" onclick="showToast('Contacting ${t.name}…')">Contact</button>
      </div>
    </div>`).join('');
  initScrollReveal();
}

/* ── DASHBOARD: PROJECT LIST ─────────────────────────────────────────── */
function renderDashProjects(data) {
  const el = document.getElementById('dash-projects-list');
  if (!el) return;
  data = data || BL.userProjects;
  const statusBadge = {
    ongoing:   '<span class="badge" style="background:#FFF3E0;color:#BF6900;font-size:9px">ONGOING</span>',
    planning:  '<span class="badge" style="background:#E3F2FD;color:#0D47A1;font-size:9px">PLANNING</span>',
    completed: '<span class="badge" style="background:#E8F5E9;color:#1B5E20;font-size:9px">DONE</span>'
  };
  const barColor = { ongoing: '#BF6900', planning: '#1565C0', completed: '#1B5E20' };
  el.innerHTML = data.map(p => `
    <div class="bl-proj-row" onclick="showToast('Opening project: ${p.name}')">
      <img src="${p.thumb_url || p.thumb}" class="rounded-2 flex-shrink-0" style="width:44px;height:44px;object-fit:cover" alt="${p.name}">
      <div class="flex-grow-1 min-width-0">
        <div class="fw-bold" style="font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${p.name}</div>
        <div class="text-muted" style="font-size:10px">${p.contractor || ''} &middot; ${p.phase || ''} &middot; Due: ${p.due_date || p.dueDate || ''}</div>
        <div class="mt-1 rounded-pill overflow-hidden" style="height:4px;background:#E3E3E8">
          <div style="height:100%;width:${p.progress}%;background:${barColor[p.status] || '#6E6E78'};border-radius:100px;transition:width .5s ease"></div>
        </div>
      </div>
      <div class="text-end flex-shrink-0 d-flex flex-column align-items-end gap-1">
        ${statusBadge[p.status] || ''}
        <span class="fw-bold" style="font-size:10px">${p.progress}%</span>
        <span class="text-muted" style="font-size:9px">${p.budget || ''}</span>
      </div>
    </div>`).join('');
}

/* ── DASHBOARD: MESSAGES ────────────────────────────────────────────── */
function renderDashMessages(data) {
  const el = document.getElementById('dash-messages-list');
  if (!el) return;
  data = (data || BL.messages).slice(0, 4);
  el.innerHTML = data.map(m => `
    <div class="d-flex align-items-start gap-2 p-2 rounded-3 mb-1"
      style="background:${!m.read ? '#F7F8FF' : 'transparent'};cursor:pointer"
      onclick="showToast('Opening message thread…')">
      <img src="${m.sender_avatar || m.avatar || 'https://i.pravatar.cc/100?img=1'}"
        class="rounded-circle flex-shrink-0" style="width:36px;height:36px;object-fit:cover" alt="${m.sender_name || m.from}">
      <div class="flex-grow-1 min-width-0">
        <div class="fw-bold" style="font-size:11px">${m.sender_name || m.from}
          <span class="text-muted fw-normal" style="font-size:9px;margin-left:4px">${m.sender_role || m.role || ''}</span>
        </div>
        <div class="text-muted" style="font-size:10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${m.content || m.preview}</div>
      </div>
      <div class="text-end flex-shrink-0">
        <div class="text-muted" style="font-size:9px">${m.created_at || m.time || ''}</div>
        ${!m.read ? `<div class="rounded-circle d-flex align-items-center justify-content-center ms-auto mt-1"
          style="width:17px;height:17px;background:var(--bl-accent);color:#fff;font-size:8px;font-weight:700">1</div>` : ''}
      </div>
    </div>`).join('');
}

/* ── DASHBOARD: BIDS ────────────────────────────────────────────────── */
function renderDashBids(data) {
  const el = document.getElementById('dash-bids-grid');
  if (!el) return;
  data = (data || BL.bids).slice(0, 3);
  const urgColor = { hot: '#C43100', warm: '#BF6900', new: '#1565C0' };
  el.innerHTML = data.map(b => `
    <div class="d-flex align-items-start gap-2 py-3"
      style="border-bottom:1px solid var(--bl-rule);cursor:pointer" onclick="showToast('Opening tender…')">
      <div class="rounded-pill flex-shrink-0 align-self-stretch"
        style="width:6px;background:${urgColor[b.urgency] || '#9E9E9E'}"></div>
      <div class="flex-grow-1 min-width-0">
        <div class="fw-bold" style="font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${b.title}</div>
        <div class="text-muted" style="font-size:10px">${b.location} &middot; ${fmtKES(b.budget_min)}&ndash;${fmtKES(b.budget_max)}</div>
        <div class="d-flex flex-wrap gap-1 mt-1">
          ${(b.trades || []).map(t => `<span style="font-size:9px;background:var(--bl-bg);color:var(--bl-muted);padding:2px 7px;border-radius:100px">${t}</span>`).join('')}
        </div>
      </div>
      <div class="text-end flex-shrink-0 ps-2">
        <div style="font-size:9px;font-weight:700;color:var(--bl-accent)"><i class="bi bi-clock"></i> ${b.deadline_days}d</div>
        <div class="text-muted" style="font-size:9px">${b.applications_count} bids</div>
        <button class="btn btn-dark mt-1" style="font-size:10px;font-weight:700;padding:3px 9px;border-radius:5px"
          onclick="event.stopPropagation();showToast('Opening bid form…')">Bid</button>
      </div>
    </div>`).join('');
}
