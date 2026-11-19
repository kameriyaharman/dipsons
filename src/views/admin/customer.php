<?php $cols = ['insurance_expiry' => 'Insurance', 'puc_expiry' => 'PUC', 'tax_expiry' => 'Road tax', 'fitness_expiry' => 'Fitness', 'permit_expiry' => 'Permit']; ?>
<div class="crumb"><a href="/admin/customers">← Customers</a></div>
<div class="grid2 wide">
  <div>
    <div class="panel">
      <div class="ph"><h3>Vehicles</h3></div>
      <?php foreach ($veh as $v): ?>
        <details class="vrow">
          <summary><b><?= e(fmt_vehicle($v['number'])) ?></b> <span class="muted"><?= e(trim($v['type'] . ' · ' . $v['model'], ' ·')) ?></span>
            <span class="vexp"><?php foreach ($cols as $col => $lab) if ($v[$col]): [$cls] = expiry_state($v[$col]); ?><span class="pill <?= $cls ?>"><?= $lab ?> <?= e(fmt_date($v[$col], 'd M y')) ?></span><?php endif; ?></span></summary>
          <form method="post" class="fgrid">
            <?= csrf_field() ?><input type="hidden" name="action" value="vehicle"><input type="hidden" name="vid" value="<?= $v['id'] ?>">
            <label class="f"><span>Number</span><input name="number" value="<?= e(fmt_vehicle($v['number'])) ?>"></label>
            <label class="f"><span>Type</span><select name="type"><?php foreach (VEHICLE_TYPES as $t): ?><option <?= $v['type'] === $t ? 'selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?></select></label>
            <label class="f"><span>Model</span><input name="model" value="<?= e($v['model']) ?>"></label>
            <?php foreach ($cols as $col => $lab): ?><label class="f"><span><?= $lab ?> expiry</span><input type="date" name="<?= $col ?>" value="<?= e($v[$col] ?? '') ?>"></label><?php endforeach; ?>
            <div class="row full"><button class="btn green sm">Save vehicle</button><button class="btn ghost sm" name="action" value="delete_vehicle" onclick="return confirm('Delete this vehicle?')">Delete</button></div>
          </form>
        </details>
      <?php endforeach; ?>
      <details class="vrow"><summary class="lnk">+ Add vehicle</summary>
        <form method="post" class="fgrid">
          <?= csrf_field() ?><input type="hidden" name="action" value="vehicle"><input type="hidden" name="vid" value="0">
          <label class="f"><span>Number *</span><input name="number" required style="text-transform:uppercase"></label>
          <label class="f"><span>Type</span><select name="type"><?php foreach (VEHICLE_TYPES as $t): ?><option><?= e($t) ?></option><?php endforeach; ?></select></label>
          <label class="f"><span>Model</span><input name="model"></label>
          <?php foreach ($cols as $col => $lab): ?><label class="f"><span><?= $lab ?> expiry</span><input type="date" name="<?= $col ?>"></label><?php endforeach; ?>
          <button class="btn green sm">Add vehicle</button>
        </form>
      </details>
    </div>
    <div class="panel">
      <div class="ph"><h3>Enquiries</h3><a class="btn red sm" href="/admin/enquiries/new?customer=<?= $c['id'] ?>"><?= icon('plus', 14) ?> New enquiry</a></div>
      <?= view('admin/_enq_table', ['list' => $enqs]) ?>
    </div>
    <?php if ($docs): ?>
    <div class="panel"><h3>Documents</h3>
      <div class="docs"><?php foreach ($docs as $d): ?><div class="doc"><a href="/admin/file/<?= $d['id'] ?>" target="_blank" class="dthumb"><?= icon($d['mime'] === 'application/pdf' ? 'file' : 'image', 24) ?></a><b><?= e($d['label']) ?></b><small><?= e(fmt_date($d['created_at'])) ?></small></div><?php endforeach; ?></div>
    </div>
    <?php endif; ?>
  </div>
  <div>
    <form method="post" class="panel">
      <?= csrf_field() ?><input type="hidden" name="action" value="customer">
      <h3>Customer</h3>
      <div class="kv one"><div><small>Mobile</small><b><a href="<?= tel($c['mobile']) ?>"><?= e(fmt_mobile($c['mobile'])) ?></a></b></div><div><small>Joined</small><b><?= e(fmt_date($c['created_at'])) ?></b></div><div><small>Last app login</small><b><?= e($c['last_login_at'] ? fmt_date($c['last_login_at']) : 'Never') ?></b></div></div>
      <label class="f"><span>Name</span><input name="name" value="<?= e($c['name']) ?>"></label>
      <label class="f"><span>City / village</span><input name="city" value="<?= e($c['city']) ?>"></label>
      <label class="tog"><input type="checkbox" name="notify" value="1" <?= $c['notify_whatsapp'] ? 'checked' : '' ?>><i></i> WhatsApp reminders</label>
      <div class="row2b"><button class="btn green">Save</button><a class="btn wa" href="<?= wa_link($c['mobile']) ?>" target="_blank" rel="noopener"><?= icon('whatsapp', 16) ?> WhatsApp</a></div>
    </form>
  </div>
</div>
