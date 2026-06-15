<div class="adm-verbar">
  <span>bildfie Company &nbsp;|&nbsp; Copyright <?= date('Y') ?></span>
  <span style="font-variant-numeric:tabular-nums;">version 0.0.1</span>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
  var b = document.getElementById('admBurger'), s = document.getElementById('admSidebar');
  if (b && s) {
    b.addEventListener('click', function(e){ e.stopPropagation(); s.classList.toggle('open'); });
    document.addEventListener('click', function(e){
      if (window.innerWidth < 992 && s.classList.contains('open') && !s.contains(e.target) && e.target !== b) s.classList.remove('open');
    });
  }
  // Collapsible nav — single open at a time (accordion); remembers the open one
  var stored; try { stored = localStorage.getItem('adm_group') || ''; } catch (_) { stored = ''; }
  var groups = document.querySelectorAll('.adm-group');
  groups.forEach(function(g){
    var head = g.querySelector('.adm-group-h');
    var label = head ? head.textContent.trim() : '';
    // keep server-rendered open (active section) OR the remembered one
    if (!g.classList.contains('open') && stored && label === stored) g.classList.add('open');
    if (head) head.addEventListener('click', function(){
      var wasOpen = g.classList.contains('open');
      groups.forEach(function(o){ o.classList.remove('open'); });   // collapse all others
      if (!wasOpen) g.classList.add('open');
      try { localStorage.setItem('adm_group', wasOpen ? '' : label); } catch (_) {}
    });
  });
})();
</script>
</body>
</html>
