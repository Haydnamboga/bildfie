/*
 * BuildLink — Utilities
 * Toast, clock, scroll reveal, nav scroll, UI helpers, search, formatting
 */
'use strict';

/* ── TOAST ──────────────────────────────────────────────────────────── */
function showToast(msg, type = 'default') {
  let t = document.getElementById('bl-toast');
  if (!t) {
    t = document.createElement('div');
    t.id = 'bl-toast';
    t.className = 'bl-toast';
    t.innerHTML = '<div class="bl-toast-dot"></div><span id="bl-toast-msg"></span>';
    document.body.appendChild(t);
  }
  const dot = t.querySelector('.bl-toast-dot');
  dot.style.background = type === 'error' ? '#ef9a9a' : type === 'warning' ? '#D4A017' : '#4CAF50';
  t.querySelector('#bl-toast-msg').textContent = msg;
  t.classList.add('show');
  clearTimeout(t._t);
  t._t = setTimeout(() => t.classList.remove('show'), 3000);
}

/* ── FORMAT CURRENCY ─────────────────────────────────────────────────── */
function fmtKES(n) {
  if (n >= 1_000_000_000) return 'KES ' + (n / 1_000_000_000).toFixed(1) + 'B';
  if (n >= 1_000_000)     return 'KES ' + (n / 1_000_000).toFixed(1) + 'M';
  if (n >= 1_000)         return 'KES ' + (n / 1_000).toFixed(0) + 'K';
  return 'KES ' + n.toLocaleString();
}

/* ── SCROLL REVEAL ───────────────────────────────────────────────────── */
function initScrollReveal() {
  const obs = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('in'); });
  }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
  document.querySelectorAll('.bl-rv').forEach(el => obs.observe(el));
}

/* ── NAV SCROLL ──────────────────────────────────────────────────────── */
function initNav() {
  const nav = document.querySelector('.bl-navbar');
  if (!nav) return;
  // Scroll shadow only — active class is now server-rendered via EJS
  window.addEventListener('scroll', () => nav.classList.toggle('scrolled', window.scrollY > 20));
}

/* ── LIVE CLOCK ──────────────────────────────────────────────────────── */
function initClock() {
  const el = document.getElementById('live-time');
  if (!el) return;
  const update = () => {
    el.textContent = new Date().toLocaleTimeString('en-KE', {
      hour: '2-digit', minute: '2-digit', hour12: false, timeZone: 'Africa/Nairobi'
    });
  };
  update();
  setInterval(update, 10_000);
}

/* ── CHIP TOGGLES ────────────────────────────────────────────────────── */
function initChips() {
  document.querySelectorAll('.bl-chip[data-filter]').forEach(c => {
    c.addEventListener('click', () => c.classList.toggle('on'));
  });
}

/* ── TAB SWITCHING ───────────────────────────────────────────────────── */
function initTabs() {
  document.querySelectorAll('.bl-tbar').forEach(bar => {
    bar.querySelectorAll('.bl-tab').forEach(tab => {
      tab.addEventListener('click', () => {
        bar.querySelectorAll('.bl-tab').forEach(t => t.classList.remove('active', 'on'));
        tab.classList.add('on');
      });
    });
  });
  document.querySelectorAll('.bl-stab').forEach(tab => {
    tab.addEventListener('click', () => {
      tab.closest('.bl-search-tabs')
        ?.querySelectorAll('.bl-stab')
        .forEach(t => t.classList.remove('active', 'on'));
      tab.classList.add('on');
    });
  });
}

/* ── CHECKBOX TOGGLE ─────────────────────────────────────────────────── */
function toggleCheck(el) {
  el.querySelector('.bl-cb-box')?.classList.toggle('on');
}

/* ── CATEGORY PILL ───────────────────────────────────────────────────── */
function setCat(el) {
  el.closest('.bl-cat-inner, .bl-tbar')
    ?.querySelectorAll('.bl-cat-pill, .bl-tab')
    .forEach(p => p.classList.remove('active', 'on'));
  el.classList.add('on');
  showToast(`Showing: ${el.querySelector('.bl-cat-lbl, span')?.textContent || 'All'}`);
}

/* ── SEARCH ──────────────────────────────────────────────────────────── */
function initSearch() {
  const inp = document.getElementById('main-search');
  if (!inp) return;
  let debounce;
  inp.addEventListener('input', e => {
    clearTimeout(debounce);
    debounce = setTimeout(() => doSearch(e.target.value.toLowerCase().trim()), 200);
  });
  document.addEventListener('click', e => {
    const r = document.getElementById('search-results');
    if (r && !r.closest('.bl-search-box')?.contains(e.target)) r.classList.remove('open');
  });
}

function doSearch(q) {
  const r = document.getElementById('search-results');
  if (!r) return;
  if (!q) { r.classList.remove('open'); return; }
  r.classList.add('open');

  const profs = (window.BL?.professionals || [])
    .filter(p => p.name.toLowerCase().includes(q) || p.trade.toLowerCase().includes(q))
    .slice(0, 3);
  const mats = (window.BL?.materials || [])
    .filter(m => m.name.toLowerCase().includes(q) || m.category.toLowerCase().includes(q))
    .slice(0, 3);

  let html = '';
  if (profs.length) {
    html += `<div class="bl-sr-group">Professionals</div>`;
    profs.forEach(p => {
      html += `<div class="bl-sr-item" onclick="showToast('Opening ${p.name} profile')">
        <div class="d-flex align-items-center justify-content-center rounded-2 fw-bold bg-light me-1"
          style="flex-shrink:0;width:34px;height:34px;font-size:13px">${p.name.charAt(0)}</div>
        <div>
          <div class="fw-semibold" style="font-size:12px">${p.name}</div>
          <div class="text-muted" style="font-size:10px">${p.trade}</div>
        </div>
        <span class="badge ms-auto" style="background:#E8F5E9;color:#2E7D32">Pro</span>
      </div>`;
    });
  }
  if (mats.length) {
    html += `<div class="bl-sr-group">Materials</div>`;
    mats.forEach(m => {
      html += `<div class="bl-sr-item" onclick="showToast('Opening ${m.name}')">
        <div class="d-flex align-items-center justify-content-center rounded-2 fw-bold bg-light me-1"
          style="flex-shrink:0;width:34px;height:34px;font-size:11px;letter-spacing:-.5px">MAT</div>
        <div>
          <div class="fw-semibold" style="font-size:12px">${m.name}</div>
          <div class="text-muted" style="font-size:10px">${m.category} &middot; KES ${m.price.toLocaleString()}</div>
        </div>
        <span class="badge ms-auto" style="background:#E3F2FD;color:#1565C0">Material</span>
      </div>`;
    });
  }
  if (!html) html = `<div class="p-3 text-muted" style="font-size:12px">No results for "<strong>${q}</strong>"</div>`;
  r.innerHTML = html;
}

function quickSearch(term) {
  const inp = document.getElementById('main-search') || document.getElementById('hero-search');
  if (inp) { inp.value = term; doSearch(term.toLowerCase()); inp.focus(); }
  showToast(`Searching for "${term}"…`);
}
