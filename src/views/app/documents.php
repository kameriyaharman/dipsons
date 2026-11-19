<header class="bar"><a href="/app/profile" class="ib"><?= icon('back', 22) ?></a><h1>My documents</h1><span class="ib"></span></header>
<main class="pad">
  <?php if (!$docs): ?><div class="empty"><?= icon('file', 40) ?><p>No documents yet. Upload them with an enquiry.</p></div><?php endif; ?>
  <div class="card">
  <?php foreach ($docs as $d): ?>
    <a class="docrow" href="/app/file/<?= $d['id'] ?>" target="_blank"><?= icon($d['mime'] === 'application/pdf' ? 'file' : 'image', 18) ?><span><?= e($d['label']) ?><small class="block muted"><?= e($d['code'] ?: '') ?> · <?= e(fmt_date($d['created_at'])) ?></small></span><small><?= e(human_size((int)$d['size'])) ?></small></a>
  <?php endforeach; ?>
  </div>
</main>
