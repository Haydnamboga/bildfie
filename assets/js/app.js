'use strict';

/* ── sidebar toggle ── */
function initSidebar() {
  const btn      = document.getElementById('sidebarToggle');
  const sidebar  = document.getElementById('bfSidebar');
  const main     = document.querySelector('.bf-main');
  const backdrop = document.getElementById('bfSideBackdrop');
  if (!btn || !sidebar) return;
  const openM  = () => { sidebar.classList.add('open');  backdrop && backdrop.classList.add('show'); };
  const closeM = () => { sidebar.classList.remove('open'); backdrop && backdrop.classList.remove('show'); };
  btn.addEventListener('click', e => {
    e.preventDefault(); e.stopPropagation();
    if (window.innerWidth < 992) {
      sidebar.classList.contains('open') ? closeM() : openM();
    } else {
      sidebar.classList.toggle('collapsed');
      main && main.classList.toggle('wide');
    }
  });
  if (backdrop) backdrop.addEventListener('click', closeM);
  // tapping a link inside the sidebar closes it on mobile
  sidebar.addEventListener('click', e => { if (window.innerWidth < 992 && e.target.closest('a.bf-link')) closeM(); });
  // reset when crossing the breakpoint back to desktop
  window.addEventListener('resize', () => { if (window.innerWidth >= 992) closeM(); });
}

/* ── auto-dismiss alerts ── */
function initAlerts() {
  document.querySelectorAll('.alert[data-ms]').forEach(el => {
    setTimeout(() => bootstrap.Alert.getOrCreateInstance(el)?.close(), +el.dataset.ms || 4000);
  });
}

/* ── confirm before action ── */
function initConfirm() {
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => { if (!confirm(el.dataset.confirm)) e.preventDefault(); });
  });
}

/* ── engagement modals (quote / review / hire) ── */
function initEngage() {
  const map = { quote: 'quoteModal', review: 'reviewModal', hire: 'hireModal' };
  document.querySelectorAll('[data-engage]').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      const id = map[btn.dataset.engage];
      const modalEl = document.getElementById(id);
      if (!modalEl || !window.bootstrap) return;
      // set context label + target provider, clear any prior error
      const ctx = btn.dataset.ctx;
      if (ctx) modalEl.querySelectorAll('.js-ctx').forEach(el => el.textContent = ctx);
      const pid = btn.dataset.pid || '';
      modalEl.querySelectorAll('input[name="provider_id"]').forEach(i => i.value = pid);
      const errOpen = modalEl.querySelector('.js-engage-err');
      if (errOpen) { errOpen.style.display = 'none'; errOpen.innerHTML = ''; }
      // reset to form view (hide any previous success)
      const form = modalEl.querySelector('.js-engage-form');
      const ok   = modalEl.querySelector('.js-engage-success');
      if (form) form.style.display = '';
      if (ok) ok.classList.remove('show');
      const stars = modalEl.querySelector('.bf-stars');
      if (stars) { stars.dataset.rating = '0'; stars.querySelectorAll('i').forEach(s => { s.classList.remove('on'); s.className = 'bi bi-star'; s.dataset.v = s.dataset.v; }); }
      bootstrap.Modal.getOrCreateInstance(modalEl).show();
    });
  });

  // star rating pickers
  document.querySelectorAll('.bf-stars').forEach(widget => {
    const stars = [...widget.querySelectorAll('i')];
    const paint = n => stars.forEach((s, i) => {
      const on = i < n;
      s.classList.toggle('on', on);
      s.classList.toggle('bi-star-fill', on);
      s.classList.toggle('bi-star', !on);
    });
    stars.forEach((s, i) => {
      s.addEventListener('mouseenter', () => paint(i + 1));
      s.addEventListener('click', () => { widget.dataset.rating = i + 1; paint(i + 1); });
    });
    widget.addEventListener('mouseleave', () => paint(+widget.dataset.rating || 0));
  });

  // real submit → POST to /api/engage.php (falls back to demo success when there's no provider target)
  document.querySelectorAll('.js-engage-form').forEach(form => {
    form.addEventListener('submit', async e => {
      e.preventDefault();
      const errBox = form.querySelector('.js-engage-err');
      const showErr = html => { if (errBox) { errBox.innerHTML = html; errBox.style.display = 'block'; } };
      const succeed = () => {
        form.style.display = 'none';
        const ok = form.parentElement.querySelector('.js-engage-success');
        if (ok) ok.classList.add('show');
      };
      const pidInput = form.querySelector('input[name="provider_id"]');
      const pid = pidInput ? pidInput.value : '';
      if (!pid || pid === '0') { succeed(); return; }   // no real target (e.g. supplier card) → demo success
      if (errBox) errBox.style.display = 'none';
      const fd = new FormData(form);
      const stars = form.querySelector('.bf-stars');
      if (stars) fd.set('rating', stars.dataset.rating || '0');
      const btn = form.querySelector('[type="submit"]');
      if (btn) btn.disabled = true;
      try {
        const res  = await fetch('/api/engage.php', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'fetch' } });
        const data = await res.json();
        if (data.ok) succeed();
        else if (data.login) showErr('Please <a href="/pages/auth/login.php" style="color:#c0392b;font-weight:700;text-decoration:underline;">sign in</a> to continue.');
        else showErr(data.error || 'Something went wrong. Please try again.');
      } catch (_) {
        showErr('Network error — please try again.');
      } finally {
        if (btn) btn.disabled = false;
      }
    });
  });
}

