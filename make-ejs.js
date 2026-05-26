'use strict';
/**
 * make-ejs.js — converts static HTML pages to EJS templates
 * Run once: node make-ejs.js
 */
const fs = require('fs');
const path = require('path');
const BASE = __dirname;

/* ── Path fixers ─────────────────────────────────────────────────────── */
function fixPaths(html) {
  // Asset paths: '../assets/' or 'assets/' → '/assets/'
  html = html.replace(/src=(["'])\.\.\/assets\//g, 'src=$1/assets/');
  html = html.replace(/href=(["'])\.\.\/assets\//g, 'href=$1/assets/');
  html = html.replace(/src=(["'])assets\//g, 'src=$1/assets/');
  html = html.replace(/href=(["'])assets\//g, 'href=$1/assets/');

  // Ordered map (longer/more-specific patterns first to avoid partial matches)
  const map = [
    ['href="../admin/"',                    'href="/admin"'],
    ['href="admin/"',                       'href="/admin"'],
    ['href="dashboard/index.html"',         'href="/dashboard"'],
    ['href="dashboard/"',                   'href="/dashboard"'],
    ['href="dashboard/messages.html"',      'href="/dashboard/messages"'],
    ['href="dashboard/invoices.html"',      'href="/dashboard/invoices"'],
    ['href="dashboard/invitations.html"',   'href="/dashboard/invitations"'],
    ['href="messages.html"',                'href="/dashboard/messages"'],
    ['href="invitations.html"',             'href="/dashboard/invitations"'],
    ['href="invoices.html"',                'href="/dashboard/invoices"'],
    ['href="index.html"',                   'href="/"'],
    ['href="../index.html"',                'href="/"'],
    ['href="professionals.html"',           'href="/professionals"'],
    ['href="../professionals.html"',        'href="/professionals"'],
    ['href="materials.html"',               'href="/materials"'],
    ['href="../materials.html"',            'href="/materials"'],
    ['href="equipment.html"',               'href="/equipment"'],
    ['href="../equipment.html"',            'href="/equipment"'],
    ['href="transport.html"',               'href="/transport"'],
    ['href="../transport.html"',            'href="/transport"'],
    ['href="facilities.html"',              'href="/facilities"'],
    ['href="../facilities.html"',           'href="/facilities"'],
    ['href="projects.html"',                'href="/projects"'],
    ['href="login.html"',                   'href="/login"'],
    ['href="../login.html"',                'href="/login"'],
    ['href="register.html"',                'href="/register"'],
    ['href="../register.html"',             'href="/register"'],
    ['href="account.html"',                 'href="/account"'],
    ['href="../account.html"',              'href="/account"'],
    ['href="profile.html"',                 'href="/profile"'],
    ['href="../profile.html"',              'href="/profile"'],
  ];
  for (const [from, to] of map) html = html.split(from).join(to);

  // JS inline redirects
  html = html.split("'login.html?next=/dashboard/'").join("'/login?next=/dashboard'");
  html = html.split('"login.html?next=/dashboard/"').join('"/login?next=/dashboard"');
  html = html.split("'login.html'").join("'/login'");

  return html;
}

/* ── Common script block to strip (after fixPaths, paths are absolute) */
const STD_SCRIPTS =
  '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>\n' +
  '<script src="/assets/js/data.js"></script>\n' +
  '<script src="/assets/js/api.js"></script>\n' +
  '<script src="/assets/js/app.js"></script>\n' +
  '<script src="/assets/js/auth.js"></script>';

// Variants for pages where auth.js is on the same line as app.js
const STD_SCRIPTS_COMPACT =
  '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>\n' +
  '<script src="/assets/js/data.js"></script>\n' +
  '<script src="/assets/js/api.js"></script>\n' +
  '<script src="/assets/js/app.js"></script><script src="/assets/js/auth.js"></script>';

function stripStdScripts(html) {
  // Try both variants
  html = html.replace(STD_SCRIPTS, '%%SCRIPTS%%');
  html = html.replace(STD_SCRIPTS_COMPACT, '%%SCRIPTS%%');
  // Fallback: find bootstrap CDN line and next 4 script tags
  if (!html.includes('%%SCRIPTS%%')) {
    const bs = '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>';
    const idx = html.indexOf(bs);
    if (idx !== -1) {
      // Find 4 more </script> after bootstrap tag
      let pos = idx + bs.length;
      for (let i = 0; i < 4; i++) {
        const next = html.indexOf('</script>', pos);
        if (next === -1) break;
        pos = next + '</script>'.length;
      }
      html = html.slice(0, idx) + '%%SCRIPTS%%' + html.slice(pos);
    }
  }
  return html;
}

/* ── Write helper ───────────────────────────────────────────────────── */
function write(dstFile, content) {
  const fullPath = path.join(BASE, dstFile);
  fs.mkdirSync(path.dirname(fullPath), { recursive: true });
  fs.writeFileSync(fullPath, content, 'utf8');
  console.log('  ✓', dstFile);
}

/* ══════════════════════════════════════════════════════════════════════
   PUBLIC PAGES  (head + navbar + [content] + footer + scripts)
   ══════════════════════════════════════════════════════════════════════ */
function processPublicPage(srcFile, dstFile, opts) {
  let html = fs.readFileSync(path.join(BASE, srcFile), 'utf8');
  html = fixPaths(html);
  html = stripStdScripts(html);

  // Remove <!DOCTYPE … </head>
  const headEnd = html.indexOf('</head>') + '</head>'.length;

  // Remove <body[attrs]> … </nav>
  const bodyTagStart = html.indexOf('<body', headEnd);
  const bodyTag = html.slice(bodyTagStart, html.indexOf('>', bodyTagStart) + 1);
  const navClose = html.indexOf('</nav>', bodyTagStart) + '</nav>'.length;

  // Locate footer
  const footerMark = html.indexOf('<!-- FOOTER -->');
  const footerClose = html.indexOf('</footer>') + '</footer>'.length;

  // Locate scripts placeholder
  const scriptsMark = html.indexOf('%%SCRIPTS%%');

  // Body content (between nav end and footer or scripts)
  const contentEnd = footerMark !== -1 ? footerMark : scriptsMark;
  const bodyContent = html.slice(navClose, contentEnd);

  // After-footer content (modals etc. between </footer> and scripts)
  const afterFooter = footerMark !== -1
    ? html.slice(footerClose, scriptsMark).trim()
    : '';

  // After-scripts content (inline <script> blocks + </body></html>)
  const afterScripts = html.slice(scriptsMark + '%%SCRIPTS%%'.length).trim();
  // afterScripts may start with \n<script>...</script>\n</body>\n</html>
  // We want everything except the closing </body></html> (we add our own)
  const bodyCloseIdx = afterScripts.lastIndexOf('</body>');
  const inlineScript = bodyCloseIdx !== -1
    ? afterScripts.slice(0, bodyCloseIdx).trim()
    : afterScripts.replace(/<\/html>\s*$/, '').trim();

  const pd = '../partials';
  const { title, activePage, description } = opts;
  const titleArg = JSON.stringify(title);
  const apArg = JSON.stringify(activePage || '');
  const descArg = description ? `, description: ${JSON.stringify(description)}` : '';

  const out = [
    `<%- include('${pd}/head', { title: ${titleArg}${descArg} }) %>`,
    `<%- include('${pd}/navbar', { activePage: ${apArg}, user: locals.user }) %>`,
    bodyContent,
    `<%- include('${pd}/footer') %>`,
    afterFooter,
    `<%- include('${pd}/scripts') %>`,
    inlineScript,
    '</body>',
    '</html>',
  ].filter(s => s.trim() !== '').join('\n');

  write(dstFile, out + '\n');
}

/* ══════════════════════════════════════════════════════════════════════
   DASHBOARD PAGES  (head + dash-topbar + dash-sidebar + [main] + scripts)
   ══════════════════════════════════════════════════════════════════════ */
function processDashPage(srcFile, dstFile, opts) {
  let html = fs.readFileSync(path.join(BASE, srcFile), 'utf8');
  html = fixPaths(html);
  html = stripStdScripts(html);

  // Remove <!DOCTYPE … </head>
  const headEnd = html.indexOf('</head>') + '</head>'.length;

  // Remove <body[...]> … </aside>
  const asideClose = html.indexOf('</aside>') + '</aside>'.length;

  // Locate scripts placeholder
  const scriptsMark = html.indexOf('%%SCRIPTS%%');

  // Main content (between </aside> and scripts)
  const mainContent = html.slice(asideClose, scriptsMark);

  // After-scripts: inline script blocks
  const afterScripts = html.slice(scriptsMark + '%%SCRIPTS%%'.length).trim();
  const bodyCloseIdx = afterScripts.lastIndexOf('</body>');
  const inlineScript = bodyCloseIdx !== -1
    ? afterScripts.slice(0, bodyCloseIdx).trim()
    : afterScripts.replace(/<\/html>\s*$/, '').trim();

  const pd = '../../partials';
  const { title, activeSection } = opts;

  const out = [
    `<%- include('${pd}/head', { title: ${JSON.stringify(title)} }) %>`,
    `<%- include('${pd}/dash-topbar', { user: locals.user }) %>`,
    `<%- include('${pd}/dash-sidebar', { activeSection: ${JSON.stringify(activeSection || '')} }) %>`,
    mainContent,
    `<%- include('${pd}/scripts') %>`,
    inlineScript,
    '</body>',
    '</html>',
  ].filter(s => s.trim() !== '').join('\n');

  write(dstFile, out + '\n');
}

/* ══════════════════════════════════════════════════════════════════════
   ADMIN PAGE  (head with extraHead + own topbar/sidebar + scripts)
   ══════════════════════════════════════════════════════════════════════ */
function processAdminPage(srcFile, dstFile, opts) {
  let html = fs.readFileSync(path.join(BASE, srcFile), 'utf8');
  html = fixPaths(html);

  // Extract chart.js CDN (it's in the head)
  const chartScript = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>';

  // Extract inline <style> block from head
  const styleStart = html.indexOf('<style>');
  const styleEnd = html.indexOf('</style>') + '</style>'.length;
  const inlineStyle = styleStart !== -1 ? html.slice(styleStart, styleEnd) : '';

  // extraHead = chart.js + inline styles
  const extraHead = [chartScript, inlineStyle].filter(Boolean).join('\n');

  // Remove head section
  const headEnd = html.indexOf('</head>') + '</head>'.length;

  // Body content (everything from <body> to before bootstrap script)
  const bodyContent = html.slice(headEnd);
  const bsScript = '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>';
  const bsIdx = bodyContent.indexOf(bsScript);

  const mainContent = bodyContent.slice(0, bsIdx);

  // After bootstrap: inline scripts
  const afterBs = bodyContent.slice(bsIdx + bsScript.length).trim();
  const bodyCloseIdx = afterBs.lastIndexOf('</body>');
  const inlineScript = bodyCloseIdx !== -1
    ? afterBs.slice(0, bodyCloseIdx).trim()
    : afterBs.replace(/<\/html>\s*$/, '').trim();

  const pd = '../../partials';

  const out = [
    `<%- include('${pd}/head', { title: ${JSON.stringify(opts.title)}, extraHead: ${JSON.stringify(extraHead)} }) %>`,
    mainContent,
    `<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>`,
    inlineScript,
    '</body>',
    '</html>',
  ].filter(s => s.trim() !== '').join('\n');

  write(dstFile, out + '\n');
}

/* ══════════════════════════════════════════════════════════════════════
   RUN ALL
   ══════════════════════════════════════════════════════════════════════ */

console.log('\nGenerating public pages…');
const publicPages = [
  { src: 'public/index.html',         dst: 'views/pages/index.ejs',         title: "BuildLink — Africa's #1 Construction Marketplace", description: "Africa's leading construction marketplace. Find verified professionals, source materials, hire equipment and manage your project.", activePage: '' },
  { src: 'public/professionals.html', dst: 'views/pages/professionals.ejs', title: 'Find Professionals — BuildLink', activePage: 'professionals' },
  { src: 'public/materials.html',     dst: 'views/pages/materials.ejs',     title: 'Materials — BuildLink',          activePage: 'materials'      },
  { src: 'public/equipment.html',     dst: 'views/pages/equipment.ejs',     title: 'Equipment — BuildLink',          activePage: 'equipment'      },
  { src: 'public/transport.html',     dst: 'views/pages/transport.ejs',     title: 'Transport — BuildLink',          activePage: 'transport'      },
  { src: 'public/facilities.html',    dst: 'views/pages/facilities.ejs',    title: 'Facilities — BuildLink',         activePage: 'facilities'     },
  { src: 'public/projects.html',      dst: 'views/pages/projects.ejs',      title: 'Projects — BuildLink',           activePage: 'projects'       },
  { src: 'public/login.html',         dst: 'views/pages/login.ejs',         title: 'Sign In — BuildLink',            activePage: ''               },
  { src: 'public/register.html',      dst: 'views/pages/register.ejs',      title: 'Register — BuildLink',           activePage: ''               },
  { src: 'public/account.html',       dst: 'views/pages/account.ejs',       title: 'My Account — BuildLink',         activePage: ''               },
  { src: 'public/profile.html',       dst: 'views/pages/profile.ejs',       title: 'My Profile — BuildLink',         activePage: ''               },
];
for (const p of publicPages) processPublicPage(p.src, p.dst, p);

console.log('\nGenerating dashboard pages…');
const dashPages = [
  { src: 'public/dashboard/index.html',       dst: 'views/pages/dashboard/index.ejs',       title: 'Dashboard — BuildLink CRM',  activeSection: 'overview'     },
  { src: 'public/dashboard/messages.html',    dst: 'views/pages/dashboard/messages.ejs',    title: 'Messages — BuildLink',       activeSection: 'messages'     },
  { src: 'public/dashboard/invitations.html', dst: 'views/pages/dashboard/invitations.ejs', title: 'Invitations — BuildLink',    activeSection: 'invitations'  },
  { src: 'public/dashboard/invoices.html',    dst: 'views/pages/dashboard/invoices.ejs',    title: 'Invoices — BuildLink',       activeSection: 'invoices'     },
];
for (const p of dashPages) processDashPage(p.src, p.dst, p);

console.log('\nGenerating admin page…');
processAdminPage('public/admin/index.html', 'views/pages/admin/index.ejs', { title: 'Admin Dashboard — BuildLink' });

console.log('\nAll EJS pages generated.\n');
