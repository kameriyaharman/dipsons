<?php
$c = app_customer();
$preId = null;
if ($pre) foreach (services_all() as $s) if ($s['slug'] === $pre) $preId = $s['id'];
$v0 = $veh[0] ?? null;
?>
<form method="post" enctype="multipart/form-data" class="wizard" id="wiz">
<?= csrf_field() ?>
<header class="bar"><a href="/app/home" class="ib" data-back><?= icon('back', 22) ?></a><h1>New enquiry</h1><span class="ib"></span></header>
<div class="stepper"><div class="sbar"><i style="width:25%"></i></div><small>Step <b data-stepno>1</b> of 4</small></div>

<section class="step on pad" data-step="1">
  <h2>Select service(s)</h2>
  <p class="muted small">You can pick more than one.</p>
  <?php foreach ($groups as $g => $list): ?>
    <div class="gl"><?= e($g) ?></div>
    <div class="pick">
      <?php foreach ($list as $s): ?><label><input type="checkbox" name="services[]" value="<?= $s['id'] ?>" data-name="<?= e($s['name']) ?>" <?= (int)$preId === (int)$s['id'] ? 'checked' : '' ?>><span><?= icon($s['icon'], 15) ?> <?= e($s['name']) ?></span></label><?php endforeach; ?>
    </div>
  <?php endforeach; ?>
</section>

<section class="step pad" data-step="2">
  <h2>Vehicle details</h2>
  <?php if ($veh): ?>
    <div class="gl">Your vehicles</div>
    <div class="pick">
      <?php foreach ($veh as $v): ?><label><input type="radio" name="pick_vehicle" data-v='<?= e(json_encode(['n' => fmt_vehicle($v['number']), 't' => $v['type'], 'm' => $v['model'], 'i' => $v['insurance_expiry'], 'p' => $v['puc_expiry']])) ?>'><span><?= e(fmt_vehicle($v['number'])) ?></span></label><?php endforeach; ?>
    </div>
  <?php endif; ?>
  <div class="gl">Vehicle type</div>
  <div class="pick">
    <?php foreach (VEHICLE_TYPES as $t): ?><label><input type="radio" name="vehicle_type" value="<?= e($t) ?>" <?= ($v0['type'] ?? 'Car / Jeep') === $t ? 'checked' : '' ?>><span><?= e($t) ?></span></label><?php endforeach; ?>
  </div>
  <label class="fl"><span>Vehicle number</span><input name="vehicle_number" value="<?= e($v0 ? fmt_vehicle($v0['number']) : '') ?>" placeholder="AS 15 C 4521" style="text-transform:uppercase"></label>
  <label class="fl"><span>Model (optional)</span><input name="vehicle_model" value="<?= e($v0['model'] ?? '') ?>" placeholder="Maruti Swift"></label>
  <div class="row2">
    <label class="fl"><span>Insurance expiry</span><input type="date" name="insurance_expiry" value="<?= e($v0['insurance_expiry'] ?? '') ?>"></label>
    <label class="fl"><span>PUC expiry</span><input type="date" name="puc_expiry" value="<?= e($v0['puc_expiry'] ?? '') ?>"></label>
  </div>
  <p class="muted small">For mechanic / plumber, add your address in the next step.</p>
</section>

<section class="step pad" data-step="3">
  <h2>Upload documents</h2>
  <p class="muted small">Optional — you can also send later on WhatsApp.</p>
  <?php foreach (['rc' => 'RC (front & back)', 'policy' => 'Previous insurance policy', 'id' => 'Aadhaar / PAN', 'other' => 'Other document'] as $k => $lab): ?>
    <label class="up">
      <span class="upi"><?= icon('upload', 20) ?></span>
      <span class="upt"><b><?= e($lab) ?></b><small data-st>Tap to take photo or upload</small></span>
      <input type="file" name="<?= $k ?>[]" accept="image/*,application/pdf" multiple>
    </label>
  <?php endforeach; ?>
  <label class="fl"><span>Message / address</span><textarea name="message" rows="3" placeholder="Want zero-dep cover this time"></textarea></label>
</section>

<section class="step pad" data-step="4">
  <h2>Review &amp; submit</h2>
  <div class="review">
    <div><small>Services</small><b data-r="services">—</b></div>
    <div><small>Vehicle</small><b data-r="vehicle">—</b></div>
    <div><small>Documents</small><b data-r="docs">None</b></div>
    <div><small>Contact</small><b><?= e(($c['name'] ?: 'You') . ' · ' . fmt_mobile($c['mobile'])) ?></b></div>
  </div>
  <div class="gl">Preferred</div>
  <div class="pick">
    <?php foreach (['Call back today', 'Call back tomorrow', 'WhatsApp only'] as $i => $p): ?><label><input type="radio" name="preferred" value="<?= e($p) ?>" <?= $i === 0 ? 'checked' : '' ?>><span><?= e($p) ?></span></label><?php endforeach; ?>
  </div>
  <label class="switch"><input type="checkbox" name="remind" value="1" checked><i></i> Remind me before expiry</label>
</section>

<div class="wfoot">
  <button type="button" class="btn red block" data-next>Next</button>
  <button type="submit" class="btn red block" data-submit hidden><?= icon('send', 16) ?> Submit Enquiry</button>
</div>
</form>
