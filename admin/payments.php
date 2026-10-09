<?php
/**
 * HerMart — Admin: Payments
 *
 * Lists every payment across the platform with status filter and
 * mutation to mark refunded / failed.
 */
$page_title = 'Payments — Admin — HerMart';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/admin-auth.php';

admin_only();

$user = current_user();
$status = $_GET['status'] ?? '';
if (!in_array($status, ['pending','paid','failed','refunded'], true)) $status = '';
$q = trim($_GET['q'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = safe_int($_POST['payment_id'] ?? 0);
    $new = $_POST['status'] ?? '';
    if ($pid > 0 && in_array($new, ['pending','paid','failed','refunded'], true)) {
        db_execute("UPDATE payments SET payment_status = ? WHERE id = ?", 'si', [$new, $pid]);
        // Mirror onto the order so /orders list agrees
        db_execute("UPDATE orders SET payment_status = ? WHERE id = (SELECT order_id FROM payments WHERE id = ?)", 'si', [$new, $pid]);
        flash_set('success', 'Payment updated.');
    }
    redirect('admin/payments.php' . ($status !== '' ? '?status=' . urlencode($status) : ''));
}

$where = [];
$args  = [];
$types = '';
if ($status !== '') { $where[] = 'p.payment_status = ?'; $args[] = $status; $types .= 's'; }
if ($q !== '') {
    $where[] = '(o.order_number LIKE ? OR u.email LIKE ? OR p.transaction_reference LIKE ?)';
    $args[] = "%$q%"; $args[] = "%$q%"; $args[] = "%$q%"; $types .= 'sss';
}

$sql = "SELECT p.*, o.order_number, o.total_amount, u.name AS customer_name, u.email AS customer_email
        FROM payments p
        LEFT JOIN orders o ON o.id = p.order_id
        LEFT JOIN users  u ON u.id = o.customer_id";
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY p.id DESC LIMIT 50';

$rows = $types ? db_select($sql, $types, $args) : db_select($sql);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/_layout_top.php';
?>

<section class="hm-container py-6">
  <div class="bg-white rounded-3xl border border-outline/40 p-6">
    <div class="flex flex-wrap items-end justify-between gap-3 mb-4">
      <h2 class="font-bold text-xl">Payments</h2>
      <form method="get" class="flex flex-wrap items-end gap-2 text-xs">
        <div>
          <label class="hm-label">Status</label>
          <select name="status" class="hm-input">
            <option value="">All</option>
            <?php foreach (['pending','paid','failed','refunded'] as $s): ?>
              <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="hm-label">Search</label>
          <input name="q" type="search" value="<?= h($q) ?>" class="hm-input" placeholder="Order # / email / ref">
        </div>
        <button class="hm-btn hm-btn-primary">Filter</button>
      </form>
    </div>

    <?php if (empty($rows)): ?>
      <p class="text-sm text-on-surface-variant py-8 text-center">No payments match.</p>
    <?php else: ?>
      <table class="w-full text-sm">
        <thead class="text-left text-xs uppercase text-on-surface-variant border-b border-outline/40">
          <tr>
            <th class="py-2">Order</th>
            <th>Customer</th>
            <th>Method</th>
            <th>Ref</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr class="border-b border-outline/20">
              <td class="py-2"><a href="order-details.php?id=<?= (int)$r['order_id'] ?>" class="text-primary font-semibold hover:underline"><?= h($r['order_number']) ?></a></td>
              <td><?= h($r['customer_name']) ?><div class="text-xs text-on-surface-variant"><?= h($r['customer_email']) ?></div></td>
              <td><?= h(strtoupper($r['payment_method'])) ?></td>
              <td class="text-xs"><?= h($r['transaction_reference']) ?></td>
              <td class="font-semibold"><?= money_tk($r['amount']) ?></td>
              <td><span class="px-2 py-1 rounded-full text-[11px] font-semibold <?= status_pill_class($r['payment_status'], 'payment') ?>"><?= format_status_label($r['payment_status']) ?></span></td>
              <td>
                <div class="admin-actions">
                  <form method="post">
                    <input type="hidden" name="payment_id" value="<?= (int)$r['id'] ?>">
                    <select name="status" class="hm-input text-xs py-1">
                      <?php foreach (['pending','paid','failed','refunded'] as $s): ?>
                        <option value="<?= $s ?>" <?= $r['payment_status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                      <?php endforeach; ?>
                    </select>
                    <button class="hm-btn hm-btn-outline py-1 px-2 text-xs mt-1">Save</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</section>

<?php
include __DIR__ . '/_layout_bottom.php';
include __DIR__ . '/../includes/footer.php';
?>
