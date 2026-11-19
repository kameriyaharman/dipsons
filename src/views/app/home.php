<?php
$c = app_customer();
$top = array_slice(services_all(), 0, 11);
$kindName = EXPIRY_KINDS;
?>
<header class="ghead">
  <div class="row"><div><small>Namaskar,</small><h1><?= e($c['name'] ?: 'Welcome') ?></h1></div><a href="/app/reminders" class="bell"><?= icon('bell', 22) ?></a></div>
  <?php if ($veh): $v = $alert ? $alert['veh'] : $veh[0]; ?>
  <a class="vcard" href="/app/reminders">
    <span class="vic"><?= icon(VEHICLE_ICONS[$v['type']] ?? 'car', 24) ?></span>
    <span class="vinfo"><b><?= e(fmt_vehicle($v['number'])) ?></b><small><?= e(trim(($v['model'] ? $v['model'] . ' · ' : '') . $v['type'], ' ·')) ?></small></span>
    <?php if ($alert): $d = $alert['days']; ?>
      <span class="due <?= $d < 0 ? 'exp' : ($d <= 30 ? 'soon' : 'ok') ?>"><?= e($kindName[$alert['kind']]) ?> <?= $d < 0 ? 'expired' : ($d === 0 ? 'today' : 'in ' . $d . ' days') ?></span>
    <?php endif; ?>
  </a>
  <?php else: ?>
  <a class="vcard add" href="/app/vehicle"><span class="vic"><?= icon('plus', 22) ?></span><span class="vinfo"><b>Add your vehicle</b><small>Get free expiry reminders</small></span></a>
  <?php endif; ?>
</header>
<main class="pad">
  <h3 class="sh">Services</h3>
  <div class="sg">
    <?php foreach ($top as $s): ?><a href="/app/new?service=<?= e($s['slug']) ?>"><span class="<?= e($s['color']) ?>"><?= icon($s['icon'], 22) ?></span><b><?= e($s['name']) ?></b></a><?php endforeach; ?>
    <a href="/app/services"><span class="green"><?= icon('grid', 22) ?></span><b>All services</b></a>
  </div>

  <?php if ($alert && $alert['days'] <= 30): $k = $alert['kind']; $slug = ['insurance' => 'insurance', 'puc' => 'pollution', 'tax' => 'road-tax', 'fitness' => 'fitness', 'permit' => 'permit'][$k]; ?>
  <a class="promo" href="/app/new?service=<?= $slug ?>">
    <div><b>Renew <?= e(strtolower($kindName[$k])) ?> <?= $alert['days'] <= 0 ? 'now' : 'today' ?></b><small><?= e(fmt_vehicle($alert['veh']['number'])) ?> · expires <?= e(fmt_date($alert['veh'][['insurance' => 'insurance_expiry', 'puc' => 'puc_expiry', 'tax' => 'tax_expiry', 'fitness' => 'fitness_expiry', 'permit' => 'permit_expiry'][$k]])) ?></small></div>
    <span class="pbtn">Get quote</span>
  </a>
  <?php else: ?>
  <a class="promo" href="/app/new?service=insurance">
    <div><b>Renew insurance today</b><small>Best quotes · policy on WhatsApp</small></div><span class="pbtn">Get quote</span>
  </a>
  <?php endif; ?>

  <?php if ($open): ?><a class="rowlink" href="/app/enquiries?f=open"><?= icon('clipboard', 20) ?><span><b><?= $open ?> open enquir<?= $open > 1 ? 'ies' : 'y' ?></b><small>Track live status</small></span><?= icon('chevron', 18) ?></a><?php endif; ?>
  <a class="rowlink" href="<?= wa_link(setting('whatsapp', setting('phone1')), 'Namaskar, I am ' . $c['name'] . '.') ?>" target="_blank" rel="noopener"><?= icon('whatsapp', 20) ?><span><b>Chat on WhatsApp</b><small>Send documents directly</small></span><?= icon('chevron', 18) ?></a>
  <a class="rowlink" href="<?= tel(setting('phone1')) ?>"><?= icon('phone', 20) ?><span><b>Call Dipsons</b><small><?= e(fmt_mobile(setting('phone1'))) ?></small></span><?= icon('chevron', 18) ?></a>
</main>
