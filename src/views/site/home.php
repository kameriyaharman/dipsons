<?php
$p1 = setting('phone1'); $wa = setting('whatsapp', $p1);
$ht = setting('hero_title');
$htHtml = $ht === 'All vehicle RTO solutions under one roof'
  ? 'All vehicle <em class="r">RTO solutions</em><br>under <em class="g">one roof</em>'
  : e($ht);
$chips = array_slice(services_all(), 0, 8);
?>
<section class="hero">
  <div class="wrap hero-grid">
    <div class="hero-text">
      <span class="pill"><?= icon('check', 13) ?> Trusted centre at <?= e(setting('address_line1')) ?></span>
      <h1><?= $htHtml ?></h1>
      <p class="lead"><?= e(setting('hero_text')) ?></p>
      <div class="chips">
        <?php foreach ($chips as $s): ?><a class="chip" href="/<?= e($s['slug']) ?>"><?= icon($s['icon'], 14) ?> <?= e($s['name']) ?></a><?php endforeach; ?>
      </div>
      <div class="btns">
        <a class="btn btn-red" href="<?= tel($p1) ?>"><?= icon('phone', 17) ?> Call <?= e(substr($p1,0,5).' '.substr($p1,5)) ?></a>
        <a class="btn btn-wa" href="<?= wa_link($wa, 'Namaskar, I have an enquiry.') ?>" target="_blank" rel="noopener"><?= icon('whatsapp', 17) ?> WhatsApp Enquiry</a>
      </div>
    </div>
    <div class="hero-side">
      <img class="hero-img" src="/assets/img/hero.jpg" alt="Pollution check certificate for cars" width="590" height="330">
      <?= view('site/_quick_form', ['heading' => 'Get a quick quote / enquiry']) ?>
    </div>
  </div>
</section>

<section class="trust">
  <div class="wrap tgrid">
    <div><?= icon('clock', 22) ?><b>Same-day PUC</b><span>Done in 10 minutes</span></div>
    <div><?= icon('shield', 22) ?><b>Best insurance quotes</b><span>Compare top insurers</span></div>
    <div><?= icon('doc', 22) ?><b>Complete paperwork</b><span>We handle RTO for you</span></div>
    <div><?= icon('bell', 22) ?><b>Expiry reminders</b><span>Never miss a renewal</span></div>
  </div>
</section>

<section class="sec soft">
  <div class="wrap center">
    <div class="eyebrow">All vehicles</div>
    <h2>We work on every type of vehicle</h2>
    <div class="vgrid">
      <?php foreach (array_slice(VEHICLE_TYPES, 0, 6) as $v): ?><div class="vcard"><?= icon(VEHICLE_ICONS[$v], 28) ?><b><?= e($v) ?></b></div><?php endforeach; ?>
    </div>
  </div>
</section>

<section class="sec" id="services">
  <div class="wrap">
    <div class="center"><div class="eyebrow">Our services</div><h2>Everything your vehicle needs — in one place</h2></div>
    <?php foreach ($groups as $gname => $list): ?>
      <h3 class="ghead"><?= e($gname) ?></h3>
      <div class="sgrid">
        <?php foreach ($list as $s): ?>
          <a class="scard" href="/<?= e($s['slug']) ?>">
            <span class="sico <?= e($s['color']) ?>"><?= icon($s['icon'], 22) ?></span>
            <b><?= e($s['name']) ?></b>
            <span class="muted"><?= e($s['short_desc']) ?></span>
            <span class="more">Enquire now <?= icon('chevron', 14) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="sec soft">
  <div class="wrap center">
    <div class="eyebrow">How it works</div>
    <h2>Done in 3 simple steps</h2>
    <div class="steps">
      <div><span class="num">1</span><b>Send enquiry</b><p>Fill the form, call or WhatsApp with your vehicle number and service.</p></div>
      <div><span class="num">2</span><b>Share documents</b><p>Send RC, old policy or ID on WhatsApp — we tell you exactly what is needed.</p></div>
      <div><span class="num">3</span><b>Get it done</b><p>Pay, and receive your policy / certificate / RTO work — with updates on WhatsApp.</p></div>
    </div>
  </div>
</section>

<section class="sec" id="reminder">
  <div class="wrap">
    <div class="remind">
      <div>
        <div class="eyebrow light">Never miss a renewal</div>
        <h2>Free reminders for insurance, PUC, tax &amp; fitness</h2>
        <p>Register your vehicle once — we remind you on WhatsApp before anything expires.</p>
      </div>
      <form method="post" action="/reminder" class="rform">
        <?= csrf_field() ?><input type="text" name="website" class="hp" tabindex="-1" autocomplete="off">
        <input name="vehicle_number" placeholder="Vehicle number (AS 15 C 4521)" required style="text-transform:uppercase">
        <input name="mobile" placeholder="Mobile number" inputmode="numeric" required>
        <label class="mini">Insurance expiry <input type="date" name="insurance_expiry"></label>
        <label class="mini">PUC expiry <input type="date" name="puc_expiry"></label>
        <button class="btn btn-white"><?= icon('bell', 16) ?> Set reminder</button>
      </form>
    </div>
  </div>
</section>

<section class="sec soft">
  <div class="wrap">
    <div class="center"><div class="eyebrow">Why Dipsons</div><h2>Local, quick and fully transparent</h2></div>
    <div class="why">
      <div><?= icon('pin', 24) ?><b>Easy to reach</b><p>Right at <?= e(setting('address_line1')) ?>, <?= e(explode(',', setting('address_line2'))[0]) ?></p></div>
      <div><?= icon('zap', 24) ?><b>Fast service</b><p>PUC in minutes, insurance the same day</p></div>
      <div><?= icon('receipt', 24) ?><b>Clear charges</b><p>Fees told upfront before any work</p></div>
      <div><?= icon('whatsapp', 24) ?><b>WhatsApp updates</b><p>Status and documents on your phone</p></div>
    </div>
  </div>
</section>

<section class="sec">
  <div class="wrap faqwrap">
    <div><div class="eyebrow">FAQ</div><h2>Common questions</h2><p class="muted">Still have a question? Call or WhatsApp us — we reply quickly.</p>
      <a class="btn btn-outline" href="/app"><?= icon('zap', 16) ?> Get the Dipsons app</a></div>
    <div class="faq">
      <?php foreach (setting_json('faq') as [$qq, $aa]): ?>
        <details><summary><?= e($qq) ?></summary><p><?= e($aa) ?></p></details>
      <?php endforeach; ?>
    </div>
  </div>
</section>
