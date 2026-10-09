<?php
/**
 * HerMart — Admin: Orders
 *
 * Lists every order on the platform with status and lets admin override
 * the order status. Status updates cascade to the deliveries table.
 */
$page_title = 'Orders — Admin — HerMart';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/admin-auth.php';

admin_only();

$status_filter = $_GET['status'] ?? '';
$allowed = ['pending','confirmed','processing','shipped','out_for_delivery','delivered','cancelled'];
if (!in_array($status_filter, $allowed, true)) {
    $status_filter = '';
}

$where = ['1=1'];
$args  = [];
$types = '';
if ($status_filter !== '') {
    $where[] = 'o.order_status = ?';
    $args[]  = $status_filter;
    $types  .= 's';
}
$whereSql = implode(' AND ', $where);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = safe_int($_POST['order_id'] ?? 0);
    $next     = $_POST['order_status'] ?? '';
    if ($order_id > 0 && in_array($next, $allowed, true)) {
        db_execute("UPDATE orders SET order_status = ? WHERE id = ?", 'si', [$next, $order_id]);
        db_execute("UPDATE deliveries SET status = ? WHERE order_id = ?", 'si', [$next, $order_id]);
        flash_set('success', 'Order status updated.');
    }
    redirect('admin/orders.php' . ($status_filter !== '' ? '?status=' . urlencode($status_filter) : ''));
}

$rows = db_select(
    "SELECT o.*, u.name AS customer_name, u.email AS customer_email
     FROM orders o LEFT JOIN users u ON u.id = o.customer_id
     WHERE $whereSql
     ORDER BY o.created_at DESC LIMIT 200",
    $types, $args
);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/_layout_top.php';
?>

<div>
  <h2 class="text-xl font-bold">Orders</h2>
  <p class="text-xs text-on-surface-variant"><?= count($rows) ?> listed</p>
</div>

<form method="get" class="bg-white rounded-2xl border border-outline/40 p-3 flex items-center gap-2">
  <label class="text-xs font-bold text-on-surface-variant">Status:</label>
  <select name="status" class="hm-select w-auto" onchange="this.form.submit()">
    <option value="">All</option>
    <?php foreach ($allowed as $s): ?>
      <option value="<?= $s ?>" <?= $status_filter === $s ? 'selected' : '' ?>><?= format_status_label($s) ?></option>
    <?php endforeach; ?>
  </select>
</form>

<div class="bg-white rounded-3xl border border-outline/40 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-xs uppercase tracking-wider text-on-surface-variant bg-surface-container/40 border-b border-outline/40">
        <tr>
          <th class="text-left py-3 px-4">Order</th>
          <th class="text-left">Customer</th>
          <th class="text-right">Amount</th>
          <th class="text-left">Payment</th>
          <th class="text-left">Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-outline/40">
        <?php foreach ($rows as $o): ?>
          <tr>
            <td class="py-3 px-4">
              <div class="font-bold">#<?= h($o['order_number']) ?></div>
              <div class="text-xs text-on-surface-variant"><?= h(date('M j, Y g:i A', strtotime($o['created_at']))) ?></div>
            </td>
            <td>
              <div class="text-sm font-semibold"><?= h($o['customer_name'] ?? '—') ?></div>
              <div class="text-xs text-on-surface-variant"><?= h($o['customer_email'] ?? '') ?></div>
            </td>
            <td class="text-right font-bold text-primary"><?= money_tk($o['total_amount']) ?></td>
            <td>
              <span class="hm-pill <?= status_pill_class($o['payment_status'], 'payment') ?>">
                <?= strtoupper(h($o['payment_method'])) ?> · <?= format_status_label($o['payment_status']) ?>
              </span>
            </td>
            <td>
              <span class="hm-pill <?= status_pill_class($o['order_status'], 'order') ?>"><?= format_status_label($o['order_status']) ?></span>
            </td>
            <td class="pr-4 text-right">
              <div class="admin-actions">
                <form method="post">
                  <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                  <select name="order_status" class="hm-select w-auto py-1 text-xs">
                    <?php foreach ($allowed as $s): ?>
                      <option value="<?= $s ?>" <?= $o['order_status'] === $s ? 'selected' : '' ?>><?= format_status_label($s) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <button type="submit" class="hm-btn hm-btn-outline text-xs mt-1">Update</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
