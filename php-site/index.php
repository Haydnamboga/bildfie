<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/auth.php';
$page_title = 'Find and hire verified construction professionals';
$nav = 'home';
?>
<?php include __DIR__ . '/includes/head.php'; ?>
<?php include __DIR__ . '/includes/navbar.php'; ?>

<!-- ═══════════════════════════════════════════
     HOMEPAGE — honest hero (developer spec §2.3)
     No statistics. No activity feed. No accreditation logos.
═══════════════════════════════════════════ -->
<section class="bf-home-hero">
  <div class="container">
    <div class="bf-home-hero-inner">
      <h1 class="bf-home-headline">Find and hire verified construction professionals.</h1>
      <p class="bf-home-sub">Post a project. Contractors bid. Pay only when work is approved.</p>
      <div class="bf-home-cta">
        <a href="/pages/projects/create.php" class="bf-home-btn bf-home-btn-primary">Post a Project</a>
        <a href="/pages/auth/register.php" class="bf-home-btn bf-home-btn-secondary">Join as a Professional</a>
      </div>
    </div>
  </div>
</section>

<style>
  /* Mobile-first (developer spec §9 — "Mobile first") */
  .bf-home-hero {
    min-height: calc(100vh - 64px);
    display: flex;
    align-items: center;
    background: var(--white, #ffffff);
    padding: 48px 0;
  }
  .bf-home-hero-inner {
    max-width: 720px;
    margin: 0 auto;
    text-align: center;
    padding: 0 4px;
  }
  .bf-home-headline {
    font-size: 32px;
    line-height: 1.15;
    font-weight: 800;
    color: var(--ink, #0f172a);
    margin: 0 0 18px;
    letter-spacing: -0.02em;
  }
  .bf-home-sub {
    font-size: 17px;
    line-height: 1.5;
    color: var(--ink-4, #475569);
    margin: 0 0 32px;
  }
  .bf-home-cta {
    display: flex;
    flex-direction: column;
    gap: 12px;
    align-items: stretch;
  }
  .bf-home-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 15px 28px;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 700;
    text-decoration: none;
    transition: transform .06s ease, box-shadow .2s ease, background .2s ease;
  }
  .bf-home-btn:active { transform: translateY(1px); }
  .bf-home-btn-primary {
    background: var(--accent, #c0392b);
    color: #ffffff;
  }
  .bf-home-btn-primary:hover { box-shadow: 0 8px 22px rgba(192,57,43,.28); color:#fff; }
  .bf-home-btn-secondary {
    background: transparent;
    color: var(--ink, #0f172a);
    border: 2px solid var(--line, #e2e8f0);
  }
  .bf-home-btn-secondary:hover { border-color: var(--ink, #0f172a); color: var(--ink, #0f172a); }

  /* Tablet / desktop: buttons side by side, larger type */
  @media (min-width: 576px) {
    .bf-home-cta { flex-direction: row; justify-content: center; }
    .bf-home-btn { min-width: 200px; }
  }
  @media (min-width: 768px) {
    .bf-home-headline { font-size: 48px; margin-bottom: 22px; }
    .bf-home-sub { font-size: 20px; margin-bottom: 40px; }
  }
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
