<div class="tbl"><table>
  <thead><tr><th>Enquiry</th><th>Customer</th><th>Mobile</th><th>Service</th><th>Vehicle</th><th>Source</th><th>Status</th><th>Time</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($list as $r): ?>
    <tr onclick="if(!event.target.closest('a'))location='/admin/enquiries/<?= e($r['code']) ?>'">
      <td><b><?= e($r['code']) ?></b></td>
      <td><?= e($r['name']) ?></td>
      <td class="nw"><?= e(fmt_mobile($r['mobile'])) ?></td>
      <td><?= e($r['service_names']) ?></td>
      <td class="nw"><?= e(trim(explode(' /', $r['vehicle_type'])[0] . ' · ' . $r['vehicle_number'], ' ·')) ?></td>
      <td><span class="src"><?= e(SOURCES[$r['source']] ?? $r['source']) ?></span></td>
      <td><?= status_badge($r['status']) ?></td>
      <td class="nw muted"><?= e(fmt_time($r['created_at'])) ?></td>
      <td class="nw acts">
        <a href="<?= wa_link($r['mobile']) ?>" target="_blank" rel="noopener" class="ab wa" title="WhatsApp"><?= icon('whatsapp', 15) ?></a>
        <a href="<?= tel($r['mobile']) ?>" class="ab" title="Call"><?= icon('phone', 15) ?></a>
        <a href="/admin/enquiries/<?= e($r['code']) ?>" class="ab" title="Open"><?= icon('eye', 15) ?></a>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$list): ?><tr><td colspan="9" class="empty">No enquiries found.</td></tr><?php endif; ?>
  </tbody>
</table></div>
