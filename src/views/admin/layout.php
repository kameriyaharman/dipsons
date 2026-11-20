<?php
$f = flash();
$a = admin();
$items = [['dash', '/admin', 'dashboard', 'Dashboard'], ['enq', '/admin/enquiries', 'clipboard', 'Enquiries'], ['cust', '/admin/customers', 'users', 'Customers'], ['rem', '/admin/reminders', 'bell', 'Reminders'], ['svc', '/admin/services', 'layers', 'Services & fees'], ['content', '/admin/content', 'image', 'Website content'], ['set', '/admin/settings', 'settings', 'Settings']];
?><!doctype html>
<html lang="en" translate="no">
<head>
<meta charset="utf-8">
<meta name="google" content="notranslate"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> · Dipsons Admin</title>
<meta name="robots" content="noindex">
<link rel="icon" href="/assets/img/icon-192.png">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body>
<aside class="sb">
  <a href="/admin" class="sbrand"><img src="/assets/img/logo.png" width="40" alt=""><span><b>DIPSONS</b><small>POLLUTION &amp; INSURANCE CENTRE</small></span></a>
  <nav>
    <?php foreach ($items as [$k, $h, $i, $t]): ?>
      <a href="<?= $h ?>" class="<?= ($nav ?? '') === $k ? 'on' : '' ?>"><?= icon($i, 18) ?><span><?= $t ?></span><?php if ($k === 'enq' && $newCount): ?><em><?= $newCount ?></em><?php endif; ?></a>
    <?php endforeach; ?>
    <a href="/" target="_blank"><?= icon('globe', 18) ?><span>View website</span></a>
  </nav>
  <div class="me"><span class="av"><?= e(mb_substr($a['name'], 0, 1)) ?></span><span><b><?= e($a['name']) ?></b><small><?= e(ucfirst($a['role'])) ?></small></span><a href="/admin/logout" title="Logout"><?= icon('logout', 18) ?></a></div>
</aside>
<div class="main">
  <header class="top">
    <button class="burger" onclick="document.body.classList.toggle('sb-open')"><?= icon('menu', 22) ?></button>
    <h1><?= e($title) ?></h1>
    <form action="/admin/enquiries" class="search"><?= icon('search', 16) ?><input name="q" placeholder="Search name, mobile, vehicle no.…" value="<?= e(get('q')) ?>"></form>
    <a href="/admin/enquiries?status=new" class="bellb" title="New enquiries"><?= icon('bell', 20) ?><?php if ($newCount): ?><i></i><?php endif; ?></a>
  </header>
  <?php if ($f): ?><div class="flash <?= e($f[1]) ?>"><?= e($f[0]) ?></div><?php endif; ?>
  <div class="content"><?= $content ?></div>
</div>
<?= csrf_field() ?>
<div class="shade" onclick="document.body.classList.remove('sb-open')"></div>
<script src="<?= asset('js/admin.js') ?>" defer></script>
</body>
</html>
