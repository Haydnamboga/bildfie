/*
 * BuildLink API Client + localStorage fallback
 * Tries the Express backend first; falls back to seeded local data.
 */
'use strict';

const API_BASE = '/api';
let _apiOnline = null;

async function checkApi() {
  if (_apiOnline !== null) return _apiOnline;
  try {
    const r = await fetch(`${API_BASE}/health`, { signal: AbortSignal.timeout(1500) });
    _apiOnline = r.ok;
  } catch { _apiOnline = false; }
  return _apiOnline;
}

async function apiFetch(path, opts = {}) {
  const online = await checkApi();
  if (!online) return null;
  try {
    const r = await fetch(`${API_BASE}${path}`, { credentials: 'include', headers: { 'Content-Type': 'application/json' }, ...opts });
    if (!r.ok) return null;
    return r.json();
  } catch { return null; }
}

/* ── Auth ── */
window.BLApi = {
  async login(email, password) {
    const online = await checkApi();
    if (!online) return null;
    try {
      const r = await fetch(`${API_BASE}/auth/login`, { method:'POST', credentials:'include', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ email, password }) });
      const data = await r.json();
      if (!r.ok) return { error: data.error || 'Login failed' };
      return data;
    } catch { return { error: 'Network error' }; }
  },
  async register(data) {
    const online = await checkApi();
    if (!online) return null;
    try {
      const r = await fetch(`${API_BASE}/auth/register`, { method:'POST', credentials:'include', headers:{'Content-Type':'application/json'}, body: JSON.stringify(data) });
      const d = await r.json();
      if (!r.ok) return { error: d.error || 'Registration failed' };
      return d;
    } catch { return { error: 'Network error' }; }
  },
  async me() { return apiFetch('/auth/me'); },
  async logout() { return apiFetch('/auth/logout', { method: 'POST' }); },
  async updateProfile(data) {
    const online = await checkApi();
    if (!online) return null;
    try {
      const r = await fetch(`${API_BASE}/auth/profile`, { method:'PUT', credentials:'include', headers:{'Content-Type':'application/json'}, body: JSON.stringify(data) });
      const d = await r.json();
      if (!r.ok) return { error: d.error || 'Update failed' };
      return d;
    } catch { return { error: 'Network error' }; }
  },
  async changePassword(current_password, new_password) {
    const online = await checkApi();
    if (!online) return null;
    try {
      const r = await fetch(`${API_BASE}/auth/password`, { method:'PUT', credentials:'include', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ current_password, new_password }) });
      const d = await r.json();
      if (!r.ok) return { error: d.error || 'Failed' };
      return d;
    } catch { return { error: 'Network error' }; }
  },
  async getSavedProfessionals() { return apiFetch('/auth/saved'); },
  async saveProfessional(id) { return apiFetch(`/auth/saved/${id}`, { method: 'POST' }); },
  async unsaveProfessional(id) { return apiFetch(`/auth/saved/${id}`, { method: 'DELETE' }); },

  /* ── Professionals ── */
  async getProfessionals(params = {}) {
    const qs = new URLSearchParams(params).toString();
    const result = await apiFetch(`/professionals?${qs}`);
    return result ? result.professionals : null;
  },

  /* ── Projects ── */
  async getMyProjects() { return apiFetch('/projects/my'); },
  async createProject(data) { return apiFetch('/projects', { method: 'POST', body: JSON.stringify(data) }); },
  async updateProject(id, data) { return apiFetch(`/projects/${id}`, { method: 'PUT', body: JSON.stringify(data) }); },
  async deleteProject(id) { return apiFetch(`/projects/${id}`, { method: 'DELETE' }); },

  /* ── Invitations ── */
  async getInvitations() { return apiFetch('/invitations/my'); },
  async getSentInvitations() { return apiFetch('/invitations/sent'); },
  async sendInvitation(data) { return apiFetch('/invitations', { method: 'POST', body: JSON.stringify(data) }); },
  async respondInvitation(id, status) { return apiFetch(`/invitations/${id}/respond`, { method: 'PUT', body: JSON.stringify({ status }) }); },

  /* ── Bids ── */
  async getBids() { return apiFetch('/bids'); },
  async applyBid(id, data) { return apiFetch(`/bids/${id}/apply`, { method: 'POST', body: JSON.stringify(data) }); },

  /* ── Materials ── */
  async getMaterials(params = {}) {
    const qs = new URLSearchParams(params).toString();
    const result = await apiFetch(`/materials?${qs}`);
    return result ? result.materials : null;
  },

  /* ── Equipment ── */
  async getEquipment(params = {}) {
    const qs = new URLSearchParams(params).toString();
    return apiFetch(`/equipment?${qs}`);
  },

  /* ── Messages ── */
  async getMessages() { return apiFetch('/messages'); },
  async sendMessage(data) { return apiFetch('/messages', { method: 'POST', body: JSON.stringify(data) }); },

  /* ── Dashboard ── */
  async getDashboardSummary() { return apiFetch('/dashboard/summary'); },
  async getInvoices() { return apiFetch('/dashboard/invoices'); },
  async approveInvoice(id) { return apiFetch(`/dashboard/invoices/${id}/approve`, { method: 'PUT' }); },
  async getNotifications() { return apiFetch('/dashboard/notifications'); },

  /* ── Prices ── */
  async getPrices() { return apiFetch('/prices'); },
};
