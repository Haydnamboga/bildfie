/*
 * BuildLink — App Init
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
  const [apiProfs, apiMats, apiBids, apiEqs] = await Promise.all([
    window.BLApi?.getProfessionals({ limit: 12 }).catch(() => null),
    window.BLApi?.getMaterials({ limit: 12 }).catch(() => null),
    window.BLApi?.getBids().catch(() => null),
    window.BLApi?.getEquipment().catch(() => null),
  ]);

  if (apiProfs) BL.professionals = apiProfs;
  if (apiMats)  BL.materials     = apiMats;
  if (apiBids)  BL.bids          = apiBids;
  if (apiEqs)   BL.equipment     = apiEqs;

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
