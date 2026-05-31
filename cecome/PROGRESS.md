# CECOME website — work-in-progress notes (for continuation after context compaction)

## STATE
- A full v1 static site is already committed & pushed on branch `claude/fervent-cerf-XUqL2`
  in repo `Haydnamboga/bildfie`, folder `cecome/`. Commit `ce65ec3`.
- Files: index.html, about.html, programs.html, impact.html, get-involved.html,
  contact.html, 404.html, assets/css/styles.css, assets/js/main.js,
  assets/img/{logo,favicon,pattern,og-cover}.svg, robots.txt, sitemap.xml,
  netlify.toml, README.md, .nojekyll.
- A zip (cecome-website.zip) was delivered to the user; it's .gitignored.

## USER FEEDBACK (the task NOW)
"Do a better job. The website needs to look BEAUTIFUL. It needs:
 1. images, 2. youtube links, 3. be like real local/international NGO sites."
User thinks assets (logo/colors/photos) can be taken from live cecome.org.

## HARD CONSTRAINT DISCOVERED (critical)
- Sandbox egress is an ALLOWLIST. cecome.org, google, youtube, unsplash, pexels,
  wikimedia (upload.wikimedia.org, commons), picsum → ALL 403 "Host not in allowlist".
- ALLOWED hosts (return 200): raw.githubusercontent.com, cdn.jsdelivr.net,
  unpkg.com, fonts.gstatic.com, fonts.googleapis.com.
- => I CANNOT download cecome's real logo/photos here. BUT the DEPLOYED site runs in a
  real browser (no allowlist) so hotlinked photos + YouTube embeds WILL work for visitors.
  I just can't pixel-verify them from the sandbox.

## STRATEGY (agreed, in progress)
Make it beautiful REGARDLESS of whether external assets resolve:
1. Bundle REAL raster photos by downloading via cdn.jsdelivr.net/gh/... (allowlisted)
   from openly/CC0/Unsplash-licensed GitHub photo repos -> commit to assets/img/photos/.
   (jsdelivr can proxy ANY github repo + npm packages.)
2. ALSO hotlink Unsplash/Pexels in <img> with onerror -> reveal a designed gradient+SVG
   fallback (so a blocked/wrong URL never shows a broken icon; still looks art-directed).
3. Embed REAL YouTube videos via click-to-play facade (youtube-nocookie). Real IDs found:
   - rtS3bg0BbUM  (Girls embrace ARP away from FGM, Marsabit)
   - E9z0PCI_DzQ  (YouTube SHORT: Kisii women rep on FGM)
   - Also CECOME Facebook ARP graduation lives (facebook.com/CecomeKisii/videos).
   Use a generic YouTube facade; user swaps in CECOME's own channel/video IDs.
4. Dramatically upgrade design quality: duotone photo frames (green), magazine/asymmetric
   layouts, marquee partners, refined hero with photo+stats, micro-interactions, textile
   SVG motifs. Keep brand: green #0e5c4a, gold #e6a82a, clay #db6a3c, cream #fbf7ef.

## CONFIRMED CECOME FACTS (use as content)
- Centre for Community Mobilization and Empowerment; woman-led NGO; registered Kenya 2012.
- Vision: "Empowered, Resilient and Safe Communities."
- Mission: support & champion rights/dignity of vulnerable groups via access, advocacy,
  capacity building, livelihood promotion.
- Focus: end FGM (Kisii prevalence ~77-86%), GBV/SGBV, gender equality, child protection,
  civic education, M&E, health, young women leadership.
- Pillars: GBV/FGM Prevention; Economic Empowerment (village savings/VSLA); Educational
  Support; Community Collaboration & Advocacy.
- Programs: Alternative Rite of Passage (ARP) — April/Aug holidays, Dec graduation, 200+
  girls one cohort 2021; Men as Champions; YW4A young women leadership; rescue centre +
  county gender policy advocacy; radio/TV/Facebook live.
- Leadership: Stella Achoki (Executive Director).
- Partners: EU, Solidaarisuus/ISF (Finland), Equality Now, YW4A/YWCA, Manga Heart,
  Embassy of Finland, Kisii County Govt.
- Contact: Credit Bank Building, Hospital Road (next to Jubilee Insurance), Kisii;
  P.O. Box 2039-40200; +254 737 609 255; info@cecome.org; Mon-Fri 8-5 EAT.
  FB: facebook.com/CecomeKisii ; LinkedIn: company/centre-for-community-mobilization-and-empowerment-cecome
- Emergency: Kenya 999/112; GBV helpline 1195.

## NEXT STEPS (resume here)
1. Finish testing jsdelivr gh image download (was probing when compaction hit).
   Find a good CC0 people/Africa/community photo repo on GitHub, download ~10-14 images
   to cecome/assets/img/photos/ (hero, arp, savings, education, advocacy, men, portrait,
   kisii landscape, classroom, hands/solidarity, graduation, community-meeting).
   Candidate approach: search GitHub for repos like "unsplash", "stock-photos", "pexels",
   "cc0", "sample-images" with people; or use a known dataset. Verify content_type image/*.
2. Add CSS components: .photo (frame), .duotone, .media img real, video facade (.video-embed
   .vid-poster .vid-play), .marquee, refined hero-with-photo. (Append to styles.css.)
3. Add JS: video facade click->inject iframe; img onerror->add .img-failed to parent.
4. Rewrite index.html (showcase) + update other pages to use <img> + one video section.
5. Keep header/footer logo as inline SVG (already good) until real logo available.
6. Re-run integrity checks (brace balance, tag balance, links).
7. Rebuild zip, commit, push to claude/fervent-cerf-XUqL2 (with backoff). Keep tree clean.
8. Final message: explain egress allowlist (can't reach cecome.org), what I used instead
   (licensed stock + real YouTube), and the one-find-replace path to swap in CECOME's
   own logo/photos/videos. List the real video URLs found.

## NOTES
- git remote is a local proxy locked to Haydnamboga/bildfie ONLY. Cannot push to new
  'cecome' repo from this session (Access denied). User must copy folder/zip to it.
- Stop hook requires clean working tree (commit+push everything; .gitignore the zip).
