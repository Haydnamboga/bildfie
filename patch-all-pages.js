/**
 * BuildLink — patch-all-pages.js  (v5 — String.fromCodePoint edition)
 *
 * All mojibake search strings are built at runtime via cp() so no invisible
 * control characters (U+008D / U+008F / U+0090 / U+009D) can be dropped
 * during source-file editing.
 */

'use strict';
var fs   = require('fs');
var path = require('path');

var BASE = path.join(__dirname, 'public');
var FILES = [
  'index.html', 'professionals.html', 'materials.html',
  'equipment.html', 'transport.html', 'facilities.html',
  'projects.html', 'login.html', 'register.html',
  'account.html', 'profile.html',
  'dashboard/index.html', 'dashboard/messages.html',
  'dashboard/invitations.html', 'dashboard/invoices.html',
  'admin/index.html',
];

var BI_CDN = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">';

// Build a string from explicit Unicode codepoint integers.
// Avoids any possibility of invisible control chars being lost in source.
function cp() {
  var out = '';
  for (var i = 0; i < arguments.length; i++) {
    out += String.fromCodePoint(arguments[i]);
  }
  return out;
}

function bi(cls, sp) {
  return '<i class="bi ' + cls + '"></i>' + (sp !== false ? ' ' : '');
}

// Shorthand codepoints for frequently used mojibake chars
var F0 = 0xF0, _9F = 0x178;   // F0 9F prefix (ðŸ)
// 0x87 in CP1252 = U+2021  (‡  double dagger) — used in flag sequences

