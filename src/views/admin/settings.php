<div class="grid2">
  <form method="post" class="panel">
    <?= csrf_field() ?><input type="hidden" name="action" value="password">
    <h3>Change my password</h3>
    <label class="f"><span>Current password</span><input type="password" name="current" required autocomplete="current-password"></label>
    <label class="f"><span>New password (8+ characters)</span><input type="password" name="new" required minlength="8" autocomplete="new-password"></label>
    <button class="btn green">Change password</button>
  </form>
  <div class="panel">
    <h3>Staff logins</h3>
    <div class="tbl flat"><table>
      <thead><tr><th>Name</th><th>Username</th><th>Role</th><th>Status</th><?php if (is_owner()): ?><th></th><?php endif; ?></tr></thead>
      <tbody><?php foreach ($staff as $s): ?>
        <tr><td><b><?= e($s['name']) ?></b></td><td><?= e($s['username']) ?></td><td><?= e(ucfirst($s['role'])) ?></td><td><span class="pill <?= $s['active'] ? 'valid' : 'expired' ?>"><?= $s['active'] ? 'Active' : 'Disabled' ?></span></td>
        <?php if (is_owner()): ?><td class="nw">
          <?php if ((int)$s['id'] !== (int)admin()['id']): ?><form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="action" value="toggle_staff"><input type="hidden" name="id" value="<?= $s['id'] ?>"><button class="lnk"><?= $s['active'] ? 'Disable' : 'Enable' ?></button></form><?php endif; ?>
          <details class="inl"><summary class="lnk">Reset password</summary><form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="action" value="reset_staff"><input type="hidden" name="id" value="<?= $s['id'] ?>"><input type="password" name="password" placeholder="New password" minlength="8" required><button class="btn green sm">Set</button></form></details>
        </td><?php endif; ?></tr>
      <?php endforeach; ?></tbody>
    </table></div>
    <?php if (is_owner()): ?>
    <form method="post" class="fgrid mt">
      <?= csrf_field() ?><input type="hidden" name="action" value="staff">
      <label class="f"><span>Name</span><input name="name" required placeholder="Dipak"></label>
      <label class="f"><span>Username</span><input name="username" required></label>
      <label class="f"><span>Password</span><input type="password" name="password" required minlength="8"></label>
      <label class="f"><span>Role</span><select name="role"><option value="staff">Staff</option><option value="owner">Owner (can manage staff)</option></select></label>
      <button class="btn red sm">Add staff</button>
    </form>
    <?php endif; ?>
  </div>
</div>
