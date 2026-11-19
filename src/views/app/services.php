<header class="bar"><a href="/app/home" class="ib"><?= icon('back', 22) ?></a><h1>All services</h1><a href="/app/reminders" class="ib"><?= icon('bell', 20) ?></a></header>
<main class="pad">
  <?php foreach ($groups as $g => $list): ?>
    <h3 class="sh"><?= e($g) ?></h3>
    <div class="list">
      <?php foreach ($list as $s): ?>
        <a href="/app/new?service=<?= e($s['slug']) ?>" class="li">
          <span class="lic <?= e($s['color']) ?>"><?= icon($s['icon'], 20) ?></span>
          <span class="lt"><b><?= e($s['name']) ?></b><small><?= e($s['short_desc']) ?></small></span>
          <?= icon('chevron', 18) ?>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
</main>
