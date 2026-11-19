<?php $icons = ['shield', 'leaf', 'receipt', 'badge', 'card', 'swap', 'doc', 'globe', 'building', 'rto', 'idcard', 'tag', 'pin', 'gauge', 'satellite', 'wrench', 'garage', 'car', 'droplet', 'bike', 'truck', 'bus', 'tractor', 'zap', 'file']; ?>
<div class="crumb"><a href="/admin/services">← Services</a></div>
<form method="post" class="panel narrow">
  <?= csrf_field() ?>
  <div class="fgrid">
    <label class="f"><span>Service name *</span><input name="name" required value="<?= e($s['name'] ?? '') ?>"></label>
    <label class="f"><span>Website URL (e.g. insurance)</span><input name="slug" value="<?= e($s['slug'] ?? '') ?>" placeholder="auto from name"></label>
    <label class="f"><span>Category</span><select name="category_id"><?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= ($s['category_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></label>
    <label class="f"><span>Order (smaller = first)</span><input type="number" name="sort" value="<?= (int)($s['sort'] ?? 500) ?>"></label>
    <label class="f full"><span>Short description (cards)</span><input name="short_desc" value="<?= e($s['short_desc'] ?? '') ?>"></label>
    <label class="f full"><span>Tagline (service page)</span><input name="tagline" value="<?= e($s['tagline'] ?? '') ?>"></label>
    <label class="f full"><span>Tags (comma separated)</span><input name="tags" value="<?= e($s['tags'] ?? '') ?>" placeholder="New policy, Renewal, Claim help"></label>
    <label class="f full"><span>What we do (one per line)</span><textarea name="what_we_do" rows="5"><?= e($s['what_we_do'] ?? '') ?></textarea></label>
    <label class="f full"><span>Documents needed (one per line)</span><textarea name="documents" rows="4"><?= e($s['documents'] ?? '') ?></textarea></label>
    <label class="f full"><span>Charges / fee note (shown on website — leave empty to hide)</span><input name="fee_note" value="<?= e($s['fee_note'] ?? '') ?>" placeholder="PUC from ₹100 · Insurance as per quote"></label>
  </div>
  <div class="lbl">Icon</div>
  <div class="iconpick"><?php foreach ($icons as $i): ?><label><input type="radio" name="icon" value="<?= $i ?>" <?= ($s['icon'] ?? 'file') === $i ? 'checked' : '' ?>><span><?= icon($i, 20) ?></span></label><?php endforeach; ?></div>
  <div class="row mt">
    <label class="chk"><input type="radio" name="color" value="red" <?= ($s['color'] ?? 'red') === 'red' ? 'checked' : '' ?>> Red</label>
    <label class="chk"><input type="radio" name="color" value="green" <?= ($s['color'] ?? '') === 'green' ? 'checked' : '' ?>> Green</label>
    <label class="tog"><input type="checkbox" name="active" value="1" <?= ($s['active'] ?? 1) ? 'checked' : '' ?>><i></i> Active (shown on website &amp; app)</label>
  </div>
  <button class="btn red mt">Save service</button>
</form>
