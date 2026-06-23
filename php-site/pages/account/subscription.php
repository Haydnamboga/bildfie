<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/auth.php';
$page_title = 'Pricing & Plans';
$nav = '';
?>
<?php include __DIR__ . '/../../includes/head.php'; ?>
<?php include __DIR__ . '/../../includes/navbar.php'; ?>

<div style="background:var(--surface);border-bottom:1px solid var(--line);padding:48px 0 36px;text-align:center;">
  <div class="container" style="max-width:640px;">
    <div class="bf-section-eyebrow mb-3" style="justify-content:center;"><i class="bi bi-credit-card" style="color:#c0392b;"></i> Pricing & Plans</div>
    <h1 style="font-size:clamp(26px,4vw,38px);font-weight:900;color:var(--ink);margin:0 0 12px;">Simple, transparent pricing</h1>
    <p style="font-size:14px;color:var(--ink-3);margin:0;">Start free. Upgrade when you need more reach, verifications or project tools.</p>
    <!-- Toggle -->
    <div style="display:inline-flex;background:var(--white);border:1px solid var(--line);border-radius:999px;padding:4px;gap:4px;margin-top:24px;">
      <button id="toggleMonthly" onclick="setToggle('monthly')" style="background:#1e3a5f;color:#fff;border:none;border-radius:999px;padding:8px 24px;font-size:12.5px;font-weight:700;cursor:pointer;">Monthly</button>
      <button id="toggleAnnual"  onclick="setToggle('annual')"  style="background:transparent;color:var(--ink-3);border:none;border-radius:999px;padding:8px 24px;font-size:12.5px;font-weight:700;cursor:pointer;">Annual <span style="font-size:10px;background:#dcfce7;color:#166534;padding:1px 6px;border-radius:4px;margin-left:4px;">Save 20%</span></button>
    </div>
  </div>
</div>

