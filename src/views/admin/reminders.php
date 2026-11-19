<div class="filters">
  <form class="inline">
    <select name="days" onchange="this.form.submit()"><?php foreach ([7, 15, 30, 60] as $d): ?><option value="<?= $d ?>" <?= $days === $d ? 'selected' : '' ?>>Due in next <?= $d ?> days</option><?php endforeach; ?></select>
    <select name="kind" onchange="this.form.submit()"><option value="">All types</option><?php foreach (EXPIRY_KINDS as $k => $t): ?><option value="<?= $k ?>" <?= get('kind') === $k ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?></select>
    <select name="show" onchange="this.form.submit()"><option value="">All</option><option value="unsent" <?= get('show') === 'unsent' ? 'selected' : '' ?>>Not yet sent</option></select>
  </form>
  <span class="muted"><?= count($list) ?> reminders · includes expired in last 30 days</span>
</div>
<div class="note-box">Click the green WhatsApp button — WhatsApp opens with the reminder message ready. Press send, and it is marked as sent here. Edit the message text in <a class="lnk" href="/admin/content#templates">Website content</a>.</div>
<?= view('admin/_renewals', ['rows' => $list]) ?>