// ─────────────────────────────────────────────────────────────────────────────
// REPLACEMENT TABLE  [ searchString, replacementString ]
//
// Built with cp(...codepoints) for mojibake entries (sects 2–5).
// Actual-Unicode emoji (sect 6) typed directly — safe, no control chars.
//
// Longer / more-specific patterns BEFORE shorter prefix fallbacks.
// ─────────────────────────────────────────────────────────────────────────────
var REPS = [

  // ── 1. BOM
  [cp(0xFEFF), ''],

  // ── 2. Kenya flag  🇰🇪  (F0 9F 87 B0  F0 9F 87 AA)
  //    0x87 → U+2021 (‡),  0xB0 → °,  0xAA → ª
  [cp(F0,_9F,0x2021,0xB0, F0,_9F,0x2021,0xAA), ''],   // 🇰🇪 full
  [cp(F0,_9F,0x2021,0xB0),  ''],                        // 🇰 alone
  [cp(F0,_9F,0x2021,0xAA),  ''],                        // 🇪 alone

  // ── 3. Mojibake typographic chars (Windows-1252 bytes re-saved as UTF-8)

  // — em-dash  E2 80 94  →  0x80=€(U+20AC),  0x94="(U+201D)
  [cp(0xE2,0x20AC,0x201D), '&mdash;'],
  // – en-dash  E2 80 93  →  0x80=€,           0x93="(U+201C)
  [cp(0xE2,0x20AC,0x201C), '&ndash;'],
  // … ellipsis E2 80 A6  →  0x80=€,           0xA6=¦(U+00A6)
  [cp(0xE2,0x20AC,0x00A6), '&hellip;'],

  // ↑ up arrow   E2 86 91  →  0x86=†(U+2020),  0x91='(U+2018)
  [cp(0xE2,0x2020,0x2018), '&uarr;'],
  // → right arr  E2 86 92  →  0x86=†,           0x92='(U+2019)
  [cp(0xE2,0x2020,0x2019), '&rarr;'],
  // ↓ down arr   E2 86 93  →  0x86=†,           0x93="(U+201C)
  [cp(0xE2,0x2020,0x201C), '&darr;'],
  // ← left arr   E2 86 90  →  0x86=†,           0x90=U+0090 (ctrl)
  [cp(0xE2,0x2020,0x0090), '&larr;'],

  // Â-prefix Latin chars  (C2 + direct Latin-1 byte)
  [cp(0xC2,0x00A9), '&copy;'],    // © C2 A9
  [cp(0xC2,0x00B3), '&sup3;'],   // ³ C2 B3
  [cp(0xC2,0x00B2), '&sup2;'],   // ² C2 B2
  [cp(0xC2,0x00B7), '&middot;'], // · C2 B7
  [cp(0xC2,0x00B0), '&deg;'],    // ° C2 B0
  [cp(0xC2,0x00BD), '&frac12;'], // ½ C2 BD
  [cp(0xC2,0x00A0), ' '],        // NBSP C2 A0 → space

  // × times sign  C3 97  →  0xC3=Ã(U+00C3),  0x97=—(U+2014)
  [cp(0x00C3,0x2014), '&times;'],

  // ── 4. Mojibake 3-byte symbols  (E2-based)

  // ✅  E2 9C 85  →  0x9C=Œ(U+0153),  0x85=…(U+2026)
  [cp(0xE2,0x0153,0x2026), bi('bi-check-circle-fill text-success')],
  // ✓   E2 9C 93  →  0x9C=Œ,           0x93="(U+201C)
  [cp(0xE2,0x0153,0x201C), bi('bi-check')],
  // ✕   E2 9C 95  →  0x9C=Œ,           0x95=•(U+2022)
  [cp(0xE2,0x0153,0x2022), '&times;'],

  // ⭐  E2 AD 90  →  0xAD=­(U+00AD),  0x90=U+0090 ctrl
  [cp(0xE2,0x00AD,0x0090), bi('bi-star-fill text-warning')],

  // ★★★★★ five-star run — BEFORE single star
  [cp(0xE2,0x02DC,0x2026, 0xE2,0x02DC,0x2026, 0xE2,0x02DC,0x2026,
      0xE2,0x02DC,0x2026, 0xE2,0x02DC,0x2026),
   '&#9733;&#9733;&#9733;&#9733;&#9733;'],
  // ★ single star  E2 98 85  →  0x98=˜(U+02DC),  0x85=…(U+2026)
  [cp(0xE2,0x02DC,0x2026), '&#9733;'],

  // ☰ list   E2 98 B0  →  0x98=˜,  0xB0=°(U+00B0)
  [cp(0xE2,0x02DC,0x00B0), bi('bi-list-ul', false)],
  // ⊞ grid   E2 8A 9E  →  0x8A=Š(U+0160),  0x9E=ž(U+017E)
  [cp(0xE2,0x0160,0x017E), bi('bi-grid', false)],

  // ⚙️ gear + variation-selector  E2 9A 99  EF B8 8F
  //    0x9A=š(U+0161),  0x99=™(U+2122),  then EF B8 8F
  [cp(0xE2,0x0161,0x2122, 0xEF,0x00B8,0x008F), bi('bi-gear')],
  // ⚙  gear alone
  [cp(0xE2,0x0161,0x2122), bi('bi-gear')],
  // ⚠  E2 9A A0  →  0x9A=š,  0xA0=U+00A0
  [cp(0xE2,0x0161,0x00A0), bi('bi-exclamation-triangle')],
  // ⚡  E2 9A A1  →  0x9A=š,  0xA1=¡(U+00A1)
  [cp(0xE2,0x0161,0x00A1), bi('bi-lightning')],

  // ⛏  E2 9B 8F  →  0x9B=›(U+203A),  0x8F=U+008F ctrl
  [cp(0xE2,0x203A,0x008F), bi('bi-gear')],
  // ●   E2 97 8F  →  0x97=—(U+2014),  0x8F=U+008F ctrl
  [cp(0xE2,0x2014,0x008F), bi('bi-circle-fill', false)],
  // ⏱  E2 8F B1  →  0x8F=U+008F ctrl,  0xB1=±(U+00B1)
  [cp(0xE2,0x008F,0x00B1), bi('bi-stopwatch')],

  // ── 5. Mojibake 4-byte emoji  (F0 9F  →  U+00F0 U+0178)
  //    Specific 4-char entries BEFORE 3-char prefix fallbacks.

  // ─── Buildings  (3rd byte 0x8F → U+008F ctrl)
  [cp(F0,_9F,0x008F,0x2014), bi('bi-building-fill-gear')],  // 🏗  0x97=—
  [cp(F0,_9F,0x008F,0x203A), bi('bi-bank')],                // 🏛  0x9B=›
  [cp(F0,_9F,0x008F,0x00A0), bi('bi-house-fill')],          // 🏠  0xA0=NBSP
  [cp(F0,_9F,0x008F,0x00A1), bi('bi-house-fill')],          // 🏡  0xA1=¡
  [cp(F0,_9F,0x008F,0x2020), bi('bi-trophy')],              // 🏆  0x86=†
  [cp(F0,_9F,0x008F,0x02DC), bi('bi-houses')],              // 🏘  0x98=˜
  [cp(F0,_9F,0x008F,0x00A2), bi('bi-building')],            // 🏢  0xA2=¢
  [cp(F0,_9F,0x008F,0x00AD), bi('bi-building-fill')],       // 🏭  0xAD=­
  [cp(F0,_9F,0x008F,0x00AA), bi('bi-shop')],                // 🏪  0xAA=ª
  [cp(F0,_9F,0x008F,0x0161), bi('bi-building')],            // 🏚  0x9A=š
  [cp(F0,_9F,0x008F),        bi('bi-building')],            // fallback

  // ─── Art / nature
  [cp(F0,_9F,0x017D,0x00A8), bi('bi-palette')],    // 🎨  0x8E=Ž,0xA8=¨
  [cp(F0,_9F,0x017D,0x201C), bi('bi-mortarboard')],// 🎓  0x8E=Ž,0x93="
  [cp(F0,_9F,0x017D),        bi('bi-palette')],    // fallback F0 9F 8E
  [cp(F0,_9F,0x0152,0x00BF), bi('bi-tree')],       // 🌿  0x8C=Œ,0xBF=¿
  [cp(F0,_9F,0x0152,0x008D), bi('bi-globe')],      // 🌍  0x8C=Œ,0x8D=ctrl
  [cp(F0,_9F,0x0152),        bi('bi-tree')],        // fallback F0 9F 8C

  // ─── Food / dining  (3rd byte 0x8D → U+008D ctrl)
  [cp(F0,_9F,0x008D,0x00BD), bi('bi-box')],  // 🍽  0x8D=ctrl,0xBD=½
  [cp(F0,_9F,0x008D),        bi('bi-box')],  // fallback

  // ─── People  (3rd byte 0x91 → U+2018)
  [cp(F0,_9F,0x2018,0x00B7), bi('bi-person-badge')],     // 👷  0x91=',0xB7=·
  [cp(F0,_9F,0x2018,0x008D), bi('bi-hand-thumbs-up')],   // 👍  0x91=',0x8D=ctrl
  [cp(F0,_9F,0x2018,0x2020), bi('bi-arrow-up')],          // 👆  0x91=',0x86=†
  [cp(F0,_9F,0x2018),        bi('bi-person')],            // fallback

  // ─── Objects / money  (3rd byte 0x92 → U+2019)
  [cp(F0,_9F,0x2019,0x00BC), bi('bi-briefcase')],   // 💼  0xBC=¼
  [cp(F0,_9F,0x2019,0x00AC), bi('bi-chat')],        // 💬  0xAC=¬
  [cp(F0,_9F,0x2019,0x00A7), bi('bi-droplet')],     // 💧  0xA7=§
  [cp(F0,_9F,0x2019,0x00B0), bi('bi-cash-stack')],  // 💰  0xB0=°
  [cp(F0,_9F,0x2019,0x00A1), bi('bi-lightbulb')],   // 💡  0xA1=¡
  [cp(F0,_9F,0x2019,0x00A8), bi('bi-wind')],        // 💨  0xA8=¨
  [cp(F0,_9F,0x2019),        bi('bi-cash')],         // fallback

  // ─── Documents  (3rd byte 0x93 → U+201C)
  [cp(F0,_9F,0x201C,0x008D), bi('bi-geo-alt-fill')],   // 📍  0x8D=ctrl
  [cp(F0,_9F,0x201C,0x2039), bi('bi-clipboard')],       // 📋  0x8B=‹
  [cp(F0,_9F,0x201C,0x0160), bi('bi-bar-chart')],       // 📊  0x8A=Š
  [cp(F0,_9F,0x201C,0x00A6), bi('bi-box-seam')],        // 📦  0xA6=¦
  [cp(F0,_9F,0x201C,0x2026), bi('bi-calendar')],        // 📅  0x85=…
  [cp(F0,_9F,0x201C,0x2020), bi('bi-calendar-check')],  // 📆  0x86=†
  [cp(F0,_9F,0x201C,0x02C6), bi('bi-graph-up')],        // 📈  0x88=ˆ
  [cp(F0,_9F,0x201C,0x2030), bi('bi-graph-down')],      // 📉  0x89=‰
  [cp(F0,_9F,0x201C,0x0090), bi('bi-patch-check')],     // 📐  0x90=ctrl
  [cp(F0,_9F,0x201C),        bi('bi-file')],             // fallback

  // ─── Tools / locks  (3rd byte 0x94 → U+201D)
  [cp(F0,_9F,0x201D,0x008D), bi('bi-search')],        // 🔍  0x8D=ctrl
  [cp(F0,_9F,0x201D,0x00A7), bi('bi-wrench')],        // 🔧  0xA7=§
  [cp(F0,_9F,0x201D,0x00A9), bi('bi-tools')],         // 🔩  0xA9=©
  [cp(F0,_9F,0x201D,0x2018), bi('bi-key')],           // 🔑  0x91='
  [cp(F0,_9F,0x201D,0x2019), bi('bi-lock')],          // 🔒  0x92='
  [cp(F0,_9F,0x201D,0x0090), bi('bi-patch-check')],   // 🔐  0x90=ctrl
  [cp(F0,_9F,0x201D,0x201E), bi('bi-arrow-repeat')],  // 🔄  0x84=„
  [cp(F0,_9F,0x201D,0x00A5), bi('bi-fire')],          // 🔥  0xA5=¥
  [cp(F0,_9F,0x201D,0x00A8), bi('bi-hammer')],        // 🔨  0xA8=¨
  [cp(F0,_9F,0x201D,0x0152), bi('bi-plug')],          // 🔌  0x8C=Œ
  [cp(F0,_9F,0x201D),        bi('bi-tools')],          // fallback

  // ─── Maps / spiral calendars  (3rd byte 0x97 → U+2014)
  [cp(F0,_9F,0x2014,0x00BA), bi('bi-map')],        // 🗺  0xBA=º
  [cp(F0,_9F,0x2014,0x201C), bi('bi-calendar3')],  // 🗓  0x93="
  [cp(F0,_9F,0x2014),        bi('bi-map')],         // fallback

  // ─── Clocks  (3rd byte 0x95 → U+2022)
  [cp(F0,_9F,0x2022,0x0090), bi('bi-clock')],   // 🕐  0x90=ctrl
  [cp(F0,_9F,0x2022,0x2018), bi('bi-clock')],   // 🕑  0x91='
  [cp(F0,_9F,0x2022),        bi('bi-clock')],   // fallback

  // ─── Transport  (3rd byte 0x9A → U+0161)
  [cp(F0,_9F,0x0161,0x0161), bi('bi-truck')],     // 🚚  same 4th byte
  [cp(F0,_9F,0x0161,0x0153), bi('bi-truck')],     // 🚜  0x9C=œ
  [cp(F0,_9F,0x0161,0x00A2), bi('bi-water')],     // 🚢  0xA2=¢
  [cp(F0,_9F,0x0161,0x203A), bi('bi-truck')],     // 🚛  0x9B=›
  [cp(F0,_9F,0x0161,0x20AC), bi('bi-rocket')],    // 🚀  0x80=€
  [cp(F0,_9F,0x0161,0x0090), bi('bi-bus')],       // 🚐  0x90=ctrl
  [cp(F0,_9F,0x0161,0x00BD), bi('bi-droplet')],   // 🚽  0xBD=½
  [cp(F0,_9F,0x0161,0x00BE), bi('bi-water')],     // 🚾  0xBE=¾
  [cp(F0,_9F,0x0161),        bi('bi-truck')],      // fallback

  // ─── Misc objects  (3rd byte 0x9B → U+203A)
  [cp(F0,_9F,0x203A,0x00A0), bi('bi-tools')],         // 🛠  0xA0=NBSP
  [cp(F0,_9F,0x203A,0x00A1), bi('bi-shield-check')],  // 🛡  0xA1=¡
  [cp(F0,_9F,0x203A,0x00A2), bi('bi-droplet-fill')],  // 🛢  0xA2=¢
  [cp(F0,_9F,0x203A,0x00A3), bi('bi-map')],           // 🛣  0xA3=£
  [cp(F0,_9F,0x203A,0x00BB), bi('bi-truck')],         // 🛻  0xBB=»
  [cp(F0,_9F,0x203A),        bi('bi-tools')],          // fallback

  // ─── Colored circles/squares  (3rd byte 0x9F → U+0178)
  [cp(F0,_9F,0x0178,0x00A2), ''],                           // 🟢 remove
  [cp(F0,_9F,0x0178,0x00A0), ''],                           // 🟠 remove
  [cp(F0,_9F,0x0178,0x00A1), ''],                           // 🟡 remove
  [cp(F0,_9F,0x0178,0x00AB), bi('bi-grid-3x3-gap', false)], // 🟫
  [cp(F0,_9F,0x0178),        ''],                            // fallback remove

  // ─── Safety vest  (3rd byte 0xA6 → U+00A6)
  [cp(F0,_9F,0x00A6,0x00BA), bi('bi-person-badge')],  // 🦺
  [cp(F0,_9F,0x00A6),        bi('bi-person-badge')],  // fallback

  // ─── Handshake  (3rd byte 0xA4 → U+00A4)
  [cp(F0,_9F,0x00A4,0x009D), bi('bi-handshake')],  // 🤝  0x9D=ctrl
  [cp(F0,_9F,0x00A4),        bi('bi-handshake')],  // fallback

  // ─── Bricks  (3rd byte 0xA7 → U+00A7)
  [cp(F0,_9F,0x00A7,0x00B1), bi('bi-bricks')],  // 🧱
  [cp(F0,_9F,0x00A7),        bi('bi-tools')],   // fallback

  // ─── Wood / bucket  (3rd byte 0xAA → U+00AA)
  [cp(F0,_9F,0x00AA,0x00B5), bi('bi-tree-fill')],  // 🪵
  [cp(F0,_9F,0x00AA,0x00A3), bi('bi-archive')],    // 🪣
  [cp(F0,_9F,0x00AA),        bi('bi-tree')],        // fallback

  // ── 6. Actual-Unicode emoji  (dashboard / admin use properly-encoded emoji)
  ['👋', bi('bi-hand-wave')],
  ['🏗', bi('bi-building-fill-gear')],
  ['🏘', bi('bi-houses')],
  ['🏢', bi('bi-building')],
  ['🏆', bi('bi-trophy')],
  ['🎨', bi('bi-palette')],
  ['🎉', bi('bi-stars')],
  ['💬', bi('bi-chat')],
  ['💰', bi('bi-cash-stack')],
  ['💼', bi('bi-briefcase')],
  ['💧', bi('bi-droplet')],
  ['💡', bi('bi-lightbulb')],
  ['📄', bi('bi-file-text')],
  ['📍', bi('bi-geo-alt-fill')],
  ['📅', bi('bi-calendar')],
  ['📊', bi('bi-bar-chart')],
  ['📋', bi('bi-clipboard')],
  ['📦', bi('bi-box-seam')],
  ['📝', bi('bi-file-text')],
  ['🔍', bi('bi-search')],
  ['🔧', bi('bi-wrench')],
  ['🔨', bi('bi-hammer')],
  ['🔑', bi('bi-key')],
  ['🔒', bi('bi-lock')],
  ['👤', bi('bi-person')],
  ['👥', bi('bi-people')],
  ['👷', bi('bi-person-badge')],
  ['👍', bi('bi-hand-thumbs-up')],
  ['🧱', bi('bi-bricks')],
  ['💳', bi('bi-credit-card')],
  ['🛠', bi('bi-tools')],
  ['🛡', bi('bi-shield-check')],
  ['🚚', bi('bi-truck')],
  ['🚜', bi('bi-truck')],
  ['🚢', bi('bi-water')],
  ['✅', bi('bi-check-circle-fill text-success')],
  ['⚠',  bi('bi-exclamation-triangle')],
  ['⚡',  bi('bi-lightning')],
  ['⚙',  bi('bi-gear')],
  ['✓',  bi('bi-check')],
  ['✔',  bi('bi-check-lg')],
  ['★',  '&#9733;'],
  ['☆',  '&#9734;'],
  ['●',  bi('bi-circle-fill', false)],
];