<div class="container" style="padding-top:48px;padding-bottom:72px;max-width:1100px;">
  <div class="row g-3 justify-content-center">
    <?php
    $plans = [
      ['Free','KES 0','0',     'Get started on bildfie','bi-person','var(--surface)','var(--ink)',false,[
        'Profile listing (1 trade)','Up to 5 bid applications/mo','Basic project posting','Community forum access','Email support',
      ],'Sign up free','/pages/auth/register.php'],
      ['Pro','KES 2,500','2,500','For active professionals','bi-star','#1e3a5f','#fff',true,[
        'Everything in Free','Unlimited bid applications','Priority search placement','NCA verification badge','3 active project posts','Client review system','Analytics dashboard','WhatsApp alerts',
      ],'Start Pro trial','/pages/auth/register.php'],
      ['Business','KES 8,000','8,000','For firms & contractors','bi-building','#c0392b','#fff',false,[
        'Everything in Pro','Unlimited project posts','Team accounts (up to 10)','API access & integrations','Dedicated account manager','Branded company profile','Invoice & escrow tools','Custom reporting',
      ],'Contact sales','#'],
    ];
    foreach ($plans as [$name,$price,$raw,$tagline,$icon,$bg,$col,$popular,$features,$cta,$link]): ?>
    <div class="col-md-4">
      <div style="background:<?= $bg ?>;border:<?= $popular?'2px solid #1e3a5f':'1px solid var(--line)' ?>;border-radius:20px;padding:32px 28px;height:100%;display:flex;flex-direction:column;position:relative;">
        <?php if ($popular): ?>
        <div style="position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:#1e3a5f;color:#fff;font-size:9.5px;font-weight:800;letter-spacing:.1em;padding:4px 14px;border-radius:999px;">MOST POPULAR</div>
        <?php endif; ?>
        <div style="width:40px;height:40px;border-radius:12px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
          <i class="bi <?= $icon ?>" style="font-size:18px;color:<?= $col ?>;"></i>
        </div>
        <div style="font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:<?= $col ?>;opacity:.6;margin-bottom:4px;"><?= $name ?></div>
        <div style="font-size:32px;font-weight:900;color:<?= $col ?>;line-height:1;margin-bottom:4px;"><?= $price ?><span style="font-size:13px;font-weight:500;opacity:.6;">/mo</span></div>
        <div style="font-size:12px;color:<?= $col ?>;opacity:.55;margin-bottom:24px;"><?= $tagline ?></div>
        <ul style="list-style:none;padding:0;margin:0 0 28px;flex:1;">
          <?php foreach ($features as $f): ?>
          <li style="display:flex;align-items:flex-start;gap:8px;font-size:12.5px;color:<?= $col ?>;margin-bottom:10px;">
            <i class="bi bi-check-circle-fill" style="color:<?= $popular?'#60a5fa':($bg==='var(--surface)'?'#22c55e':'#f87171') ?>;flex-shrink:0;margin-top:1px;"></i><?= $f ?>
          </li>
          <?php endforeach; ?>
        </ul>
        <a href="<?= $link ?>" style="display:block;text-align:center;background:<?= $popular?'#fff':($bg==='var(--surface)'?'#1e3a5f':'rgba(255,255,255,.15)') ?>;color:<?= $popular?'#1e3a5f':'#fff' ?>;border:<?= $bg==='var(--surface)'?'none':'1px solid rgba(255,255,255,.2)' ?>;padding:13px;border-radius:10px;font-size:13px;font-weight:700;text-decoration:none;transition:opacity .15s;"><?= $cta ?></a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Feature comparison table -->
  <div style="margin-top:60px;">
    <h2 style="font-size:20px;font-weight:800;color:var(--ink);text-align:center;margin-bottom:32px;">Full feature comparison</h2>
    <div style="border:1px solid var(--line);border-radius:16px;overflow:hidden;">
      <table style="width:100%;border-collapse:collapse;">
        <thead>
          <tr style="background:var(--surface);">
            <th style="padding:14px 20px;text-align:left;font-size:12px;font-weight:700;color:var(--ink-3);">Feature</th>
            <?php foreach (['Free','Pro','Business'] as $p): ?>
            <th style="padding:14px 20px;text-align:center;font-size:12px;font-weight:800;color:var(--ink);"><?= $p ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ([
            ['Profile listing',             '1 trade','Unlimited','Unlimited + featured'],
            ['Bid applications/month',      '5','Unlimited','Unlimited'],
            ['Active project posts',        '1','3','Unlimited'],
            ['NCA verification badge',      '✗','✓','✓'],
            ['Analytics dashboard',         '✗','✓','✓ Advanced'],
            ['Team accounts',               '✗','1','Up to 10'],
            ['API access',                  '✗','✗','✓'],
            ['Escrow & invoicing',          '✗','✓','✓ Advanced'],
            ['Account manager',             '✗','✗','Dedicated'],
            ['Support',                     'Email','Email + WhatsApp','Priority 24/7'],
          ] as $i => [$feat,$free,$pro,$biz]): ?>
          <tr style="border-top:1px solid var(--line);<?= $i%2===0?'background:var(--white)':'' ?>">
            <td style="padding:12px 20px;font-size:12.5px;color:var(--ink);"><?= $feat ?></td>
            <?php foreach ([$free,$pro,$biz] as $val): ?>
            <td style="padding:12px 20px;text-align:center;font-size:12px;color:<?= $val==='✓'?'#22c55e':($val==='✗'?'var(--ink-4)':'var(--ink)') ?>;font-weight:<?= in_array($val,['✓','✗'])?'700':'500' ?>;"><?= $val ?></td>
            <?php endforeach; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- FAQ -->
  <div style="margin-top:60px;max-width:680px;margin-left:auto;margin-right:auto;">
    <h2 style="font-size:20px;font-weight:800;color:var(--ink);text-align:center;margin-bottom:28px;">Frequently asked questions</h2>
    <?php foreach ([
      ['Can I switch plans anytime?','Yes — upgrade or downgrade at any time. Changes take effect at the start of your next billing cycle.'],
      ['Is there a free trial?','The Pro plan comes with a 14-day free trial. No credit card required to start.'],
      ['What payment methods are accepted?','M-Pesa, Visa, Mastercard and bank transfer. Annual plans can also be invoiced.'],
      ['Do you offer NGO / charity discounts?','Yes. Registered NGOs get 40% off any paid plan. Contact us with your registration certificate.'],
    ] as [$q,$a]): ?>
    <div style="border-bottom:1px solid var(--line);padding:18px 0;">
      <div style="font-size:13.5px;font-weight:700;color:var(--ink);margin-bottom:6px;"><?= $q ?></div>
      <div style="font-size:13px;color:var(--ink-3);line-height:1.7;"><?= $a ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<script>
function setToggle(mode) {
  var m = document.getElementById('toggleMonthly'), a = document.getElementById('toggleAnnual');
  m.style.background = mode==='monthly'?'#1e3a5f':'transparent';
  m.style.color      = mode==='monthly'?'#fff':'var(--ink-3)';
  a.style.background = mode==='annual' ?'#1e3a5f':'transparent';
  a.style.color      = mode==='annual' ?'#fff':'var(--ink-3)';
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
<?php include __DIR__ . '/../../includes/scripts.php'; ?>
