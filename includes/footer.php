<footer class="bf-footer mt-auto">
  <div class="container">
    <div class="row g-4 g-lg-5">

      <!-- Brand col -->
      <div class="col-12 col-lg-3">
        <div class="bf-footer-brand d-flex align-items-center gap-2 mb-3">
          <i class="bi bi-building-fill-up"></i> bildfie
        </div>
        <p style="font-size:13px;color:rgba(255,255,255,.45);line-height:1.7;max-width:220px;">
          A construction marketplace connecting verified professionals with quality projects.
        </p>
        <div class="mt-4" style="font-size:11px;color:rgba(255,255,255,.25);line-height:1.8;">
          <div>🇰🇪 Kenya</div>
          <div class="mt-1"><a href="mailto:hello@bildfie.com" style="color:rgba(255,255,255,.35);">hello@bildfie.com</a></div>
        </div>
      </div>

      <!-- Marketplace -->
      <div class="col-6 col-sm-4 col-lg-2">
        <div class="bf-footer-heading">Marketplace</div>
        <?php foreach ([
          'Professionals' => '/pages/marketplace/professionals.php',
          'Open Bids' => '/pages/bids/index.php',
        ] as $l => $h): ?>
        <a href="<?= $h ?>" class="bf-footer-link"><?= $l ?></a>
        <?php endforeach; ?>
      </div>

      <!-- Platform -->
      <div class="col-6 col-sm-4 col-lg-2">
        <div class="bf-footer-heading">Platform</div>
        <?php foreach ([
          'How it Works' => '#',
          'Pricing & Plans' => '/pages/account/subscription.php',
          'Dashboard' => '/pages/dashboard/index.php',
          'Post a Project' => '/pages/projects/create.php',
          'API & Integrations' => '#',
          'Mobile App' => '#',
          'For Enterprises' => '#',
        ] as $l => $h): ?>
        <a href="<?= $h ?>" class="bf-footer-link"><?= $l ?></a>
        <?php endforeach; ?>
      </div>

      <!-- Company -->
      <div class="col-6 col-sm-4 col-lg-2">
        <div class="bf-footer-heading">Company</div>
        <?php foreach ([
          'About bildfie' => '#',
          'Careers' => '#',
          'Press & Media' => '#',
          'Blog & Insights' => '#',
          'Partner Program' => '#',
          'Contact Us' => '#',
          'Sitemap' => '#',
        ] as $l => $h): ?>
        <a href="<?= $h ?>" class="bf-footer-link"><?= $l ?></a>
        <?php endforeach; ?>
      </div>

      <!-- Newsletter + trust -->
      <div class="col-12 col-lg-3">
        <div class="bf-footer-heading">Stay Updated</div>
        <p style="font-size:12.5px;color:rgba(255,255,255,.4);margin-bottom:14px;line-height:1.6;">Market prices, new listings, regulatory updates and platform news — straight to your inbox, weekly.</p>
        <form class="bf-news-signup" action="#" onsubmit="return false;">
          <div class="bf-news-field">
            <i class="bi bi-envelope-at"></i>
            <input type="email" placeholder="Enter your email" aria-label="Email address">
            <button type="submit"><span class="d-none d-sm-inline">Subscribe</span><i class="bi bi-send-fill"></i></button>
          </div>
          <div class="bf-news-note"><i class="bi bi-shield-check"></i> Weekly digest · No spam, unsubscribe anytime.</div>
        </form>
      </div>

    </div>

    <!-- Bottom bar -->
    <div class="bf-footer-bottom-row" style="border-top:1px solid rgba(255,255,255,.07);padding-top:24px;margin-top:48px;display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:16px;">
      <span style="font-size:11.5px;color:rgba(255,255,255,.22);">&copy; <?= date('Y') ?> bildfie · A Remissionary Studio product · All rights reserved.</span>
      <div class="d-flex flex-wrap gap-4">
        <?php foreach (['Privacy Policy'=>'#','Terms of Service'=>'#','Cookie Policy'=>'#','Accessibility'=>'#','Security'=>'#'] as $l=>$h): ?>
        <a href="<?= $h ?>" style="font-size:11.5px;color:rgba(255,255,255,.25);text-decoration:none;transition:color .15s;" onmouseover="this.style.color='rgba(255,255,255,.55)'" onmouseout="this.style.color='rgba(255,255,255,.25)'"><?= $l ?></a>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</footer>

<!-- Cookie consent (lower third) -->
<div id="bfCookie" class="bf-cookie" style="display:none;" role="dialog" aria-label="Cookie consent">
  <div class="bf-cookie-card">
    <div class="bf-cookie-ic"><i class="bi bi-cookie"></i></div>
    <div class="bf-cookie-body">
      <div class="bf-cookie-title">We value your privacy</div>
      <p class="bf-cookie-text">We use cookies to improve your experience, analyse traffic and personalise content. <a href="#">Learn more</a>.</p>
    </div>
    <div class="bf-cookie-actions">
      <button type="button" class="bf-cookie-btn ghost" data-cookie="reject">Reject</button>
      <button type="button" class="bf-cookie-btn solid" data-cookie="accept">Accept</button>
    </div>
  </div>
</div>
<script>
(function(){
  var KEY='bf_cookie_consent', box=document.getElementById('bfCookie');
  if(!box) return;
  try { if(localStorage.getItem(KEY)) return; } catch(_){}
  box.style.display='flex';
  box.querySelectorAll('[data-cookie]').forEach(function(b){
    b.addEventListener('click', function(){
      try { localStorage.setItem(KEY, b.getAttribute('data-cookie')); } catch(_){}
      box.style.display='none';
    });
  });
})();
</script>
