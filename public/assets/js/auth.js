/*
 * BuildLink — Auth State Manager
 * Loaded on every page. Checks session, guards dashboard, updates navbar.
 */
'use strict';

window.BLUser = null;

/* ── Sign Out ── */
window.signOut = async function () {
  await BLApi.logout();
  window.BLUser = null;
  window.location.href = '/index.html';
};

/* ── Navbar injection ── */
function injectAuthNav(user) {
  // Find the nav auth zone — look for the Sign In button container
  const btns = document.querySelectorAll('.navbar .d-flex button, .navbar .d-flex a');
  let signInBtn = null;
  btns.forEach(b => { if (b.textContent.trim() === 'Sign In') signInBtn = b; });

  if (!signInBtn) return;
  const container = signInBtn.closest('.d-flex');
  if (!container) return;

  // Build the user menu HTML
  const avatarSrc = user.avatar || `https://i.pravatar.cc/100?img=${user.id % 70 + 1}`;
  const firstName  = (user.name || 'Account').split(' ')[0];
  const dashLink   = user.role === 'professional' ? '/dashboard/' : '/dashboard/';

  // Replace Sign In button with avatar dropdown
  signInBtn.outerHTML = `
    <div class="dropdown">
      <button class="btn btn-light btn-sm text-dark dropdown-toggle d-flex align-items-center gap-2 px-2" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius:8px">
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
        <li><a class="dropdown-item py-2" href="${dashLink}"><span class="me-2">📊</span>Dashboard</a></li>
        <li><a class="dropdown-item py-2" href="/account.html"><span class="me-2">👤</span>My Account</a></li>
        ${user.role === 'professional' ? `<li><a class="dropdown-item py-2" href="/profile.html"><span class="me-2">🏅</span>My Profile</a></li>` : ''}
        <li><a class="dropdown-item py-2" href="/dashboard/messages.html"><span class="me-2">💬</span>Messages ${user._unread ? `<span class="badge bg-danger ms-1" style="font-size:9px">${user._unread}</span>` : ''}</a></li>
        <li><hr class="dropdown-divider my-1"></li>
        <li><a class="dropdown-item py-2 text-danger" href="#" onclick="event.preventDefault();signOut()"><span class="me-2">🚪</span>Sign Out</a></li>
      </ul>
    </div>`;

  // Also update "Post a Project" button to actually open modal or navigate
  const postBtn = Array.from(container.querySelectorAll('button')).find(b => b.textContent.includes('Post a Project'));
  if (postBtn) {
    postBtn.setAttribute('onclick', "window.location.href='/dashboard/'");
    postBtn.textContent = '+ New Project';
  }

  // Update Dashboard link if present
  const dashA = container.querySelector('a[href*="dashboard"]');
  if (dashA) dashA.style.display = 'none'; // hidden now that dropdown has it
}

/* ── Guard dashboard / account pages ── */
function guardPage(user) {
  const path = window.location.pathname;
  const isProtected = path.includes('/dashboard') || path.includes('/account');
  if (isProtected && !user) {
    window.location.href = '/login.html?next=' + encodeURIComponent(window.location.pathname);
    return false;
  }
  return true;
}

/* ── Mark active dashboard link ── */
function markActiveDashLink() {
  const path = window.location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.bl-dsb-link').forEach(a => {
    const href = a.getAttribute('href') || '';
    if (href && (href.endsWith(path) || (path === 'index.html' && href === '#'))) {
      a.classList.add('on');
    }
  });
}

/* ── Main init ── */
async function initAuth() {
  // Wait for BLApi to exist
  if (typeof BLApi === 'undefined') return;

  const user = await BLApi.me();
  window.BLUser = user;

  if (!guardPage(user)) return;

  if (user) {
    injectAuthNav(user);
    // Broadcast for pages that need user data
    document.dispatchEvent(new CustomEvent('bl:user', { detail: user }));
  }

  markActiveDashLink();
}

document.addEventListener('DOMContentLoaded', initAuth);
