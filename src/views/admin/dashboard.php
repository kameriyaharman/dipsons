<?php
$src = []; foreach ($stats['today_src'] as $r) $src[] = $r['n'] . ' ' . strtolower(SOURCES[$r['source']] ?? $r['source']);
$max = max(1, ...array_map(fn($r) => (int)$r['n'], $bySvc ?: [['n' => 1]]));
$colors = ['#1f6b35', '#d4152f', '#e0a526', '#3b6fd8', '#7a4fd6', '#16a3a3'];
?>
<div class="stats">
  <div class="stat"><span class="si red"><?= icon('clipboard', 20) ?></span><div><small>New enquiries today</small><b><?= $stats['today'] ?></b><em><?= e(implode(' · ', $src) ?: 'none yet') ?></em></div></div>
  <div class="stat"><span class="si amber"><?= icon('clock', 20) ?></span><div><small>Pending follow-up</small><b><?= $stats['pending'] ?></b><em><?= $stats['callback_due'] ?> call back due</em></div></div>
  <div class="stat"><span class="si green"><?= icon('check', 20) ?></span><div><small>Completed this month</small><b><?= $stats['done_month'] ?></b><em><?= date('F') ?></em></div></div>
  <div class="stat"><span class="si blue"><?= icon('bell', 20) ?></span><div><small>Renewals due (7 days)</small><b><?= $stats['renewals7'] ?></b><em><a href="/admin/reminders?days=7">send reminders →</a></em></div></div>
</div>

<div class="bar-actions">
  <h2>Latest enquiries</h2>
  <div><a class="btn outline sm" href="/admin/enquiries"><?= icon('clipboard', 15) ?> All enquiries</a> <a class="btn red sm" href="/admin/enquiries/new"><?= icon('plus', 15) ?> Add enquiry</a></div>
</div>
<?= view('admin/_enq_table', ['list' => $recent]) ?>

<div class="grid2">
  <div class="panel">
    <h3>Enquiries by service — <?= date('F') ?></h3>
    <?php foreach ($bySvc as $i => $r): ?>
      <div class="hbar"><span><?= e($r['name']) ?></span><i><b style="width:<?= round($r['n'] / $max * 100) ?>%;background:<?= $colors[$i % 6] ?>"></b></i><em><?= $r['n'] ?></em></div>
    <?php endforeach; ?>
    <?php if (!$bySvc): ?><p class="muted">No enquiries this month yet.</p><?php endif; ?>
    <?php if ($callbacks): ?>
      <h3 class="mt">Call backs</h3>
      <?php foreach ($callbacks as $r): ?>
        <a class="cbrow <?= strtotime($r['callback_at']) <= time() ? 'late' : '' ?>" href="/admin/enquiries/<?= e($r['code']) ?>"><b><?= e($r['name']) ?></b><span><?= e($r['service_names']) ?></span><em><?= e(date('d M · h:i A', strtotime($r['callback_at']))) ?></em></a>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
  <div class="panel">
    <div class="ph"><h3>Renewals due this week</h3><a href="/admin/reminders?days=7" class="lnk red">Send all reminders</a></div>
    <?= view('admin/_renewals', ['rows' => $ren]) ?>
  </div>
</div>
