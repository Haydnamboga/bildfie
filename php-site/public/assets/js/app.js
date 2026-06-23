/*
 * bildfie — App Init
 * Boots the page: fetches live data from the API, merges with static
 * fallbacks, then renders all grids and widgets.
 *
 * Depends on (loaded before this file):
 *   data.js      → window.BL (static data store)
 *   api.js       → window.BLApi (API client)
 *   bl-utils.js  → showToast, fmtKES, initNav, initClock, initSearch, …
 *   bl-render.js → renderProfessionals, renderMaterials, …
 *   bl-modals.js → openProfModal, openInviteModal, submitInvite, …
 */
'use strict';

document.addEventListener('DOMContentLoaded', async () => {

  /* ── Boot UI helpers ── */
  initScrollReveal();
  initNav();
  initClock();
  initTabs();
  initSearch();
  initChips();

  /* ── Fetch live data (non-blocking; falls back to BL static data) ── */
  const [apiProfs, apiMats, apiBids, apiEqs, apiTr, apiProjs] = await Promise.all([
    window.BLApi?.getProfessionals({ limit: 12 }).catch(() => null),
    window.BLApi?.getMaterials({ limit: 12 }).catch(() => null),
    window.BLApi?.getBids().catch(() => null),
    window.BLApi?.getEquipment().catch(() => null),
    window.BLApi?.getTransport().catch(() => null),
    window.BLApi?.getPublicProjects().catch(() => null),
  ]);

  // null  = API call failed (offline / error) → keep static fallback
  // []    = API online but empty              → show empty state
  // [...] = real data                         → show it
  if (apiProfs  !== null) BL.professionals = apiProfs;
  if (apiMats   !== null) BL.materials     = apiMats;
  if (apiBids   !== null) BL.bids          = apiBids;
  if (apiEqs    !== null) BL.equipment     = apiEqs;
  if (apiTr     !== null) BL.transport     = apiTr;
  if (apiProjs  !== null) BL.projects      = apiProjs;

  /* ── Render marketplace grids ── */
  const grid = id => document.getElementById(id);

  if (grid('profs-grid'))  renderProfessionals(grid('profs-grid'),  BL.professionals);
  if (grid('mats-grid'))   renderMaterials(grid('mats-grid'),       BL.materials);
  if (grid('bids-grid'))   renderBids(grid('bids-grid'),            BL.bids);
  if (grid('projs-grid'))  renderProjects(grid('projs-grid'),       BL.projects);
  if (grid('eq-grid'))     renderEquipment(grid('eq-grid'),         BL.equipment);
  if (grid('tr-grid'))     renderTransport(grid('tr-grid'),         BL.transport);

  /* ── Render dashboard widgets (no-ops on non-dashboard pages) ── */
  renderDashProjects();
  renderDashMessages();
  renderDashBids();
});
