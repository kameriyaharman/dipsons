<?php $pages = (int)ceil($total / $per); ?>
<div class="filters"><form class="inline"><input name="q" value="<?= e(get('q')) ?>" placeholder="Search name, mobile or vehicle no." class="wide"><button class="btn outline sm"><?= icon('search', 15) ?> Search</button></form><span class="muted"><?= $total ?> customers</span></div>
<div class="tbl"><table>
  <thead><tr><th>Name</th><th>Mobile</th><th>Vehicles</th><th>Enquiries</th><th>City</th><th>Joined</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($list as $c): ?>
    <tr onclick="if(!event.target.closest('a'))location='/admin/customers/<?= $c['id'] ?>'">
      <td><b><?= e($c['name'] ?: '—') ?></b></td>
      <td class="nw"><?= e(fmt_mobile($c['mobile'])) ?></td>
      <td><?= e($c['vnums'] ?: '—') ?></td>
      <td><?= (int)$c['ec'] ?></td>
      <td><?= e($c['city']) ?></td>
      <td class="nw muted"><?= e(fmt_date($c['created_at'])) ?></td>
      <td class="nw acts"><a href="<?= wa_link($c['mobile']) ?>" target="_blank" rel="noopener" class="ab wa"><?= icon('whatsapp', 15) ?></a><a href="<?= tel($c['mobile']) ?>" class="ab"><?= icon('phone', 15) ?></a></td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$list): ?><tr><td colspan="7" class="empty">No customers found.</td></tr><?php endif; ?>
  </tbody>
</table></div>
<?php if ($pages > 1): ?><div class="pager"><?php for ($i = 1; $i <= $pages; $i++): ?><a href="?q=<?= urlencode(get('q')) ?>&p=<?= $i ?>" class="<?= $i === $page ? 'on' : '' ?>"><?= $i ?></a><?php endfor; ?></div><?php endif; ?>
