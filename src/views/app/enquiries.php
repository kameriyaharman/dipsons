<header class="bar"><a href="/app/home" class="ib"><?= icon('back', 22) ?></a><h1>My enquiries</h1><a href="/app/new" class="ib"><?= icon('plus', 22) ?></a></header>
<div class="seg pad0">
  <?php foreach (['all' => 'All', 'open' => 'Open', 'done' => 'Done'] as $k => $t): ?><a href="?f=<?= $k ?>" class="<?= $f === $k ? 'on' : '' ?>"><?= $t ?></a><?php endforeach; ?>
</div>
<main class="pad">
  <?php if (!$list): ?>
    <div class="empty"><?= icon('clipboard', 40) ?><p>No enquiries yet.</p><a class="btn red" href="/app/new">Send an enquiry</a></div>
  <?php endif; ?>
  <div class="list">
  <?php foreach ($list as $q): ?>
    <a class="li" href="/app/enquiry/<?= e($q['code']) ?>">
      <span class="lt"><b><?= e($q['service_names'] ?: 'Enquiry') ?></b><small><?= e($q['code']) ?><?= $q['vehicle_number'] ? ' · ' . e($q['vehicle_number']) : '' ?> · <?= e(fmt_date($q['created_at'], 'd M')) ?></small></span>
      <?= status_badge($q['status']) ?>
    </a>
  <?php endforeach; ?>
  </div>
</main>
