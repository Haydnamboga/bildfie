<?php include __DIR__ . '/../components/modals/engagement-modals.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= ASSETS_URL ?>/js/app.js"></script>
<?php if (!empty($extra_js)): foreach ($extra_js as $src): ?>
<script src="<?= htmlspecialchars($src) ?>"></script>
<?php endforeach; endif; ?>
<script>
/* Any element with data-href becomes clickable (cards/rows). Inner links & form fields still work. */
document.addEventListener('click', function(e){
  var el = e.target.closest('[data-href]');
  if (!el || e.target.closest('a, input, select, textarea, label')) return;
  var u = el.getAttribute('data-href'); if (u) window.location.href = u;
});
</script>
</body>
</html>
