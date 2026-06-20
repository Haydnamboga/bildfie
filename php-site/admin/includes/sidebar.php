<?php $ap = $ap ?? ''; $admin = current_admin(); ?>
<aside class="adm-sidebar" id="admSidebar">

  <a href="/admin/index.php" class="adm-brand">
    <i class="bi bi-building-fill-up" style="font-size:22px;"></i>
    <div>
      <div class="logo">bildfie</div>
      <span class="tag">Admin CRM</span>
    </div>
  </a>

  <nav class="adm-nav">

    <a href="/admin/index.php" class="adm-link <?= $ap==='dashboard'?'active':'' ?>" style="margin-bottom:4px;">
      <i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span>
    </a>

    <a href="/admin/users.php" class="adm-link <?= $ap==='users'?'active':'' ?>" style="margin-bottom:4px;">
      <i class="bi bi-people-fill"></i><span>Users</span>
    </a>

    <a href="/admin/companies.php" class="adm-link <?= $ap==='companies'?'active':'' ?>" style="margin-bottom:4px;">
      <i class="bi bi-buildings"></i><span>Companies</span>
    </a>

    <?php
    // [key, label, lead-icon, default-open, group-count, [ [key,icon,label,href,chip] ] ]
    $groups = [
      ['ai','AI Hub','bi-cpu',false,'NEW',[
        ['ai_overview','bi-stars','AI Overview','/admin/ai.php',''],
        ['ai_match','bi-diagram-2','Smart Matching','#',''],
        ['ai_fraud','bi-shield-exclamation','Fraud & Risk','#',''],
        ['ai_mod','bi-eye','Content Moderation','#',''],
        ['ai_assist','bi-robot','AI Assistant','#',''],
        ['ai_forecast','bi-graph-up-arrow','Demand Forecasting','#',''],
        ['ai_pricing','bi-tags','Dynamic Pricing','#',''],
        ['ai_usage','bi-speedometer2','Usage & Costs','#',''],
      ]],
      ['crm','CRM','bi-person-vcard',false,'',[
        ['clients','bi-person-vcard','Clients & Associates','#',''],
        ['contacts','bi-person-rolodex','Contacts','#',''],
        ['profiles','bi-person-badge','Customer Profiles','#',''],
        ['leads','bi-funnel','Leads','#','38'],
        ['pipeline','bi-bar-chart-steps','Lead Pipeline (Status)','#',''],
        ['assignment','bi-person-check','Assignments','#',''],
        ['activities','bi-activity','Activities','#',''],
        ['followups','bi-bell','Follow-ups','#',''],
        ['conversion','bi-graph-up-arrow','Conversions','#',''],
      ]],
      ['sales','Sales & Revenue','bi-cash-coin',false,'',[
        ['proposals','bi-file-earmark-text','Proposals','#',''],
        ['quotations','bi-chat-square-quote','Quotations','#',''],
        ['estimates','bi-calculator','Estimates','#',''],
        ['orders','bi-bag','Orders','#','12'],
        ['invoices','bi-receipt','Invoices','#','23'],
        ['payments','bi-credit-card','Payments','#',''],
        ['creditnotes','bi-receipt-cutoff','Credit Notes','#',''],
        ['deliverynotes','bi-truck','Delivery Notes','#',''],
        ['subscriptions','bi-stars','Subscriptions','#',''],
      ]],
      ['ads','Advertising','bi-badge-ad',false,'',[
        ['ads_overview','bi-bar-chart','Ad Overview','/admin/ads.php',''],
        ['ads_campaigns','bi-megaphone','Campaigns','#','214'],
        ['ads_sponsored','bi-stars','Sponsored Listings','#',''],
        ['ads_display','bi-image','Display & Banner','#',''],
        ['ads_search','bi-search','Promoted Search','#',''],
        ['ads_advertisers','bi-building','Advertisers','#',''],
        ['ads_placements','bi-grid-1x2','Placements & Inventory','#',''],
        ['ads_creatives','bi-palette','Creatives','#',''],
        ['ads_targeting','bi-bullseye','Targeting & Audiences','#',''],
        ['ads_perf','bi-graph-up','Ad Performance','#',''],
        ['ads_revenue','bi-cash-coin','Ad Revenue','#',''],
        ['ads_billing','bi-receipt','Advertiser Billing','#',''],
      ]],
      ['procurement','Marketplace & Procurement','bi-bag-check',false,'',[
        ['listings','bi-box-seam','Listings','/admin/listings.php',''],
        ['materials','bi-bricks','Materials','#',''],
        ['services','bi-tools','Services','#',''],
        ['vendors','bi-shop','Vendors','#',''],
        ['supquotes','bi-chat-square-quote','Supplier Quotations','#',''],
        ['comparisons','bi-bar-chart','Comparisons','#',''],
        ['procorders','bi-bag-check','Orders','#',''],
        ['deliveries','bi-truck','Deliveries','#',''],
        ['supperf','bi-speedometer2','Supplier Performance','#',''],
        ['supreviews','bi-star','Reviews','#',''],
        ['vencontracts','bi-file-earmark-ruled','Vendor Contracts','#',''],
      ]],
      ['verticals','Verticals','bi-grid-3x3-gap',false,'',[
        ['v_construction','bi-bricks','Construction','#',''],
        ['v_travel','bi-airplane','Travel & Bookings','#','NEW'],
        ['v_logistics','bi-truck','Logistics & Transport','#',''],
        ['v_equipment','bi-gear-wide-connected','Equipment Hire','#',''],
        ['v_pro','bi-person-workspace','Professional Services','#',''],
        ['v_events','bi-calendar-event','Events & Venues','#',''],
        ['v_add','bi-plus-circle','Add vertical','#',''],
      ]],
      ['projects','Projects & Tasks','bi-kanban',false,'',[
        ['projects','bi-kanban','Projects','#','7'],
        ['milestones','bi-flag','Milestones','#',''],
        ['resources','bi-people','Resources','#',''],
        ['projstatus','bi-list-check','Status','#',''],
        ['tasks','bi-check2-square','Tasks','#','34'],
        ['boards','bi-columns-gap','Boards','#',''],
        ['lists','bi-list-ul','Lists','#',''],
      ]],
      ['financials','Financials','bi-journal-text',false,'',[
        ['expenses','bi-cash-stack','Expenses','#',''],
        ['disbursements','bi-arrow-up-right-circle','Disbursements','#',''],
        ['reimburse','bi-arrow-counterclockwise','Reimbursements','#',''],
        ['ledger','bi-journal-text','Ledger','#',''],
        ['budgeting','bi-pie-chart','Budgeting','#',''],
        ['taxes','bi-percent','Taxes','#',''],
        ['currencies','bi-currency-exchange','Currencies','#',''],
        ['paymodes','bi-wallet2','Payment Modes','#',''],
      ]],
      ['catalog','Catalog & Resources','bi-box-seam',false,'',[
        ['matcatalog','bi-bricks','Materials Catalog','#',''],
        ['svccatalog','bi-tools','Services Catalog','#',''],
        ['categories','bi-tags','Verticals & Services','/admin/catalog.php',''],
        ['pricing','bi-cash','Pricing','#',''],
        ['units','bi-rulers','Units','#',''],
        ['inventory','bi-box-seam','Inventory','#','6'],
        ['sku','bi-upc-scan','SKU','#',''],
        ['media','bi-images','Media & Files','#',''],
        ['documents','bi-folder2','Documents','#',''],
        ['notes','bi-sticky','Notes','#',''],
      ]],
      ['reports','Reports & Analytics','bi-bar-chart-line',false,'',[
        ['salesrep','bi-graph-up','Sales Reports','#',''],
        ['finrep','bi-cash-coin','Financial Reports','#',''],
        ['projrep','bi-kanban','Project Reports','#',''],
        ['taskprod','bi-check2-circle','Task Productivity','#',''],
        ['custinsights','bi-people','Customer Insights','#',''],
        ['leadconv','bi-funnel','Lead Conversion','#',''],
        ['mktanalytics','bi-shop','Marketplace Analytics','#',''],
        ['forecasting','bi-graph-up-arrow','Forecasting','#',''],
        ['customrep','bi-sliders','Custom Reports','#',''],
      ]],
      ['system','System & Administration','bi-gear-wide-connected',false,'',[
        ['roles_users','bi-person-vcard','Roles & Users','/admin/roles.php',''],
        ['team','bi-people','Team & Members','#','68'],
        ['permissions','bi-shield-lock','Permissions','#',''],
        ['departments','bi-diagram-3','Departments','#',''],
        ['collab','bi-chat-dots','Collaboration','#',''],
        ['meetings','bi-camera-video','Meetings','#',''],
        ['activitylog','bi-clipboard-data','Activity Logs','#',''],
        ['tickets','bi-headset','Support Tickets','#','18'],
        ['slas','bi-stopwatch','SLAs','#',''],
        ['integrations','bi-plug','Integrations','#',''],
        ['webhooks','bi-code-slash','API & Webhooks','#',''],
      ]],
    ];
    foreach ($groups as [$gkey,$glabel,$gicon,$gopen,$gcount,$items]):
      $hasActive = false;
      foreach ($items as $it) { if ($ap === $it[0]) { $hasActive = true; break; } }
      $open = $gopen || $hasActive;
    ?>
    <div class="adm-group <?= $open?'open':'' ?>" data-group>
      <button class="adm-group-h" type="button">
        <i class="bi <?= $gicon ?> lead"></i><span><?= $glabel ?></span>
        <?php if ($gcount): ?><span class="gcount"><?= $gcount ?></span><?php endif; ?>
        <i class="bi bi-chevron-right chev"></i>
      </button>
      <div class="adm-group-body">
        <?php foreach ($items as [$k,$i,$l,$h,$chip]): ?>
        <a href="<?= $h ?>" class="adm-link <?= $ap===$k?'active':'' ?>">
          <i class="bi <?= $i ?>"></i><span><?= $l ?></span>
          <?php if ($chip): ?><span class="chip"><?= $chip ?></span><?php endif; ?>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>

  </nav>

  <div class="adm-user">
    <img src="<?= htmlspecialchars($admin['photo'] ?? 'https://randomuser.me/api/portraits/women/65.jpg') ?>" alt="">
    <div style="flex:1;min-width:0;">
      <div class="nm text-truncate"><?= htmlspecialchars($admin['name'] ?? 'Owner') ?></div>
      <div class="rl text-truncate"><?= htmlspecialchars($admin['role'] ?? 'Super Admin') ?></div>
    </div>
    <a href="/admin/logout.php" class="so" title="Sign out"><i class="bi bi-box-arrow-right"></i></a>
  </div>

</aside>
