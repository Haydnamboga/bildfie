/*
 * Bildfie — Auth Manager
 * Loaded on every page. Handles login, register, forgot-password,
 * session checks, navbar injection, and page guards.
 */
'use strict';

window.BLUser = null;

/* ══════════════════════════════════════════════════════════════════
   LOGIN FORM
══════════════════════════════════════════════════════════════════ */

document.getElementById('login-form')?.addEventListener('submit', async function (e) {
  e.preventDefault();
  const email    = document.getElementById('email').value.trim();
  const password = document.getElementById('password').value;
  const btn      = document.getElementById('login-btn');
  const errEl    = document.getElementById('auth-err');

  btn.disabled    = true;
  btn.textContent = 'Signing in…';
  errEl.style.display = 'none';

  const result = await BLApi.login(email, password);

  if (!result || result.error) {
    errEl.textContent   = result?.error || 'Login failed — please try again';
    errEl.style.display = 'block';
    btn.disabled        = false;
    btn.textContent     = 'Sign In';
    return;
  }

  // Redirect: honour ?next= param, default to dashboard
  const next = new URLSearchParams(window.location.search).get('next') || '/dashboard';
  window.location.href = next;
});

/* ── Fill demo credentials (removed from login page in production) ── */
window.fillDemo = function () {
  const emailEl = document.getElementById('email');
  const pwEl    = document.getElementById('password');
  if (emailEl) emailEl.value = 'john.k@karidevelopers.co.ke';
  if (pwEl)    pwEl.value    = 'password123';
  document.getElementById('login-form')?.requestSubmit();
};

/* ── Forgot password modal ── */
window.showForgot = function (e) {
  e?.preventDefault();
  const modal = document.getElementById('forgotModal');
  if (modal && window.bootstrap) new bootstrap.Modal(modal).show();
};

window.submitForgot = async function () {
  const email = document.getElementById('forgot-email')?.value?.trim();
  if (!email) return showToast('Please enter your email', 'error');

  try {
    const r = await fetch('/api/auth/forgot-password', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ email }),
    });
    const d = await r.json();

    const modal = bootstrap.Modal.getInstance(document.getElementById('forgotModal'));
    modal?.hide();

    if (r.ok) {
      showToast('Reset link sent — check your inbox');
    } else {
      showToast(d.error || 'Could not send reset link', 'error');
    }
  } catch {
    showToast('Network error — please try again', 'error');
  }
};

/* ══════════════════════════════════════════════════════════════════
   REGISTER FORM
══════════════════════════════════════════════════════════════════ */

window.submitReg = async function () {
  const name     = document.getElementById('reg-name')?.value.trim();
  const email    = document.getElementById('reg-email')?.value.trim();
  const password = document.getElementById('reg-password')?.value;
  const password2= document.getElementById('reg-password2')?.value;
  const phone    = document.getElementById('reg-phone')?.value.trim();
  const location = document.getElementById('reg-location')?.value || 'Nairobi, Kenya';
  const terms    = document.getElementById('terms-check')?.checked;

  const errEl = document.getElementById('reg-err');
  const btn   = document.getElementById('reg-btn');

  const showErr = msg => {
    errEl.textContent   = msg;
    errEl.style.display = 'block';
    errEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  };

  if (!name)               return showErr('Full name is required');
  if (!email)              return showErr('Email address is required');
  if (!password)           return showErr('Password is required');
  if (password.length < 6) return showErr('Password must be at least 6 characters');
  if (password !== password2) return showErr('Passwords do not match');
  if (!terms)              return showErr('Please accept the Terms of Service');

  btn.disabled    = true;
  btn.textContent = 'Creating account…';
  errEl.style.display = 'none';

  const payload = { name, email, password, role: 'client', phone: phone || undefined, location };

  const result = await BLApi.register(payload);

  if (!result || result.error) {
    showErr(result?.error || 'Registration failed — please try again');
    btn.disabled    = false;
    btn.textContent = 'Create Free Account';
    return;
  }

  // Show success step
  document.getElementById('step-1').style.display = 'none';
  const step2 = document.getElementById('step-2');
  if (step2) {
    step2.style.display = 'block';
    const subEl = document.getElementById('success-name');
    if (subEl) subEl.textContent = `Welcome, ${name.split(' ')[0]}! Your account is ready.`;
  }
};

/* ── Password strength meter ── */
document.getElementById('reg-password')?.addEventListener('input', function () {
  const bar    = document.getElementById('pwd-strength');
  const label  = document.getElementById('pwd-label');
  const bars   = [document.getElementById('bar1'), document.getElementById('bar2'), document.getElementById('bar3')];
  if (!bar || !label) return;

  const v = this.value;
  bar.style.display = v ? 'block' : 'none';

  let score = 0;
  if (v.length >= 6)                       score++;
  if (v.length >= 10)                      score++;
  if (/[A-Z]/.test(v) && /[0-9]/.test(v)) score++;

  const colors = ['#ef5350', '#FFA726', '#66BB6A'];
  const labels = ['Weak', 'Fair', 'Strong'];
  bars.forEach((b, i) => {
    if (b) b.style.background = i < score ? colors[score - 1] : 'var(--bl-rule)';
  });
  if (label) { label.textContent = labels[score - 1] || ''; label.style.color = colors[score - 1] || 'var(--bl-muted)'; }
});

/* ══════════════════════════════════════════════════════════════════
   SIGN OUT
══════════════════════════════════════════════════════════════════ */