/* ── favourites / save (localStorage) ── */
function initFav() {
  let saved;
  try { saved = JSON.parse(localStorage.getItem('bf_saved') || '[]'); } catch (_) { saved = []; }
  document.querySelectorAll('[data-fav]').forEach(btn => {
    const key = btn.dataset.fav;
    if (saved.includes(key)) btn.classList.add('is-saved');
    btn.addEventListener('click', e => {
      e.preventDefault(); e.stopPropagation();
      const i = saved.indexOf(key);
      if (i === -1) { saved.push(key); btn.classList.add('is-saved'); }
      else { saved.splice(i, 1); btn.classList.remove('is-saved'); }
      try { localStorage.setItem('bf_saved', JSON.stringify(saved)); } catch (_) {}
    });
  });
}

/* ── follow companies / organizations (localStorage) ── */
function initFollow() {
  let following;
  try { following = JSON.parse(localStorage.getItem('bf_following') || '[]'); } catch (_) { following = []; }
  document.querySelectorAll('[data-follow]').forEach(btn => {
    const key = btn.dataset.follow;
    if (following.includes(key)) btn.classList.add('is-following');
    btn.addEventListener('click', e => {
      e.preventDefault(); e.stopPropagation();
      const i = following.indexOf(key);
      if (i === -1) { following.push(key); btn.classList.add('is-following'); }
      else { following.splice(i, 1); btn.classList.remove('is-following'); }
      try { localStorage.setItem('bf_following', JSON.stringify(following)); } catch (_) {}
    });
  });
}

/* ── tabbed panels (project workspace) ── */
function initTabs() {
  document.querySelectorAll('[data-tabnav]').forEach(nav => {
    const root   = (nav.parentElement && nav.parentElement.querySelector('[data-tabroot]')) || document;
    const btns   = [...nav.querySelectorAll('[data-tab]')];
    const panels = [...root.querySelectorAll('[data-panel]')];
    const activate = t => {
      if (!btns.some(b => b.dataset.tab === t)) return false;
      btns.forEach(b => b.classList.toggle('active', b.dataset.tab === t));
      panels.forEach(p => p.classList.toggle('active', p.dataset.panel === t));
      return true;
    };
    btns.forEach(btn => btn.addEventListener('click', () => {
      activate(btn.dataset.tab);
      try { history.replaceState(null, '', '#' + btn.dataset.tab); } catch (_) {}
    }));
    // Honour the URL hash on load so a POST → redirect (#tasks, #milestones…) stays on that tab.
    const h = (location.hash || '').replace('#', '');
    if (h) activate(h);
  });
}

/* ── kanban drag & drop ── */
function initKanban() {
  const boards = document.querySelectorAll('[data-kanban]');
  if (!boards.length) return;
  let dragged = null;
  const recount = board => board.querySelectorAll('[data-col]').forEach(col => {
    const c = col.querySelector('.bf-kan-count');
    if (c) c.textContent = col.querySelectorAll('.bf-kan-card').length;
  });
  boards.forEach(board => {
    board.querySelectorAll('.bf-kan-card').forEach(card => {
      card.addEventListener('dragstart', () => { dragged = card; setTimeout(() => card.classList.add('dragging'), 0); });
      card.addEventListener('dragend',   () => { card.classList.remove('dragging'); dragged = null; recount(board); });
    });
    board.querySelectorAll('[data-col]').forEach(col => {
      col.addEventListener('dragover',  e => { e.preventDefault(); col.classList.add('drag-over'); });
      col.addEventListener('dragleave', () => col.classList.remove('drag-over'));
      col.addEventListener('drop', e => {
        e.preventDefault(); col.classList.remove('drag-over');
        if (dragged) { col.insertBefore(dragged, col.querySelector('.bf-kan-add')); recount(board); }
      });
    });
  });
}

document.addEventListener('DOMContentLoaded', () => {
  initSidebar();
  initAlerts();
  initConfirm();
  initEngage();
  initFav();
  initFollow();
  initTabs();
  initKanban();
});
