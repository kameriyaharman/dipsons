<?php
// Quick enquiry card. Variables: $heading (string), $selected (service id|null)
$old = $_SESSION['old'] ?? []; unset($_SESSION['old']);
$selected = $selected ?? ($old['services'][0] ?? null);
$p1 = setting('phone1');
?>
<form class="qform card" method="post" action="/enquiry" id="enquiry">
  <?= csrf_field() ?>
  <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off">
  <h3><?= e($heading ?? 'Get a quick quote / enquiry') ?></h3>
  <p class="muted">Fill in 30 seconds — <?= e(setting('callback_promise')) ?>.</p>
  <label class="fl"><span>Your name</span><input name="name" required value="<?= e($old['name'] ?? '') ?>" placeholder="Rahul Boro"></label>
  <label class="fl"><span>Mobile number</span><input name="mobile" required inputmode="numeric" maxlength="14" value="<?= e($old['mobile'] ?? '') ?>" placeholder="+91 98XXX XXXXX"></label>
  <div class="row2">
    <label class="fl"><span>Service</span>
      <select name="services[]" required>
        <option value="">Select</option>
        <?php foreach (services_grouped() as $g => $list): ?><optgroup label="<?= e($g) ?>">
          <?php foreach ($list as $s): ?><option value="<?= $s['id'] ?>" <?= (string)$selected === (string)$s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option><?php endforeach; ?>
        </optgroup><?php endforeach; ?>
      </select></label>
    <label class="fl"><span>Vehicle</span>
      <select name="vehicle_type">
        <?php foreach (VEHICLE_TYPES as $v): ?><option <?= ($old['vehicle_type'] ?? 'Car / Jeep') === $v ? 'selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?>
      </select></label>
  </div>
  <label class="fl"><span>Vehicle number</span><input name="vehicle_number" value="<?= e($old['vehicle_number'] ?? '') ?>" placeholder="AS 15 C 4521" style="text-transform:uppercase"></label>
  <button class="btn btn-red block"><?= icon('send', 16) ?> Send Enquiry</button>
  <p class="or">or <a href="<?= wa_link(setting('whatsapp', $p1), 'Namaskar, I have an enquiry.') ?>" target="_blank" rel="noopener" class="g"><?= icon('whatsapp', 14) ?> WhatsApp us</a> · <a href="<?= tel($p1) ?>" class="r"><?= icon('phone', 13) ?> Call <?= e(substr($p1,0,5).' '.substr($p1,5)) ?></a></p>
</form>
