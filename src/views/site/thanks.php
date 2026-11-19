<?php $p1 = setting('phone1'); $wa = setting('whatsapp', $p1); ?>
<section class="sec">
  <div class="wrap">
    <div class="card pad thanks">
      <span class="okc"><?= icon('check', 40) ?></span>
      <h1>Enquiry received!</h1>
      <p>Enquiry no. <b><?= e($enq['code']) ?></b> · <?= e(setting('callback_promise')) ?>.</p>
      <p class="muted">Services: <?= e($enq['service_names']) ?><?= $enq['vehicle_number'] ? ' · ' . e($enq['vehicle_number']) : '' ?></p>
      <div class="btns center">
        <a class="btn btn-wa" target="_blank" rel="noopener" href="<?= wa_link($wa, 'Namaskar, my enquiry number is ' . $enq['code'] . '. Sending documents for ' . $enq['service_names'] . '.') ?>"><?= icon('whatsapp', 17) ?> Send documents on WhatsApp</a>
        <a class="btn btn-outline" href="<?= tel($p1) ?>"><?= icon('phone', 17) ?> Call <?= e(substr($p1,0,5).' '.substr($p1,5)) ?></a>
      </div>
      <p class="muted small">Track this enquiry anytime in the <a href="/app">Dipsons app</a> with your mobile number.</p>
    </div>
  </div>
</section>
