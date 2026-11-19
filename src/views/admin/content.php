<?php
$fields = [
  ['business_name', 'Business name'], ['business_tagline', 'Tagline under name'],
  ['phone1', 'Phone 1 (main)'], ['phone2', 'Phone 2'], ['whatsapp', 'WhatsApp number'], ['timings', 'Timings'],
  ['address_line1', 'Address line 1'], ['address_line2', 'Address line 2'], ['map_query', 'Google Maps search text'], ['callback_promise', 'Call-back promise text'],
];
$faq = setting_json('faq'); $faq[] = ['', ''];
$tpl = setting_json('wa_templates'); $tpl[] = ['', ''];
?>
<form method="post">
<?= csrf_field() ?>
<div class="grid2 wide">
  <div>
    <div class="panel"><h3>Contact &amp; business details</h3>
      <div class="fgrid"><?php foreach ($fields as [$k, $l]): ?><label class="f"><span><?= $l ?></span><input name="<?= $k ?>" value="<?= e(setting($k)) ?>"></label><?php endforeach; ?></div>
    </div>
    <div class="panel"><h3>Home page</h3>
      <label class="f"><span>Hero title</span><input name="hero_title" value="<?= e(setting('hero_title')) ?>"></label>
      <label class="f"><span>Hero text</span><textarea name="hero_text" rows="3"><?= e(setting('hero_text')) ?></textarea></label>
      <label class="f"><span>About page text (blank line = new paragraph)</span><textarea name="about_text" rows="7"><?= e(setting('about_text')) ?></textarea></label>
    </div>
    <div class="panel"><h3>FAQ</h3>
      <?php foreach ($faq as [$qq, $aa]): ?>
        <div class="faqrow"><label class="f"><span>Question</span><input name="faq_q[]" value="<?= e($qq) ?>"></label><label class="f"><span>Answer</span><textarea name="faq_a[]" rows="2"><?= e($aa) ?></textarea></label></div>
      <?php endforeach; ?>
      <p class="muted small">Clear a question to remove it. Save to get another empty row.</p>
    </div>
  </div>
  <div>
    <div class="panel" id="templates"><h3>WhatsApp reply templates</h3>
      <p class="muted small">Use: {name} {code} {services} {documents} {charges} {phone} {vehicle}</p>
      <?php foreach ($tpl as [$n, $t]): ?>
        <div class="faqrow"><label class="f"><span>Template name</span><input name="tpl_n[]" value="<?= e($n) ?>"></label><label class="f"><span>Message</span><textarea name="tpl_t[]" rows="4"><?= e($t) ?></textarea></label></div>
      <?php endforeach; ?>
    </div>
    <div class="panel"><h3>Expiry reminder message</h3>
      <p class="muted small">Use: {name} {kind} {vehicle} {date} {phone}</p>
      <label class="f"><span>Message</span><textarea name="reminder_template" rows="4"><?= e(setting('reminder_template')) ?></textarea></label>
    </div>
    <button class="btn red block">Save website content</button>
  </div>
</div>
</form>