// ─────────────────────────────────────────────────────────────────────────────
function applyReps(html) {
  for (var i = 0; i < REPS.length; i++) {
    var s = REPS[i][0];
    if (html.indexOf(s) !== -1) {
      html = html.split(s).join(REPS[i][1]);
    }
  }
  return html;
}

function injectBICDN(html) {
  if (html.indexOf('bootstrap-icons') !== -1) return html;
  var bootIdx = html.indexOf('bootstrap');
  if (bootIdx !== -1) {
    var end = html.indexOf('>', bootIdx);
    if (end !== -1) {
      return html.slice(0, end + 1) + '\n    ' + BI_CDN + html.slice(end + 1);
    }
  }
  return html.replace('</head>', '    ' + BI_CDN + '\n</head>');
}

function fixProfModalIds(html) {
  return html
    .replace(/id="profModal-title"/g,   'id="modal-name"')
    .replace(/id="profModal-avatar"/g,  'id="modal-avatar"')
    .replace(/id="profModal-name"/g,    'id="modal-fullname"')
    .replace(/id="profModal-meta"/g,    'id="modal-title"')
    .replace(/id="profModal-rating"/g,  'id="modal-rating"')
    .replace(/id="profModal-reviews"/g, 'id="modal-reviews"')
    .replace(/id="profModal-stats"/g,   'id="modal-stats"')
    .replace(/id="profModal-bio"/g,     'id="modal-bio"')
    .replace(/id="profModal-tags"/g,    'id="modal-tags"')
    .replace(/id="profModal-avail"/g,   'id="modal-avail"')
    .replace(/id="inviteModal-name"/g,  'id="invite-pro-name"');
}

// ─────────────────────────────────────────────────────────────────────────────
var changed = 0, skipped = 0;

FILES.forEach(function(rel) {
  var fpath = path.join(BASE, rel);
  if (!fs.existsSync(fpath)) {
    console.log('  SKIP (not found): ' + rel);
    skipped++;
    return;
  }
  var html   = fs.readFileSync(fpath, 'utf8');
  var before = html;

  html = injectBICDN(html);
  html = applyReps(html);
  if (rel === 'professionals.html') html = fixProfModalIds(html);

  if (html !== before) {
    fs.writeFileSync(fpath, html, 'utf8');
    console.log('  OK   ' + rel);
    changed++;
  } else {
    console.log('  --   ' + rel + '  (no changes)');
  }
});

console.log('\nDone. ' + changed + ' file(s) updated, ' + skipped + ' skipped.');
