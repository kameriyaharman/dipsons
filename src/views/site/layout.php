<?php
$bn = setting('business_name', 'DIPSONS');
$tag = setting('business_tagline');
$p1 = setting('phone1'); $p2 = setting('phone2'); $wa = setting('whatsapp', $p1);
$addr = setting('address_line1') . ', ' . setting('address_line2');
$nav = [['/', 'Home'], ['/services', 'Services'], ['/insurance', 'Insurance'], ['/pollution', 'Pollution'], ['/all-rto-work', 'RTO Work'], ['/about', 'About'], ['/contact', 'Contact']];
$cur = url_path();
$f = flash();
?><!doctype html>
<html lang="en" translate="no">
<head>
<meta charset="utf-8">
<meta name="google" content="notranslate">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(($title ? $title . ' — ' : '') . 'Dipsons Pollution & Insurance Centre, Simlaguri') ?></title>
<meta name="description" content="<?= e($meta ?? 'Insurance, pollution (PUC), road tax, fitness, permit, ownership transfer, FASTag, GPS, VLTD and all RTO work for every vehicle at NRL Bharat Petrol Pump, Simlaguri, Baksa, Assam.') ?>">
<meta name="theme-color" content="#1f6b35">
<link rel="icon" href="/assets/img/icon-192.png">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/site.css') ?>">
</head>
<body>
<div class="topbar">
  <div class="wrap">
    <span><?= icon('phone', 14) ?> <a href="<?= tel($p1) ?>"><?= e(substr($p1,0,5).' '.substr($p1,5)) ?></a><?php if ($p2): ?> &nbsp;|&nbsp; <a href="<?= tel($p2) ?>"><?= e(substr($p2,0,5).' '.substr($p2,5)) ?></a><?php endif; ?></span>
    <span class="hide-sm"><?= icon('pin', 14) ?> <?= e($addr) ?></span>
    <span class="hide-sm"><?= icon('clock', 14) ?> <?= e(setting('timings')) ?></span>
  </div>
</div>
<header class="header">
  <div class="wrap">
    <a href="/" class="brand"><img src="/assets/img/logo.png" alt="" width="44" height="40"><span><b><?= e($bn) ?></b><small><?= e($tag) ?></small></span></a>
    <nav class="nav" id="nav">
      <?php foreach ($nav as [$h, $t]): ?><a href="<?= $h ?>" class="<?= $cur === $h ? 'on' : '' ?>"><?= $t ?></a><?php endforeach; ?>
      <a href="/app" class="nav-app">Our app</a>
    </nav>
    <div class="head-cta">
      <a class="btn btn-wa sm hide-sm" href="<?= wa_link($wa, 'Namaskar, I have an enquiry.') ?>" target="_blank" rel="noopener"><?= icon('whatsapp', 16) ?> WhatsApp</a>
      <a class="btn btn-red sm" href="/contact"><?= icon('send', 15) ?> <span>Send Enquiry</span></a>
      <button class="burger" aria-label="Menu" onclick="document.body.classList.toggle('menu-open')"><?= icon('menu', 24) ?></button>
    </div>
  </div>
</header>

<?php if ($f): ?><div class="wrap"><div class="flash <?= e($f[1]) ?>"><?= e($f[0]) ?></div></div><?php endif; ?>

<main><?= $content ?></main>

<footer class="footer">
  <div class="wrap fgrid">
    <div>
      <a href="/" class="brand light"><img src="/assets/img/logo.png" alt="" width="44" height="40"><span><b><?= e($bn) ?></b><small><?= e($tag) ?></small></span></a>
      <p>Pollution check, insurance &amp; all RTO services for every vehicle — plus FASTag, GPS, mechanic and plumber services.</p>
    </div>
    <div>
      <h4>Services</h4>
      <?php foreach (array_slice(services_all(), 0, 8) as $s): ?><a href="/<?= e($s['slug']) ?>"><?= e($s['name']) ?></a><?php endforeach; ?>
      <a href="/services">All services →</a>
    </div>
    <div>
      <h4>Visit us</h4>
      <p><?= e(setting('address_line1')) ?><br><?= e(setting('address_line2')) ?><br><?= e(setting('timings')) ?></p>
      <a href="https://maps.google.com/?q=<?= rawurlencode(setting('map_query')) ?>" target="_blank" rel="noopener">Get directions →</a>
    </div>
    <div>
      <h4>Call / WhatsApp</h4>
      <a href="<?= tel($p1) ?>"><?= e(fmt_mobile($p1)) ?></a>
      <?php if ($p2): ?><a href="<?= tel($p2) ?>"><?= e(fmt_mobile($p2)) ?></a><?php endif; ?>
      <a href="<?= wa_link($wa) ?>" target="_blank" rel="noopener">WhatsApp us</a>
      <a href="/downloads/Dipsons.apk" download>Download Android app (APK)</a>
      <a href="/app">Open web app</a>
    </div>
  </div>
  <div class="wrap copy">© <?= date('Y') ?> Dipsons Pollution &amp; Insurance Centre, Simlaguri. All rights reserved.</div>
</footer>

<div class="mbar">
  <a href="<?= tel($p1) ?>" class="btn btn-outline"><?= icon('phone', 18) ?> Call</a>
  <a href="/contact" class="btn btn-red"><?= icon('send', 18) ?> Send Enquiry</a>
</div>
<a class="wa-float" href="<?= wa_link($wa, 'Namaskar, I have an enquiry.') ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><?= icon('whatsapp', 28) ?></a>
<script src="<?= asset('js/site.js') ?>" defer></script>
</body>
</html>
