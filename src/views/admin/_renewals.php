<div class="tbl"><table>
  <thead><tr><th>Customer</th><th>Vehicle</th><th>Due</th><th>Expiry</th><th>Last sent</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): [$cls, $lab] = expiry_state($r['due']); ?>
    <tr>
      <td><a href="/admin/customers/<?= $r['cid'] ?>"><b><?= e($r['name'] ?: '—') ?></b></a><small class="block muted"><?= e(fmt_mobile($r['mobile'])) ?></small></td>
      <td class="nw"><?= e(fmt_vehicle($r['number'])) ?></td>
      <td class="nw"><?= e(EXPIRY_KINDS[$r['kind']]) ?> · <?= e(fmt_date($r['due'], 'd M')) ?></td>
      <td><span class="pill <?= $cls ?>"><?= e($lab) ?></span></td>
      <td class="nw muted small" data-sent><?= $r['last_sent'] ? e(fmt_time($r['last_sent'])) : ($r['notify_whatsapp'] ? '—' : 'Opted out') ?></td>
      <td class="nw"><a class="ab wa" target="_blank" rel="noopener" href="<?= wa_link($r['mobile'], reminder_text($r)) ?>" data-remind='<?= e(json_encode(['vid' => $r['vid'], 'kind' => $r['kind'], 'due' => $r['due']])) ?>' title="Send reminder on WhatsApp"><?= icon('whatsapp', 15) ?></a></td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="6" class="empty">No renewals due.</td></tr><?php endif; ?>
  </tbody>
</table></div>
