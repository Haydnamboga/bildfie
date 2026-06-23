<?php $sp = $sp ?? ''; $u = current_user(); ?>
<?php include __DIR__ . '/navbar.php'; ?>
<div class="bf-side-backdrop" id="bfSideBackdrop"></div>
<aside class="bf-sidebar" id="bfSidebar">

  <nav class="flex-grow-1 overflow-y-auto py-2 px-2">

    <?php
    // [label, group-icon, [ [key, icon, label, href], … ]]   empty label = plain top-level link.
    // Groups render as a collapsed accordion (one open at a time; the active group opens on load).
    $groups = [
      ['', '', [
        ['dashboard', 'bi-grid-1x2-fill', 'Dashboard', '/pages/dashboard/index.php'],
      ]],
      ['Projects', 'bi-kanban', [
        ['projects',   'bi-kanban',              'Projects',   '/pages/projects/index.php'],
        ['tasks',      'bi-check2-square',       'Tasks',      '#'],
        ['milestones', 'bi-flag',                'Milestones', '#'],
        ['timelines',  'bi-bar-chart-steps',     'Timelines',  '#'],
        ['kanban',     'bi-layout-three-columns','Kanban',     '#'],
        ['team',       'bi-people',              'Teams',      '/pages/team/index.php'],
      ]],
      ['Sales', 'bi-cash-coin', [
        ['orders',        'bi-bag',                    'Orders',        '#'],
        ['proposals',     'bi-file-earmark-text',      'Proposals',     '/pages/sales/proposals.php'],
        ['estimates',     'bi-calculator',             'Estimates',     '/pages/sales/estimates.php'],
        ['contracts',     'bi-file-earmark-ruled',     'Contracts',     '/pages/contracts/index.php'],
        ['invoices',      'bi-receipt',                'Invoices',      '/pages/dashboard/invoices.php'],
        ['credit-notes',  'bi-arrow-counterclockwise', 'Credit Notes',  '/pages/sales/credit-notes.php'],
        ['subscriptions', 'bi-stars',                  'Subscriptions', '/pages/subscriptions/index.php'],
        ['sales-reports', 'bi-graph-up-arrow',         'Sales Reports', '#'],
      ]],
      ['Utilities', 'bi-tools', [
        ['files',       'bi-folder2',            'Files & Documents', '#'],
        ['media',       'bi-images',             'Media',             '#'],
        ['calendar',    'bi-calendar3',          'Calendar',          '#'],
        ['u-contracts', 'bi-file-earmark-ruled', 'Contracts',         '/pages/contracts/index.php'],
        ['u-team',      'bi-people',             'Teams',             '/pages/team/index.php'],
      ]],
      ['Reports & Analytics', 'bi-bar-chart-line', [
        ['exec-dashboard',  'bi-speedometer2',   'Executive Dashboard', '#'],
        ['r-sales',         'bi-graph-up',       'Sales Reports',       '#'],
        ['financial',       'bi-cash-stack',     'Financial Reports',   '#'],
        ['project-reports', 'bi-clipboard-data', 'Project Reports',     '#'],
        ['team-reports',    'bi-people',         'Team Reports',        '#'],
        ['marketing',       'bi-megaphone',      'Marketing Reports',   '#'],
        ['support',         'bi-life-preserver', 'Support Reports',     '#'],
      ]],
    ];
    $opened = false;   // accordion: open only the first group containing the active page
    foreach ($groups as [$label, $gicon, $links]):
      if ($label === ''):
        foreach ($links as [$k,$i,$l,$h]): ?>
        <a href="<?= $h ?>" class="bf-link <?= $sp===$k ? 'active' : '' ?>"><i class="bi <?= $i ?>"></i><span class="bf-sl"><?= $l ?></span></a>
        <?php endforeach;
      else:
        $hasActive = false;
        if (!$opened) { foreach ($links as $lk) { if ($sp === $lk[0]) { $hasActive = true; $opened = true; break; } } } ?>
        <div class="bf-grp <?= $hasActive ? 'open' : '' ?>">
          <button type="button" class="bf-grp-h"><i class="bi <?= $gicon ?> lead"></i><span class="bf-sl"><?= htmlspecialchars($label) ?></span><i class="bi bi-chevron-right chev bf-sl"></i></button>
          <div class="bf-grp-body">
            <?php foreach ($links as [$k,$i,$l,$h]): ?>
            <a href="<?= $h ?>" class="bf-link <?= $sp===$k ? 'active' : '' ?>"><i class="bi <?= $i ?>"></i><span class="bf-sl"><?= $l ?></span></a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif;
    endforeach; ?>

  </nav>

  <div class="px-2 pb-3 pt-2" style="border-top:1px solid rgba(0,0,0,.06);">
    <a href="/pages/account/settings.php" class="bf-link <?= $sp==='settings'?'active':'' ?>"><i class="bi bi-gear"></i><span class="bf-sl">Settings</span></a>
  </div>

  <script>
  (function(){
    document.querySelectorAll('#bfSidebar .bf-grp-h').forEach(function(h){
      h.addEventListener('click', function(){
        var grp = h.parentElement, wasOpen = grp.classList.contains('open');
        document.querySelectorAll('#bfSidebar .bf-grp').forEach(function(g){ g.classList.remove('open'); });
        if (!wasOpen) grp.classList.add('open');
      });
    });
  })();
  </script>

</aside>
