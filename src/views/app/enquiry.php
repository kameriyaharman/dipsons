<?php
$wa = setting('whatsapp', setting('phone1'));
$order = ['new', 'called', 'docs_pending', 'in_process', 'done'];
$pos = array_search($enq['status'], $order, true);
?>
<header class="bar"><a href="/app/enquiries" class="ib"><?= icon('back', 22) ?></a><h1>Enquiry <?= e($enq['code']) ?></h1><span class="ib"></span></header>
<main class="pad">
  <?php if ($isNew): ?>
  <div class="okbox"><span><?= icon('check', 30) ?></span><b>Enquiry received!</b><small>Enquiry no. <?= e($enq['code']) ?> · <?= e(setting('callback_promise')) ?></small></div>
  <?php endif; ?>
  <div class="card">
    <div class="row"><div><b class="big"><?= e($enq['service_names']) ?></b><small class="muted"><?= e(trim($enq['vehicle_number'] . ' · ' . fmt_date($enq['created_at']), ' ·')) ?></small></div><?= status_badge($enq['status']) ?></div>
    <div class="two">
      <a class="btn outline sm" href="<?= wa_link($wa, 'Namaskar, about my enquiry ' . $enq['code'] . ' (' . $enq['service_names'] . ').') ?>" target="_blank" rel="noopener"><?= icon('chat', 16) ?> Chat</a>
      <a class="btn outline sm" href="<?= tel(setting('phone1')) ?>"><?= icon('phone', 16) ?> Call</a>
    </div>
  </div>

  <h3 class="sh">Status</h3>
  <div class="card">
    <ul class="tl">
      <?php foreach ($notes as $n): ?>
        <li class="done"><i></i><b><?= e($n['text']) ?></b><small><?= e(fmt_date($n['created_at'], 'd M · h:i A')) ?></small></li>
      <?php endforeach; ?>
      <?php if ($enq['status'] === 'cancelled'): ?>
        <li class="x"><i></i><b>Cancelled</b></li>
      <?php elseif ($enq['status'] !== 'done'): ?>
        <?php $nowTxt = ['new' => ['Waiting for call back', 'We will call you shortly'], 'called' => ['Called by Dipsons', 'We are working on it'], 'docs_pending' => ['Documents pending', 'Please upload or WhatsApp the documents'], 'in_process' => ['In process', 'We are working on it']][$enq['status']] ?? [STATUSES[$enq['status']], '']; ?>
        <li class="now"><i></i><b><?= e($nowTxt[0]) ?></b><small><?= e($nowTxt[1]) ?></small></li>
        <li><i></i><b>Completed</b><small>Documents / certificate on WhatsApp</small></li>
      <?php endif; ?>
    </ul>
  </div>

  <h3 class="sh">Documents</h3>
  <div class="card">
    <?php foreach ($docs as $d): ?>
      <a class="docrow" href="/app/file/<?= $d['id'] ?>" target="_blank"><?= icon($d['mime'] === 'application/pdf' ? 'file' : 'image', 18) ?><span><?= e($d['label']) ?></span><small><?= e(human_size((int)$d['size'])) ?></small></a>
    <?php endforeach; ?>
    <?php if (!$docs): ?><p class="muted small">No documents uploaded yet.</p><?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="upform">
      <?= csrf_field() ?>
      <label class="up sm"><span class="upi"><?= icon('upload', 18) ?></span><span class="upt"><b>Add documents</b><small data-st>Photos or PDF</small></span><input type="file" name="docs[]" accept="image/*,application/pdf" multiple required onchange="this.form.querySelector('button').hidden=false"></label>
      <button class="btn green block sm" hidden>Upload</button>
    </form>
    <a class="link small" href="<?= wa_link($wa, 'Namaskar, sending documents for enquiry ' . $enq['code'] . '.') ?>" target="_blank" rel="noopener">Or send documents on WhatsApp →</a>
  </div>

  <?php if ($enq['charges']): ?><div class="card"><small class="muted">Charges</small><b class="block"><?= e($enq['charges']) ?></b><small class="muted">Payment: <?= e(ucfirst($enq['payment_status'])) ?></small></div><?php endif; ?>
</main>
