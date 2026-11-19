<?php
$qs = function (array $over) { $g = array_merge($_GET, $over); unset($g['p']); $g = array_filter($g, fn($v) => $v !== '' && $v !== null); return '?' . http_build_query($g); };
$st = get('status'); $all = array_sum($counts);
$pages = (int)ceil($total / $per);
?>
<div class="filters">
  <div class="tabsf">
    <a href="<?= $qs(['status' => '']) ?>" class="<?= !$st ? 'on' : '' ?>">All <em><?= $all ?></em></a>
    <?php foreach (STATUSES as $k => $t): ?><a href="<?= $qs(['status' => $k]) ?>" class="<?= $st === $k ? 'on' : '' ?>"><?= e($t) ?><?php if (!empty($counts[$k])): ?> <em><?= $counts[$k] ?></em><?php endif; ?></a><?php endforeach; ?>
  </div>
  <div class="fr">
    <form class="inline" method="get">
      <?php if ($st): ?><input type="hidden" name="status" value="<?= e($st) ?>"><?php endif; ?>
      <?php if (get('q')): ?><input type="hidden" name="q" value="<?= e(get('q')) ?>"><?php endif; ?>
      <select name="service" onchange="this.form.submit()"><option value="">All services</option>
        <?php foreach (services_all(false) as $s): ?><option value="<?= $s['id'] ?>" <?= (int)get('service') === (int)$s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option><?php endforeach; ?>
      </select>
      <select name="source" onchange="this.form.submit()"><option value="">All sources</option>
        <?php foreach (SOURCES as $k => $t): ?><option value="<?= $k ?>" <?= get('source') === $k ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?>
      </select>
      <input type="date" name="from" value="<?= e(get('from')) ?>" onchange="this.form.submit()" title="From">
      <input type="date" name="to" value="<?= e(get('to')) ?>" onchange="this.form.submit()" title="To">
    </form>
    <a class="btn outline sm" href="/admin/enquiries/export<?= $qs([]) ?>"><?= icon('download', 15) ?> Export</a>
    <a class="btn red sm" href="/admin/enquiries/new"><?= icon('plus', 15) ?> Add enquiry</a>
  </div>
</div>
<?php if (get('q')): ?><p class="muted">Search results for “<?= e(get('q')) ?>” — <a class="lnk" href="<?= $qs(['q' => '']) ?>">clear</a></p><?php endif; ?>
<?= view('admin/_enq_table', ['list' => $list]) ?>
<?php if ($pages > 1): ?><div class="pager"><?php for ($i = 1; $i <= $pages; $i++): ?><a href="<?= $qs([]) . '&p=' . $i ?>" class="<?= $i === $page ? 'on' : '' ?>"><?= $i ?></a><?php endfor; ?></div><?php endif; ?>
