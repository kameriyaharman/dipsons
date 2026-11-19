<section class="phead">
  <div class="wrap">
    <div class="crumbs"><a href="/">Home</a> › Services</div>
    <h1>All our services</h1>
    <p class="lead">RTO work, insurance and PUC, FASTag and tracking devices, and trusted repair &amp; home services — all from one centre.</p>
  </div>
</section>
<section class="sec">
  <div class="wrap">
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
