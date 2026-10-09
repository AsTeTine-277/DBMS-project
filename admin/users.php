<?php
/**
 * HerMart — Admin: Users
 *
 * Lists users with filters and supports role changes / activation toggle.
 * Self-deletion or self-demotion of the current admin is blocked.
 */
$page_title = 'Users — Admin — HerMart';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/admin-auth.php';

admin_only();

$user  = current_user();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action'] ?? '';
    $user_id = safe_int($_POST['user_id'] ?? 0);

    if ($user_id > 0 && $user_id !== (int)$user['id']) {
        if ($action === 'change_role') {
            $role = $_POST['role'] ?? '';
            if (in_array($role, ['customer','seller','admin'], true)) {
                db_execute("UPDATE users SET role = ? WHERE id = ?", 'si', [$role, $user_id]);
                $success = 'Role updated.';
            } else {
                $error = 'Invalid role.';
            }
        } elseif ($action === 'delete') {
            db_execute("DELETE FROM users WHERE id = ?", 'i', [$user_id]);
            $success = 'User removed.';
        }
    } else {
        $error = 'You cannot modify your own account from here.';
    }
}

$role_filter = $_GET['role'] ?? '';
$search      = trim($_GET['q'] ?? '');

$where = ['1=1'];
$args  = [];
$types = '';
if (in_array($role_filter, ['customer','seller','admin'], true)) {
    $where[] = 'role = ?';
    $args[]  = $role_filter;
    $types  .= 's';
}
if ($search !== '') {
    $like = '%' . str_replace(['%','_'], ['\%','\_'], $search) . '%';
    $where[] = '(name LIKE ? OR email LIKE ?)';
    $args[]  = $like;
    $args[]  = $like;
    $types  .= 'ss';
}
$whereSql = implode(' AND ', $where);

$rows = db_select(
    "SELECT * FROM users WHERE $whereSql ORDER BY created_at DESC LIMIT 200",
    $types, $args
);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/_layout_top.php';
?>

<?php if ($error): ?>
  <div class="rounded-xl px-4 py-3 text-sm font-medium border bg-red-50 text-red-700 border-red-200"><?= h($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
  <div class="rounded-xl px-4 py-3 text-sm font-medium border bg-emerald-50 text-emerald-700 border-emerald-200"><?= h($success) ?></div>
<?php endif; ?>

<div>
  <h2 class="text-xl font-bold">Users</h2>
  <p class="text-xs text-on-surface-variant"><?= count($rows) ?> listed</p>
</div>

<form method="get" class="bg-white rounded-2xl border border-outline/40 p-3 flex flex-wrap gap-2">
  <input type="text" name="q" value="<?= h($search) ?>" placeholder="Search by name or email" class="hm-input flex-1 min-w-[200px]">
  <select name="role" class="hm-select w-auto">
    <option value="">All roles</option>
    <option value="customer" <?= $role_filter === 'customer' ? 'selected' : '' ?>>Customer</option>
    <option value="seller"   <?= $role_filter === 'seller'   ? 'selected' : '' ?>>Seller</option>
    <option value="admin"    <?= $role_filter === 'admin'    ? 'selected' : '' ?>>Admin</option>
  </select>
  <button type="submit" class="hm-btn hm-btn-primary">Filter</button>
</form>

<div class="bg-white rounded-3xl border border-outline/40 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-xs uppercase tracking-wider text-on-surface-variant bg-surface-container/40 border-b border-outline/40">
        <tr>
          <th class="text-left py-3 px-4">User</th>
          <th class="text-left">Email</th>
          <th class="text-left">Phone</th>
          <th class="text-left">Role</th>
          <th class="text-left">Joined</th>
          <th></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-outline/40">
        <?php foreach ($rows as $u): ?>
          <tr>
            <td class="py-3 px-4 font-semibold">
              <?= h($u['name']) ?>
              <?php if ((int)$u['id'] === (int)$user['id']): ?>
                <span class="hm-pill bg-primary-light text-primary ml-2">You</span>
              <?php endif; ?>
            </td>
            <td class="text-xs"><?= h($u['email']) ?></td>
            <td class="text-xs"><?= h($u['phone'] ?? '—') ?></td>
            <td class="text-xs"><?= h(ucfirst($u['role'])) ?></td>
            <td class="text-xs"><?= h(date('M j, Y', strtotime($u['created_at']))) ?></td>
            <td class="pr-4 text-right">
              <?php if ((int)$u['id'] !== (int)$user['id']): ?>
                <div class="admin-actions">
                  <form method="post" onsubmit="return true;">
                    <input type="hidden" name="action" value="change_role">
                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                    <select name="role" class="hm-select w-auto py-1 text-xs">
                      <?php foreach (['customer','seller','admin'] as $r): ?>
                        <option value="<?= $r ?>" <?= $u['role'] === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
                      <?php endforeach; ?>
                    </select>
                    <button type="submit" class="hm-btn hm-btn-outline text-xs mt-1">Save role</button>
                  </form>

                  <form method="post" onsubmit="return confirm('Delete this user?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                    <button class="hm-btn hm-btn-outline text-red-500 text-xs">Delete user</button>
                  </form>
                </div>
              <?php else: ?>
                <span class="text-xs text-on-surface-variant">—</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
