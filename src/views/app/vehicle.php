<header class="bar"><a href="/app/reminders" class="ib"><?= icon('back', 22) ?></a><h1><?= e($title) ?></h1><span class="ib"></span></header>
<main class="pad">
  <form method="post" class="card">
    <?= csrf_field() ?>
    <label class="fl"><span>Vehicle number *</span><input name="number" required value="<?= e($v ? fmt_vehicle($v['number']) : '') ?>" placeholder="AS 15 C 4521" style="text-transform:uppercase"></label>
    <div class="gl">Vehicle type</div>
    <div class="pick">
      <?php foreach (VEHICLE_TYPES as $t): ?><label><input type="radio" name="type" value="<?= e($t) ?>" <?= ($v['type'] ?? 'Car / Jeep') === $t ? 'checked' : '' ?>><span><?= e($t) ?></span></label><?php endforeach; ?>
    </div>
    <label class="fl"><span>Model</span><input name="model" value="<?= e($v['model'] ?? '') ?>" placeholder="Maruti Swift"></label>
    <div class="gl">Expiry dates (for reminders)</div>
    <div class="row2">
      <?php foreach (['insurance_expiry' => 'Insurance', 'puc_expiry' => 'PUC', 'tax_expiry' => 'Road tax', 'fitness_expiry' => 'Fitness', 'permit_expiry' => 'Permit'] as $col => $lab): ?>
        <label class="fl"><span><?= $lab ?></span><input type="date" name="<?= $col ?>" value="<?= e($v[$col] ?? '') ?>"></label>
      <?php endforeach; ?>
    </div>
    <button class="btn red block">Save vehicle</button>
    <?php if ($v): ?><button class="btn ghost block" name="delete" value="1" onclick="return confirm('Remove this vehicle?')">Remove vehicle</button><?php endif; ?>
  </form>
</main>
