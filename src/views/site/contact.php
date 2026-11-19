<?php
$old = $_SESSION['old'] ?? []; unset($_SESSION['old']);
$sel = array_map('strval', (array)($old['services'] ?? []));
if (!$sel && $pre && ($ps = service_by_slug($pre))) $sel = [(string)$ps['id']];
$p1 = setting('phone1'); $p2 = setting('phone2'); $wa = setting('whatsapp', $p1);
?>
<section class="phead">
  <div class="wrap">
    <div class="crumbs"><a href="/">Home</a> › Contact</div>
    <h1>Send us your enquiry</h1>
    <p class="lead">Tell us your vehicle and the work needed — we will call you back.</p>
  </div>
</section>
<section class="sec">
  <div class="wrap svc-grid">
    <form class="card pad" method="post" action="/enquiry" enctype="multipart/form-data" id="enquiry">
      <?= csrf_field() ?><input type="text" name="website" class="hp" tabindex="-1" autocomplete="off">
      <h3>Enquiry form</h3>
      <p class="muted">All fields are quick — only name, mobile and service are required.</p>
      <div class="row2">
        <label class="fl"><span>Full name *</span><input name="name" required value="<?= e($old['name'] ?? '') ?>" placeholder="Rahul Boro"></label>
        <label class="fl"><span>Mobile (WhatsApp) *</span><input name="mobile" required inputmode="numeric" value="<?= e($old['mobile'] ?? '') ?>" placeholder="+91 98XXX XXXXX"></label>
      </div>
      <div class="lbl">Which service? * <small class="muted">(select one or more)</small></div>
      <?php foreach (services_grouped() as $g => $list): ?>
        <div class="glabel"><?= e($g) ?></div>
        <div class="pick">
          <?php foreach ($list as $s): ?><label><input type="checkbox" name="services[]" value="<?= $s['id'] ?>" <?= in_array((string)$s['id'], $sel, true) ? 'checked' : '' ?>><span><?= e($s['name']) ?></span></label><?php endforeach; ?>
        </div>
      <?php endforeach; ?>
      <div class="lbl">Vehicle type</div>
      <div class="pick">
        <?php foreach (VEHICLE_TYPES as $i => $v): ?><label><input type="radio" name="vehicle_type" value="<?= e($v) ?>" <?= ($old['vehicle_type'] ?? 'Car / Jeep') === $v ? 'checked' : '' ?>><span><?= e($v) ?></span></label><?php endforeach; ?>
      </div>
      <div class="row2">
        <label class="fl"><span>Vehicle number</span><input name="vehicle_number" value="<?= e($old['vehicle_number'] ?? '') ?>" placeholder="AS 15 C 4521" style="text-transform:uppercase"></label>
        <label class="fl"><span>Insurance / PUC expiry (if known)</span><input type="date" name="expiry" value="<?= e($old['expiry'] ?? '') ?>"></label>
      </div>
      <label class="fl"><span>Message / address (for mechanic, plumber)</span><textarea name="message" rows="3" placeholder="Need insurance renewal and new PUC for my car."><?= e($old['message'] ?? '') ?></textarea></label>
      <label class="upload"><?= icon('upload', 20) ?> <span>Upload RC / old policy (optional) — photos or PDF</span><input type="file" name="docs[]" multiple accept="image/*,application/pdf"></label>
      <div class="filelist" data-for="docs[]"></div>
      <button class="btn btn-red block"><?= icon('send', 16) ?> Submit Enquiry</button>
    </form>
    <aside class="side">
      <div class="card pad">
        <h3>Visit our centre</h3>
        <p><?= e(setting('address_line1')) ?>, <?= e(setting('address_line2')) ?></p>
        <a class="btn btn-outline sm" href="https://maps.google.com/?q=<?= rawurlencode(setting('map_query')) ?>" target="_blank" rel="noopener"><?= icon('map', 15) ?> Get directions</a>
        <hr>
        <p class="ci"><?= icon('phone', 18) ?><span><b>Call</b><a href="<?= tel($p1) ?>"><?= e(fmt_mobile($p1)) ?></a><?php if ($p2): ?> · <a href="<?= tel($p2) ?>"><?= e(fmt_mobile($p2)) ?></a><?php endif; ?></span></p>
        <p class="ci"><?= icon('whatsapp', 18) ?><span><b>WhatsApp</b><a href="<?= wa_link($wa) ?>" target="_blank" rel="noopener"><?= e(fmt_mobile($wa)) ?></a> — send documents directly</span></p>
        <p class="ci"><?= icon('clock', 18) ?><span><b>Timings</b><?= e(setting('timings')) ?></span></p>
      </div>
      <div class="map"><iframe title="Map" loading="lazy" src="https://maps.google.com/maps?q=<?= rawurlencode(setting('map_query')) ?>&z=15&output=embed"></iframe></div>
    </aside>
  </div>
</section>