window.signOut = async function () {
  try { await BLApi.logout(); } catch (_) {}
  window.BLUser = null;
  window.location.href = '/';
};

/* ══════════════════════════════════════════════════════════════════
   NAVBAR INJECTION
══════════════════════════════════════════════════════════════════ */

function injectAuthNav(user) {
  const btns = document.querySelectorAll('.navbar .d-flex button, .navbar .d-flex a');
  let signInBtn = null;
  btns.forEach(b => { if (b.textContent.trim() === 'Sign In') signInBtn = b; });
  if (!signInBtn) return;

  const container  = signInBtn.closest('.d-flex');
  if (!container) return;

  const avatarSrc = user.avatar || `https://i.pravatar.cc/100?img=${(user.id % 70) + 1}`;
  const firstName = (user.name || 'Account').split(' ')[0];

  signInBtn.outerHTML = `
    <div class="dropdown">
      <button class="btn btn-light btn-sm text-dark dropdown-toggle d-flex align-items-center gap-2 px-2"
        data-bs-toggle="dropdown" aria-expanded="false" style="border-radius:8px">
        <img src="${avatarSrc}" style="width:24px;height:24px;border-radius:50%;object-fit:cover" alt="${user.name}">
        <span class="d-none d-md-inline fw-500" style="font-size:12px">${firstName}</span>
      </button>
      <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-1" style="border-radius:12px;min-width:200px;font-size:13px">
        <li>
          <div class="px-3 py-2 border-bottom">
            <div class="fw-bold" style="font-size:13px">${user.name}</div>
            <div class="text-muted" style="font-size:11px">${user.email}</div>
          </div>
        </li>
        <li><a class="dropdown-item py-2" href="/dashboard"><span class="me-2">📊</span>Dashboard</a></li>
        <li><a class="dropdown-item py-2" href="/account"><span class="me-2">👤</span>My Account</a></li>
        ${user.role === 'professional' ? `<li><a class="dropdown-item py-2" href="/profile"><span class="me-2">🏅</span>My Profile</a></li>` : ''}
        <li><a class="dropdown-item py-2" href="/dashboard/messages"><span class="me-2">💬</span>Messages${user._unread ? ` <span class="badge bg-danger ms-1" style="font-size:9px">${user._unread}</span>` : ''}</a></li>
        <li><hr class="dropdown-divider my-1"></li>
        <li><a class="dropdown-item py-2 text-danger" href="/api/auth/logout"><span class="me-2">🚪</span>Sign Out</a></li>
      </ul>
    </div>`;

  // "Post a Project" → navigate to dashboard
  const postBtn = Array.from(container.querySelectorAll('button')).find(b => b.textContent.includes('Post a Project'));
  if (postBtn) {
    postBtn.setAttribute('onclick', "window.location.href='/dashboard'");
    postBtn.textContent = '+ New Project';
  }
}

/* ══════════════════════════════════════════════════════════════════
   DASHBOARD INIT (populates metrics + greeting when on /dashboard)
══════════════════════════════════════════════════════════════════ */

async function initDashboard(user) {
  // Greeting
  const greetEl = document.getElementById('dash-greeting-name');
  if (greetEl) greetEl.textContent = (user.name || 'there').split(' ')[0];

  // Load summary
  const summary = await BLApi.getDashboardSummary().catch(() => null);
  if (summary) {
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
    set('metric-projects', summary.active_projects ?? summary.projects ?? '—');
    set('metric-messages', summary.unread_messages ?? '—');
    set('metric-invoices', summary.pending_invoices ?? '—');
    if (summary.total_budget != null) set('metric-budget', fmtKES(summary.total_budget));
  }

  // Load user's projects
  const myProjects = await BLApi.getMyProjects().catch(() => null);
  if (myProjects) {
    BL.userProjects = myProjects;
    renderDashProjects(myProjects);
  }

  // Load messages
  const msgs = await BLApi.getMessages().catch(() => null);
  if (msgs) renderDashMessages(msgs);

  // Load notifications badge
  const notifs = await BLApi.getNotifications().catch(() => null);
  if (notifs) {
    const unread = Array.isArray(notifs) ? notifs.filter(n => !n.read).length : 0;
    const badge  = document.getElementById('notif-count');
    if (badge) { badge.textContent = unread; badge.style.display = unread ? 'inline-flex' : 'none'; }
  }
}

/* ══════════════════════════════════════════════════════════════════
   PAGE GUARD
══════════════════════════════════════════════════════════════════ */

function guardPage(user) {
  const path        = window.location.pathname;
  const isProtected = path.startsWith('/dashboard') || path.startsWith('/account');
  if (isProtected && !user) {
    window.location.href = '/login?next=' + encodeURIComponent(window.location.pathname);
    return false;
  }
  return true;
}

/* ══════════════════════════════════════════════════════════════════
   MAIN INIT
══════════════════════════════════════════════════════════════════ */

async function initAuth() {
  if (typeof BLApi === 'undefined') return;

  const user = await BLApi.me();
  window.BLUser = user;

  if (!guardPage(user)) return;

  if (user) {
    injectAuthNav(user);
    document.dispatchEvent(new CustomEvent('bl:user', { detail: user }));

    // Dashboard-specific data loading
    if (window.location.pathname.startsWith('/dashboard')) {
      initDashboard(user);
    }
  }
}

document.addEventListener('DOMContentLoaded', initAuth);
