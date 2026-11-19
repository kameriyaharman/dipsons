<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> · Dipsons</title><meta name="robots" content="noindex">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>"></head>
<body class="loginpg">
<form method="post" class="lcard">
  <?= csrf_field() ?>
  <div class="lbrand"><img src="/assets/img/logo.png" width="56" alt=""><span><b>DIPSONS</b><small>ADMIN PANEL</small></span></div>
  <h2><?= e($title) ?></h2>
  <?php if ($setup): ?><p class="muted">First time setup — create the owner login.</p><?php endif; ?>
  <?php if ($err): ?><div class="flash err"><?= e($err) ?></div><?php endif; ?>
  <?php if ($setup): ?><label class="f"><span>Your name</span><input name="name" value="<?= e(post('name')) ?>" placeholder="Dipak"></label><?php endif; ?>
  <label class="f"><span>Username</span><input name="username" required autofocus value="<?= e(post('username')) ?>" autocomplete="username"></label>
  <label class="f"><span>Password</span><input type="password" name="password" required autocomplete="<?= $setup ? 'new-password' : 'current-password' ?>"></label>
  <button class="btn red block"><?= $setup ? 'Create account' : 'Login' ?></button>
</form>
</body></html>
