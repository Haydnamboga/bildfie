# CECOME — website

A fast, accessible, story-driven **static website** for **CECOME — the Centre for
Community Mobilization and Empowerment**, a woman-led organisation ending female genital
mutilation (FGM) and gender-based violence (GBV) in **Kisii County, Kenya** (since 2012).

This is a proposed replacement for the current cecome.org. It is built with plain
HTML, CSS and a touch of vanilla JavaScript — **no build step, no dependencies, no
database** — so it can be hosted anywhere and edited by anyone comfortable with HTML.

---

## What's here

```
cecome/
├── index.html          # Home — hero, the problem, pillars, impact, story, CTA
├── about.html          # Who We Are — story, mission, vision, values, timeline, team
├── programs.html       # What We Do — the 4 pillars + every programme
├── impact.html         # Our Impact — stats, featured story, story grid, advocacy wins
├── get-involved.html   # Donate / Partner / Volunteer + FAQ
├── contact.html        # Contact form, details, map, helpline
├── 404.html            # Friendly not-found page
├── robots.txt          # SEO
├── sitemap.xml         # SEO (update the domain)
└── assets/
    ├── css/styles.css  # The whole design system (one file)
    ├── js/main.js      # Nav, scroll reveals, counters, accordion, form stub
    └── img/            # Logo, favicon, social card, background pattern (all SVG)
```

## Design at a glance

- **Identity:** dignified, warm and hopeful — credible enough for donors, human enough for the community.
- **Colours:** deep Gusii-highland green `#0e5c4a`, warm marigold gold `#e6a82a`, clay accent `#db6a3c`, warm cream background. All defined as CSS variables at the top of `styles.css`.
- **Type:** *Fraunces* (display serif) + *Plus Jakarta Sans* (body), loaded from Google Fonts.
- **Motion:** subtle reveal-on-scroll and animated counters, all disabled automatically for visitors who prefer reduced motion.
- **Accessibility:** semantic HTML, skip link, keyboard-friendly menu/accordion, focus styles, alt text, good colour contrast.

---

## How to preview locally

It's just static files. Any of these works:

```bash
# Python
cd cecome && python3 -m http.server 8080
# then open http://localhost:8080

# or Node
npx serve cecome
```

You can also just double-click `index.html`, though a local server is recommended.

---

## ✅ Before you go live — things to personalise

Search the project for the word **`EDITABLE`** (and the bracketed `[ … ]` placeholders)
to find everything that needs your real details. The main ones:

1. **Real photos.** Every image is currently a labelled placeholder (`.media .ph`). Drop
   a real photo in `assets/img/` and replace the placeholder `<div class="ph">…</div>`
   with `<img src="assets/img/your-photo.jpg" alt="Describe the photo">`. Each placeholder
   caption tells you what kind of photo fits there.
2. **Impact numbers.** The stat counters on `index.html` and `impact.html` use solid,
   sourced figures where available and clearly-illustrative ones elsewhere (marked with an
   `EDITABLE` comment). Replace them with CECOME's own verified, current totals.
3. **Donation details** (`get-involved.html#donate`): add your real **M-Pesa Paybill/Till**,
   **bank account**, and an **online giving link** (e.g. a Donorbox/PayPal page).
4. **Email address:** the site uses `info@cecome.org` throughout — change it if needed.
5. **Team:** `about.html` features the Executive Director plus role-based placeholder cards.
   Add real names, roles and photos.
6. **Contact form:** it currently shows a success message without sending (no backend).
   To make it deliver email, point the `<form>` `action` at a service like
   [Formspree](https://formspree.io) or [Getform], e.g.
   `action="https://formspree.io/f/XXXX" method="POST"`, and remove the `data-demo` attribute.
7. **Map:** replace the map placeholder on `contact.html` with a real Google Maps embed `<iframe>`.
8. **Domain:** update the URL in `robots.txt` and `sitemap.xml`, and the `og:image`/links
   if the site lives somewhere other than the domain root.
9. **Social card:** `assets/img/og-cover.svg` is a branded share image. Some platforms
   prefer PNG/JPG for Open Graph — export it to `og-cover.jpg` (1200×630) and update the
   `<meta property="og:image">` tags if link previews matter to you.

> A note on content accuracy: the copy was researched from public sources (CECOME's own
> pages as indexed, plus partners like the EU, Solidaarisuus/ISF, Equality Now, YW4A and
> Kenyan news outlets). Please have the team review every figure, name and quote before
> publishing so the site speaks in CECOME's authentic voice.

---

## Deploying (pick one — all free for a site this size)

- **Netlify / Cloudflare Pages / Vercel:** drag-and-drop the `cecome/` folder, or connect
  the repo and set the publish directory to `cecome`. The `404.html` is picked up automatically.
- **GitHub Pages:** push and enable Pages on the folder/branch.
- **Your existing host:** upload the contents of `cecome/` to the web root via cPanel/FTP.

No server-side runtime is required.

---

## Editing tips

- Header and footer markup is repeated in each page (so the site needs no build tool).
  If you change a nav link or footer detail, update it in each `.html` file.
- All colours, spacing, fonts and shadows are CSS variables at the top of
  `assets/css/styles.css` — change the brand there and it updates everywhere.
- The logo is inline SVG (in the header/footer) and also available as
  `assets/img/logo.svg` / `favicon.svg`.

---

*Empowered, Resilient and Safe Communities.*
