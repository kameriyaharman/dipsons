<?php
$cols = ['insurance' => 'insurance_expiry', 'puc' => 'puc_expiry', 'tax' => 'tax_expiry', 'fitness' => 'fitness_expiry', 'permit' => 'permit_expiry'];
$slugs = ['insurance' => 'insurance', 'puc' => 'pollution', 'tax' => 'road-tax', 'fitness' => 'fitness', 'permit' => 'permit'];
?>
<header class="bar"><a href="/app/home" class="ib"><?= icon('back', 22) ?></a><h1>Reminders</h1><a href="/app/vehicle" class="ib"><?= icon('plus', 22) ?></a></header>
<main class="pad">
  <p class="muted small">We remind you on WhatsApp before anything expires.</p>
  <?php if (!$veh): ?>
    <div class="empty"><?= icon('bell', 40) ?><p>Add your vehicle to get free expiry reminders.</p></div>
  <?php endif; ?>
  <?php foreach ($veh as $v): ?>
    <div class="card">
      <div class="row"><b class="big"><?= e(fmt_vehicle($v['number'])) ?></b><a href="/app/vehicle?id=<?= $v['id'] ?>" class="link small">Edit</a></div>
      <small class="muted"><?= e(trim($v['model'] . ' · ' . $v['type'], ' ·')) ?></small>
      <div class="exp">
        <?php $any = false; foreach ($cols as $k => $col): if (!$v[$col]) continue; $any = true; [$cls, $lab] = expiry_state($v[$col]); ?>
          <a href="/app/new?service=<?= $slugs[$k] ?>" class="er"><span><?= e(EXPIRY_KINDS[$k]) ?></span><span><?= e(fmt_date($v[$col])) ?></span><span class="pill <?= $cls ?>"><?= e($lab) ?></span></a>
        <?php endforeach; ?>
        <?php if (!$any): ?><a class="link small" href="/app/vehicle?id=<?= $v['id'] ?>">+ Add expiry dates</a><?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
  <a class="btn outline block" href="/app/vehicle"><?= icon('plus', 16) ?> Add vehicle</a>
</main>
