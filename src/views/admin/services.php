<div class="filters"><span class="muted">Turn services on/off, edit text, documents and fees shown on the website and app.</span><a class="btn red sm" href="/admin/services/new"><?= icon('plus', 15) ?> Add service</a></div>
<form method="post" id="orderf"><?= csrf_field() ?><input type="hidden" name="action" value="order">
<div class="tbl"><table>
  <thead><tr><th>Order</th><th>Service</th><th>Category</th><th>Charges / fee note</th><th>Enquiries</th><th>Status</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($list as $s): ?>
    <tr>
      <td><input class="ord" type="number" name="sort[<?= $s['id'] ?>]" value="<?= (int)$s['sort'] ?>"></td>
      <td><span class="sico <?= e($s['color']) ?>"><?= icon($s['icon'], 16) ?></span> <b><?= e($s['name']) ?></b><small class="block muted">/<?= e($s['slug']) ?></small></td>
      <td><?= e($s['cat']) ?></td>
      <td><?= e($s['fee_note'] ?: '—') ?></td>
      <td><?= (int)$s['n'] ?></td>
      <td><button form="t<?= $s['id'] ?>" class="pill <?= $s['active'] ? 'valid' : 'expired' ?>" title="Click to switch"><?= $s['active'] ? 'Active' : 'Hidden' ?></button></td>
      <td class="nw"><a class="btn outline sm" href="/admin/services/<?= $s['id'] ?>"><?= icon('edit', 14) ?> Edit</a> <a class="ab" href="/<?= e($s['slug']) ?>" target="_blank" title="View on website"><?= icon('eye', 15) ?></a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div>
<button class="btn green sm mt">Save order</button>
</form>
<?php foreach ($list as $s): ?><form method="post" id="t<?= $s['id'] ?>"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $s['id'] ?>"></form><?php endforeach; ?>
