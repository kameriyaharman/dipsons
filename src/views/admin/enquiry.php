<?php
$tpls = setting_json('wa_templates');
$vars = [
    'name' => $enq['name'], 'code' => $enq['code'], 'services' => $enq['service_names'],
    'documents' => $svcDocs ? implode(', ', array_slice($svcDocs, 0, 5)) : 'RC and ID proof',
    'charges' => $enq['charges'] ?: '(charges)', 'phone' => setting('phone1'), 'vehicle' => $enq['vehicle_number'],
];
$cb = $enq['callback_at'] ? date('Y-m-d\TH:i', strtotime($enq['callback_at'])) : '';
$dotc = ['status' => 'g', 'assign' => 'a', 'wa' => 'w', 'note' => 'b'];
?>
<div class="crumb"><a href="/admin/enquiries">← Enquiries</a></div>
<div class="grid2 wide">
  <div>
    <div class="panel">
      <div class="ph"><h3>Enquiry <?= e($enq['code']) ?> · <?= e($enq['name']) ?></h3><?= status_badge($enq['status']) ?></div>
      <div class="kv">
        <div><small>Mobile</small><b><a href="<?= tel($enq['mobile']) ?>"><?= e(fmt_mobile($enq['mobile'])) ?></a></b></div>
        <div><small>Source</small><b><?= e(SOURCES[$enq['source']] ?? $enq['source']) ?></b></div>
        <div><small>Received</small><b><?= e(date('d M · h:i A', strtotime($enq['created_at']))) ?></b></div>
        <div><small>Services</small><b><?= e($enq['service_names']) ?></b></div>
        <div><small>Vehicle</small><b><?= e(trim($enq['vehicle_type'] . ($enq['vehicle_model'] ? ' · ' . $enq['vehicle_model'] : ''), ' ·') ?: '—') ?></b></div>
        <div><small>Vehicle no.</small><b><?= e($enq['vehicle_number'] ?: '—') ?></b></div>
        <div><small>Insurance expiry</small><b><?= e(fmt_date($veh['insurance_expiry'] ?? $enq['insurance_expiry']) ?: '—') ?></b></div>
        <div><small>PUC expiry</small><b><?= e(fmt_date($veh['puc_expiry'] ?? $enq['puc_expiry']) ?: '—') ?></b></div>
        <div><small>Preferred</small><b><?= e($enq['preferred'] ?: '—') ?></b></div>
        <?php if ($enq['message']): ?><div class="full"><small>Message</small><b><?= nl2br(e($enq['message'])) ?></b></div><?php endif; ?>
      </div>
      <details class="edit"><summary>Edit details</summary>
        <form method="post" class="fgrid">
          <?= csrf_field() ?><input type="hidden" name="action" value="edit">
          <label class="f"><span>Name</span><input name="name" value="<?= e($enq['name']) ?>"></label>
          <label class="f"><span>Vehicle type</span><select name="vehicle_type"><?php foreach (VEHICLE_TYPES as $t): ?><option <?= $enq['vehicle_type'] === $t ? 'selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?></select></label>
          <label class="f"><span>Vehicle no.</span><input name="vehicle_number" value="<?= e($enq['vehicle_number']) ?>"></label>
          <label class="f"><span>Model</span><input name="vehicle_model" value="<?= e($enq['vehicle_model']) ?>"></label>
          <label class="f full"><span>Message</span><textarea name="message" rows="2"><?= e($enq['message']) ?></textarea></label>
          <button class="btn green sm">Save</button>
        </form>
      </details>
      <?php if ($enq['customer_id']): ?><a class="lnk" href="/admin/customers/<?= (int)$enq['customer_id'] ?>">View customer profile →</a><?php endif; ?>
    </div>

    <div class="panel">
      <div class="ph"><h3>Documents</h3><?php if ($docs): ?><a class="lnk red" href="/admin/enquiries/<?= e($enq['code']) ?>/zip"><?= icon('download', 14) ?> Download all</a><?php endif; ?></div>
      <div class="docs">
        <?php foreach ($docs as $d): ?>
          <div class="doc">
            <a href="/admin/file/<?= $d['id'] ?>" target="_blank" class="dthumb"><?php if (str_starts_with($d['mime'], 'image/') && $d['mime'] !== 'image/heic'): ?><img src="/admin/file/<?= $d['id'] ?>" alt="" loading="lazy"><?php else: ?><?= icon('file', 26) ?><?php endif; ?></a>
            <b><?= e($d['label']) ?></b><small><?= e(strtoupper(pathinfo($d['filename'], PATHINFO_EXTENSION))) ?> · <?= e(human_size((int)$d['size'])) ?></small>
            <div class="dacts"><a href="/admin/file/<?= $d['id'] ?>?dl=1" title="Download"><?= icon('download', 14) ?></a>
              <form method="post" onsubmit="return confirm('Delete this document?')"><?= csrf_field() ?><input type="hidden" name="action" value="delete_doc"><input type="hidden" name="doc" value="<?= $d['id'] ?>"><button title="Delete"><?= icon('trash', 14) ?></button></form></div>
          </div>
        <?php endforeach; ?>
        <form method="post" enctype="multipart/form-data" class="doc add">
          <?= csrf_field() ?><input type="hidden" name="action" value="upload">
          <label class="dthumb"><?= icon('upload', 24) ?><input type="file" name="docs[]" multiple accept="image/*,application/pdf" onchange="this.form.submit()"></label>
          <b>Add document</b><small>Photo or PDF</small>
          <input type="hidden" name="label" value="Uploaded by staff">
        </form>
      </div>
      <?php if ($svcDocs): ?><p class="muted small">Needed for these services: <?= e(implode(' · ', $svcDocs)) ?></p><?php endif; ?>
    </div>

    <div class="panel">
      <h3>Notes &amp; follow-up</h3>
      <ul class="notes">
        <?php foreach ($notes as $n): ?>
          <li><i class="<?= $dotc[$n['kind']] ?? 'b' ?>"></i><div><b><?= nl2br(e($n['text'])) ?></b><?php if ($n['by_name']): ?><small> — <?= e($n['by_name']) ?></small><?php endif; ?><?php if ($n['public']): ?> <span class="tagp" title="Customer can see this in the app">visible to customer</span><?php endif; ?></div><em><?= e(fmt_date($n['created_at'], 'd M · h:i A')) ?></em></li>
        <?php endforeach; ?>
      </ul>
      <form method="post" class="addnote">
        <?= csrf_field() ?><input type="hidden" name="action" value="note">
        <label class="f"><span>Add note</span><textarea name="note" rows="2" required placeholder="Called — wants quotes from 3 insurers, zero-dep."></textarea></label>
        <div class="row"><label class="chk"><input type="checkbox" name="public" value="1"> Show to customer in app</label><button class="btn green sm">Add note</button></div>
      </form>
    </div>
    <?php if ($other): ?>
    <div class="panel"><h3>Other enquiries from this number</h3>
      <?php foreach ($other as $o): ?><a class="cbrow" href="/admin/enquiries/<?= e($o['code']) ?>"><b><?= e($o['code']) ?></b><span><?= e($o['service_names']) ?></span><?= status_badge($o['status']) ?></a><?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <div>
    <form method="post" class="panel">
      <?= csrf_field() ?><input type="hidden" name="action" value="update">
      <h3>Update status</h3>
      <div class="stpick">
        <?php foreach (STATUSES as $k => $t): ?><label><input type="radio" name="status" value="<?= $k ?>" <?= $enq['status'] === $k ? 'checked' : '' ?>><span class="s-<?= $k ?>"><?= e($t) ?></span></label><?php endforeach; ?>
      </div>
      <div class="fgrid">
        <label class="f"><span>Assigned to</span><select name="assigned_to"><option value="">—</option><?php foreach (array_unique(array_merge(staff_names(), $enq['assigned_to'] ? [$enq['assigned_to']] : [])) as $n): ?><option <?= $enq['assigned_to'] === $n ? 'selected' : '' ?>><?= e($n) ?></option><?php endforeach; ?></select></label>
        <label class="f"><span>Call back on</span><input type="datetime-local" name="callback_at" value="<?= e($cb) ?>"></label>
        <label class="f"><span>Charges (₹)</span><input name="charges" value="<?= e($enq['charges']) ?>" placeholder="Insurance as per quote + PUC ₹100"></label>
        <label class="f"><span>Payment</span><select name="payment_status"><?php foreach (['pending' => 'Pending', 'partial' => 'Partial', 'paid' => 'Paid'] as $k => $t): ?><option value="<?= $k ?>" <?= $enq['payment_status'] === $k ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?></select></label>
      </div>
      <button class="btn red block">Save changes</button>
    </form>

    <div class="panel" id="wa" data-code="<?= e($enq['code']) ?>" data-mobile="91<?= e($enq['mobile']) ?>" data-csrf="<?= e(csrf_token()) ?>">
      <h3><span class="wag"><?= icon('whatsapp', 18) ?></span> Reply on WhatsApp</h3>
      <label class="f"><span>Template</span><select id="watpl">
        <?php foreach ($tpls as $i => [$n, $t]): ?><option value="<?= $i ?>" data-text="<?= e(fill_template($t, $vars)) ?>"><?= e($n) ?></option><?php endforeach; ?>
        <option value="custom" data-text="">Custom message</option>
      </select></label>
      <div class="wabox">
        <div class="wah"><span class="av">D</span><span><b>Dipsons</b><small>Business account</small></span></div>
        <textarea id="watext" rows="6"><?= e($tpls ? fill_template($tpls[0][1], $vars) : '') ?></textarea>
      </div>
      <div class="row2b">
        <button type="button" class="btn wa" id="wasend"><?= icon('send', 16) ?> Send</button>
        <a class="btn outline" href="<?= tel($enq['mobile']) ?>"><?= icon('phone', 16) ?> Call now</a>
      </div>
      <p class="muted small">Opens WhatsApp with this message ready — just press send.</p>
    </div>

    <form method="post" class="panel">
      <?= csrf_field() ?><input type="hidden" name="action" value="reminders">
      <h3><?= icon('bell', 18) ?> Reminders</h3>
      <?php if ($veh): ?>
        <p class="muted small">Expiry dates for <?= e(fmt_vehicle($veh['number'])) ?> — they appear in the Reminders list before they expire.</p>
        <div class="fgrid">
          <?php foreach (['insurance_expiry' => 'Insurance', 'puc_expiry' => 'PUC', 'tax_expiry' => 'Road tax', 'fitness_expiry' => 'Fitness', 'permit_expiry' => 'Permit'] as $col => $lab): ?>
            <label class="f"><span><?= $lab ?></span><input type="date" name="<?= $col ?>" value="<?= e($veh[$col] ?? '') ?>"></label>
          <?php endforeach; ?>
        </div>
      <?php else: ?><p class="muted small">No vehicle number on this enquiry — add one in “Edit details” to save expiry reminders.</p><?php endif; ?>
      <label class="tog"><input type="checkbox" name="remind" value="1" <?= $enq['remind'] ? 'checked' : '' ?>><i></i> Customer wants reminders</label>
      <button class="btn green sm">Save reminders</button>
    </form>
  </div>
</div>
