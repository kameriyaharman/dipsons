<section class="phead">
  <div class="wrap">
    <div class="crumbs"><a href="/">Home</a> › About</div>
    <h1>About Dipsons</h1>
    <p class="lead">Pollution &amp; Insurance Centre — <?= e(setting('address_line1')) ?>, <?= e(setting('address_line2')) ?></p>
  </div>
</section>
<section class="sec">
  <div class="wrap svc-grid">
    <div class="card pad prose">
      <?php foreach (preg_split("/\n\s*\n/", setting('about_text')) as $para): ?><p><?= nl2br(e(trim($para))) ?></p><?php endforeach; ?>
      <div class="why mt">
        <div><?= icon('zap', 24) ?><b>Fast service</b><p>PUC in minutes, insurance the same day</p></div>
        <div><?= icon('receipt', 24) ?><b>Clear charges</b><p>Fees told upfront before any work</p></div>
        <div><?= icon('whatsapp', 24) ?><b>WhatsApp updates</b><p>Status and documents on your phone</p></div>
        <div><?= icon('bell', 24) ?><b>Free reminders</b><p>Before insurance, PUC, tax &amp; fitness expiry</p></div>
      </div>
    </div>
    <aside class="side"><?= view('site/_quick_form', []) ?></aside>
  </div>
</section>
