<?php $old = $_SESSION['old'] ?? []; unset($_SESSION['old']); $sel = array_map('strval', (array)($old['services'] ?? [])); ?>
<div class="crumb"><a href="/admin/enquiries">← Enquiries</a></div>
<form method="post" enctype="multipart/form-data" class="panel narrow">
  <?= csrf_field() ?>
  <h3>Add enquiry (phone call / WhatsApp / walk-in)</h3>
  <div class="fgrid">
    <label class="f"><span>Customer name *</span><input name="name" required value="<?= e($old['name'] ?? $cust['name'] ?? '') ?>"></label>
    <label class="f"><span>Mobile *</span><input name="mobile" required inputmode="numeric" value="<?= e($old['mobile'] ?? $cust['mobile'] ?? '') ?>"></label>
    <label class="f"><span>Source</span><select name="source"><?php foreach (SOURCES as $k => $t): if ($k === 'app') continue; ?><option value="<?= $k ?>" <?= ($old['source'] ?? 'call') === $k ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?></select></label>
    <label class="f"><span>Assign to</span><select name="assigned_to"><option value="">—</option><?php foreach (staff_names() as $n): ?><option><?= e($n) ?></option><?php endforeach; ?></select></label>
  </div>
  <div class="lbl">Services *</div>
  <?php foreach (services_grouped() as $g => $list): ?>
    <div class="glabel"><?= e($g) ?></div>
    <div class="pick"><?php foreach ($list as $s): ?><label><input type="checkbox" name="services[]" value="<?= $s['id'] ?>" <?= in_array((string)$s['id'], $sel, true) ? 'checked' : '' ?>><span><?= e($s['name']) ?></span></label><?php endforeach; ?></div>
  <?php endforeach; ?>
  <div class="fgrid mt">
    <label class="f"><span>Vehicle type</span><select name="vehicle_type"><?php foreach (VEHICLE_TYPES as $t): ?><option <?= ($old['vehicle_type'] ?? 'Car / Jeep') === $t ? 'selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?></select></label>
    <label class="f"><span>Vehicle no.</span><input name="vehicle_number" value="<?= e($old['vehicle_number'] ?? '') ?>" style="text-transform:uppercase"></label>
    <label class="f"><span>Model</span><input name="vehicle_model" value="<?= e($old['vehicle_model'] ?? '') ?>"></label>
    <label class="f"><span>Insurance expiry</span><input type="date" name="insurance_expiry" value="<?= e($old['insurance_expiry'] ?? '') ?>"></label>
    <label class="f"><span>PUC expiry</span><input type="date" name="puc_expiry" value="<?= e($old['puc_expiry'] ?? '') ?>"></label>
    <label class="f"><span>Documents</span><input type="file" name="docs[]" multiple accept="image/*,application/pdf"></label>
    <label class="f full"><span>Message / notes</span><textarea name="message" rows="3"><?= e($old['message'] ?? '') ?></textarea></label>
  </div>
  <button class="btn red">Save enquiry</button>
</form>
