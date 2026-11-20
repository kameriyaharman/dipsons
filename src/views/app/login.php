<div class="splash" id="splash">
  <img src="/assets/img/logo.png" alt="" width="70"><div class="sb"><b><?= e(setting('business_name')) ?></b><small><?= e(setting('business_tagline')) ?></small></div>
  <img class="splash-img" src="/assets/img/hero.jpg" alt="">
  <h2>All vehicle RTO solutions</h2>
  <p>Insurance · Pollution · Tax · Fitness · Transfer · Permit · FASTag · GPS</p>
</div>
<div class="login">
  <div class="brandrow"><img src="/assets/img/logo.png" alt="" width="54"><div><b><?= e(setting('business_name')) ?></b><small><?= e(setting('business_tagline')) ?></small></div></div>
  <h1>Login with mobile</h1>
  <p class="muted">Enter your mobile number to see your vehicles, enquiries and reminders.</p>
  <?php if ($err): ?><div class="alert err"><?= e($err) ?></div><?php endif; ?>
  <form method="post">
    <?= csrf_field() ?>
    <label class="fl"><span>Mobile number</span><input name="mobile" inputmode="numeric" maxlength="14" required autofocus value="<?= e(post('mobile')) ?>" placeholder="98XXX XXXXX"></label>
    <label class="fl"><span>Your name (first time only)</span><input name="name" value="<?= e(post('name')) ?>" placeholder="Rahul Boro"></label>
    <button class="btn red block">Continue</button>
  </form>
  <p class="center small muted"><a href="/app/guest" class="link">Or continue as guest to send an enquiry</a></p>
  <p class="center small muted apkdl"><a href="/downloads/Dipsons.apk" download class="link">📲 Download Android app (APK)</a></p>
  <script>if(/DipsonsApp/.test(navigator.userAgent)||window.matchMedia('(display-mode: standalone)').matches)document.querySelector('.apkdl').hidden=true</script>
</div>
<script>setTimeout(function(){var s=document.getElementById('splash');if(s)s.classList.add('gone')},<?= empty($_SESSION['splash_seen']) ? 1400 : 0 ?>);</script>
<?php $_SESSION['splash_seen'] = 1; ?>
