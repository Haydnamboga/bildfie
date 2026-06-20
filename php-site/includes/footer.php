<footer style="background:#0d0d0d;color:#9b9b9b;padding:64px 0 32px;">
  <div class="container">
    <div class="row g-4 mb-5">
      <!-- Brand column -->
      <div class="col-12 col-md-4">
        <div class="d-flex align-items-center gap-2 mb-3">
          <i class="bi bi-building-fill-up" style="font-size:20px;color:#c9a84c;"></i>
          <span style="font-weight:800;font-size:1.1rem;color:#ffffff;letter-spacing:-.5px;">bildfie</span>
        </div>
        <p style="font-size:.875rem;line-height:1.7;max-width:280px;color:#9b9b9b;">
          Build Smarter. Connect Better.<br>
          Africa's construction professional marketplace.
        </p>
        <div class="d-flex gap-3 mt-3">
          <a href="#" style="color:#6b6b6b;font-size:1.1rem;" title="Twitter/X"><i class="bi bi-twitter-x"></i></a>
          <a href="#" style="color:#6b6b6b;font-size:1.1rem;" title="LinkedIn"><i class="bi bi-linkedin"></i></a>
          <a href="#" style="color:#6b6b6b;font-size:1.1rem;" title="Instagram"><i class="bi bi-instagram"></i></a>
          <a href="#" style="color:#6b6b6b;font-size:1.1rem;" title="Facebook"><i class="bi bi-facebook"></i></a>
        </div>
      </div>

      <!-- For Clients -->
      <div class="col-6 col-md-2">
        <h6 style="color:#ffffff;font-size:.75rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:16px;">For Clients</h6>
        <ul class="list-unstyled" style="font-size:.875rem;">
          <li class="mb-2"><a href="/pages/projects/create.php" style="color:#9b9b9b;text-decoration:none;">Post a Project</a></li>
          <li class="mb-2"><a href="#" style="color:#9b9b9b;text-decoration:none;">How it works</a></li>
          <li class="mb-2"><a href="/pages/professionals/" style="color:#9b9b9b;text-decoration:none;">Browse Professionals</a></li>
          <li class="mb-2"><a href="#" style="color:#9b9b9b;text-decoration:none;">Pricing</a></li>
        </ul>
      </div>

      <!-- For Professionals -->
      <div class="col-6 col-md-2">
        <h6 style="color:#ffffff;font-size:.75rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:16px;">For Pros</h6>
        <ul class="list-unstyled" style="font-size:.875rem;">
          <li class="mb-2"><a href="/pages/auth/register.php" style="color:#9b9b9b;text-decoration:none;">Join as a Pro</a></li>
          <li class="mb-2"><a href="/pages/dashboard/" style="color:#9b9b9b;text-decoration:none;">Dashboard</a></li>
          <li class="mb-2"><a href="/pages/account/" style="color:#9b9b9b;text-decoration:none;">Build Your Profile</a></li>
          <li class="mb-2"><a href="#" style="color:#9b9b9b;text-decoration:none;">Success Stories</a></li>
        </ul>
      </div>

      <!-- Company -->
      <div class="col-6 col-md-2">
        <h6 style="color:#ffffff;font-size:.75rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:16px;">Company</h6>
        <ul class="list-unstyled" style="font-size:.875rem;">
          <li class="mb-2"><a href="#" style="color:#9b9b9b;text-decoration:none;">About</a></li>
          <li class="mb-2"><a href="#" style="color:#9b9b9b;text-decoration:none;">Contact</a></li>
          <li class="mb-2"><a href="#" style="color:#9b9b9b;text-decoration:none;">Blog</a></li>
          <li class="mb-2"><a href="#" style="color:#9b9b9b;text-decoration:none;">Careers</a></li>
        </ul>
      </div>

      <!-- Legal -->
      <div class="col-6 col-md-2">
        <h6 style="color:#ffffff;font-size:.75rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin-bottom:16px;">Legal</h6>
        <ul class="list-unstyled" style="font-size:.875rem;">
          <li class="mb-2"><a href="#" style="color:#9b9b9b;text-decoration:none;">Privacy Policy</a></li>
          <li class="mb-2"><a href="#" style="color:#9b9b9b;text-decoration:none;">Terms of Service</a></li>
          <li class="mb-2"><a href="#" style="color:#9b9b9b;text-decoration:none;">Cookie Policy</a></li>
        </ul>
      </div>
    </div>

    <div style="border-top:1px solid #1e1e1e;padding-top:24px;display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;">
      <span style="font-size:.8rem;">&copy; <?= date('Y') ?> bildfie. All rights reserved.</span>
      <span style="font-size:.8rem;">Made with <i class="bi bi-heart-fill" style="color:#c0392b;font-size:.7rem;"></i> in Nairobi, Kenya.</span>
    </div>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= ASSETS_URL ?>/js/app.js"></script>
<?php if (!empty($extra_js)) echo $extra_js; ?>
</body>
</html>
