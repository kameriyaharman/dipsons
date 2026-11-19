<?php
$f = flash();
$tabs = [['home', '/app/home', 'home', 'Home'], ['services', '/app/services', 'grid', 'Services'], ['enq', '/app/enquiries', 'clipboard', 'Enquiries'], ['rem', '/app/reminders', 'bell', 'Reminders'], ['me', '/app/profile', 'user', 'Profile']];
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($title ?? 'Dipsons') ?> · Dipsons</title>
<meta name="theme-color" content="#1f6b35">
<link rel="manifest" href="/manifest.webmanifest">
<link rel="icon" href="/assets/img/icon-192.png">
<link rel="apple-touch-icon" href="/assets/img/icon-192.png">
<meta name="apple-mobile-web-app-capable" content="yes">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="<?= !empty($bare) ? 'bare' : '' ?>">
<div class="phone">
<?php if ($f): ?><div class="toast <?= e($f[1]) ?>"><?= e($f[0]) ?></div><?php endif; ?>
<?= $content ?>
<?php if (empty($bare)): ?>
<nav class="tabs">
  <?php foreach ($tabs as [$k, $h, $i, $t]): ?><a href="<?= $h ?>" class="<?= ($tab ?? '') === $k ? 'on' : '' ?>"><?= icon($i, 22) ?><span><?= $t ?></span></a><?php endforeach; ?>
</nav>
<?php endif; ?>
</div>
<script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>
