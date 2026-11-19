<?php $ini = strtoupper(implode('', array_map(fn($w) => mb_substr($w, 0, 1), array_slice(preg_split('/\s+/', trim($cust['name'] ?: 'U')), 0, 2)))); ?>
<header class="bar"><span class="ib"></span><h1>Profile</h1><span class="ib"></span></header>
<main class="pad">
  <div class="prof"><span class="av"><?= e($ini) ?></span><b><?= e($cust['name']) ?></b><small><?= e(fmt_mobile($cust['mobile'])) ?><?= $cust['city'] ? ' · ' . e($cust['city']) : '' ?></small></div>
  <div class="list">
    <a class="li" href="/app/reminders"><span class="lic green"><?= icon('car', 20) ?></span><span class="lt"><b>My vehicles</b></span><span class="cnt"><?= $vc ?></span><?= icon('chevron', 18) ?></a>
    <a class="li" href="/app/documents"><span class="lic green"><?= icon('file', 20) ?></span><span class="lt"><b>My documents</b></span><span class="cnt"><?= $dc ?></span><?= icon('chevron', 18) ?></a>
    <a class="li" href="https://maps.google.com/?q=<?= rawurlencode(setting('map_query')) ?>" target="_blank" rel="noopener"><span class="lic red"><?= icon('map', 20) ?></span><span class="lt"><b>Visit centre — directions</b></span><?= icon('chevron', 18) ?></a>
    <a class="li" href="<?= tel(setting('phone1')) ?>"><span class="lic red"><?= icon('phone', 20) ?></span><span class="lt"><b>Call <?= e(substr(setting('phone1'),0,5).' '.substr(setting('phone1'),5)) ?></b></span><?= icon('chevron', 18) ?></a>
  </div>
  <form method="post" class="card">
    <?= csrf_field() ?>
    <h3 class="sh0">Edit details</h3>
    <label class="fl"><span>Name</span><input name="name" value="<?= e($cust['name']) ?>"></label>
    <label class="fl"><span>City / village</span><input name="city" value="<?= e($cust['city']) ?>" placeholder="Baksa"></label>
    <label class="switch"><input type="checkbox" name="notify" value="1" <?= $cust['notify_whatsapp'] ? 'checked' : '' ?>><i></i> WhatsApp reminders &amp; updates</label>
    <button class="btn green block sm">Save</button>
  </form>
  <a class="btn ghost block" href="/app/logout"><?= icon('logout', 16) ?> Logout</a>
  <p class="center small muted">Install this app: open the browser menu ⋮ and tap “Add to Home screen”.</p>
</main>
