<?php $p1 = setting('phone1'); ?>
<section class="phead">
  <div class="wrap">
    <div class="crumbs"><a href="/">Home</a> › <a href="/services">Services</a> › <?= e($svc['name']) ?></div>
    <div class="phead-row">
      <span class="sico big <?= e($svc['color']) ?>"><?= icon($svc['icon'], 30) ?></span>
      <div><h1><?= e($svc['name']) ?></h1><p class="lead"><?= e($svc['tagline'] ?: $svc['short_desc']) ?></p></div>
    </div>
    <div class="chips"><?php foreach (csv_list($svc['tags']) as $t): ?><span class="chip"><?= e($t) ?></span><?php endforeach; ?></div>
  </div>
</section>
<section class="sec">
  <div class="wrap svc-grid">
    <div>
      <div class="card pad">
        <h3>What we do</h3>
        <ul class="ticks"><?php foreach (lines($svc['what_we_do']) as $l): ?><li><?= icon('check', 16) ?> <?= e($l) ?></li><?php endforeach; ?></ul>
      </div>
      <?php if ($docs = lines($svc['documents'])): ?>
      <div class="card pad">
        <h3>Documents needed</h3>
        <div class="docchips"><?php foreach ($docs as $l): ?><span><?= icon('doc', 15) ?> <?= e($l) ?></span><?php endforeach; ?></div>
        <p class="muted small">Send photos on WhatsApp — we tell you if anything else is needed.</p>
      </div>
      <?php endif; ?>
      <?php if ($svc['fee_note']): ?><div class="card pad"><h3>Charges</h3><p><?= e($svc['fee_note']) ?></p></div><?php endif; ?>
      <?php if ($svc['category_slug'] !== 'repair'): ?>
      <div class="card pad">
        <h3>Vehicles covered</h3>
        <div class="vgrid small"><?php foreach (array_slice(VEHICLE_TYPES, 0, 6) as $v): ?><div class="vcard"><?= icon(VEHICLE_ICONS[$v], 22) ?><b><?= e($v) ?></b></div><?php endforeach; ?></div>
      </div>
      <?php endif; ?>
      <div class="card pad">
        <h3>Other services</h3>
        <div class="chips"><?php foreach (services_all() as $s) if ($s['id'] !== $svc['id']): ?><a class="chip" href="/<?= e($s['slug']) ?>"><?= icon($s['icon'], 14) ?> <?= e($s['name']) ?></a><?php endif; ?></div>
      </div>
    </div>
    <aside class="side">
      <?= view('site/_quick_form', ['heading' => 'Get ' . strtolower($svc['name']) . ' quote', 'selected' => $svc['id']]) ?>
      <a class="card pad talk" href="<?= tel($p1) ?>"><?= icon('phone', 22) ?><span>Prefer to talk?<b><?= e(fmt_mobile($p1)) ?></b></span></a>
    </aside>
  </div>
</section>
