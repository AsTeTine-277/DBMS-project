<?php

$page_title = 'My Orders — HerMart';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/seller-auth.php';

seller_only();

$user = current_user();
$biz  = current_business($user['id']);
$biz_id = (int)$biz['id'];

$status_filter = $_GET['status'] ?? '';
$allowed = ['pending','confirmed','processing','shipped','out_for_delivery','delivered','cancelled'];
if (!in_array($status_filter, $allowed, true)) {
    $status_filter = '';
}

$where  = ['oi.seller_id = ?'];
$args   = [$biz_id];
$types  = 'i';
if ($status_filter !== '') {
    $where[] = 'o.order_status = ?';
    $args[]  = $status_filter;
    $types  .= 's';
}
$whereSql = implode(' AND ', $where);


$rows = db_select(
    "SELECT o.id AS order_id, o.order_number, o.created_at, o.order_status, o.payment_method,
            o.shipping_address, o.district, o.upazila,
            u.name AS customer_name,
            COUNT(oi.id)              AS item_count,
            COALESCE(SUM(oi.quantity), 0) AS item_qty,
            COALESCE(SUM(oi.subtotal), 0) AS item_subtotal
     FROM order_items oi
     JOIN orders   o ON o.id = oi.order_id
     LEFT JOIN users u ON u.id = o.customer_id
     WHERE $whereSql
     GROUP BY o.id, o.order_number, o.created_at, o.order_status, o.payment_method,
              o.shipping_address, o.district, o.upazila, u.name
     ORDER BY o.created_at DESC",
    $types, $args
);


$counts = db_select_one(
    "SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN o.order_status IN ('pending','confirmed','processing','shipped','out_for_delivery') THEN 1 ELSE 0 END) AS open_orders,
        SUM(CASE WHEN o.order_status = 'delivered' THEN 1 ELSE 0 END) AS completed_orders
     FROM orders o
     WHERE EXISTS (SELECT 1 FROM order_items oi WHERE oi.order_id = o.id AND oi.seller_id = ?)",
    'i', [$biz_id]
);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/_layout_top.php';
?>

<div class="flex items-center justify-between gap-3 flex-wrap">
  <div>
    <h2 class="text-xl font-bold">My Orders</h2>
    <p class="text-xs text-on-surface-variant">
      <?= (int)($counts['total'] ?? 0) ?> total ·
      <?= (int)($counts['open_orders'] ?? 0) ?> open ·
      <?= (int)($counts['completed_orders'] ?? 0) ?> delivered
    </p>
  </div>
</div>

<form method="get" class="flex items-center gap-2 bg-white rounded-2xl border border-outline/40 p-3">
  <label class="text-xs font-bold text-on-surface-variant">Filter by status:</label>
  <select name="status" class="hm-select w-auto" onchange="this.form.submit()">
    <option value="">All</option>
    <?php foreach ($allowed as $s): ?>
      <option value="<?= $s ?>" <?= $status_filter === $s ? 'selected' : '' ?>><?= format_status_label($s) ?></option>
    <?php endforeach; ?>
  </select>
</form>

<?php if (empty($rows)): ?>
  <div class="bg-white rounded-3xl border border-outline/40 p-12 text-center">
    <span class="material-symbols-outlined text-primary text-5xl">receipt_long</span>
    <h3 class="font-bold text-xl mt-3">No orders yet</h3>
    <p class="text-on-surface-variant mt-1">When customers buy your products, the orders will show up here.</p>
  </div>
<?php else: ?>
  <div class="bg-white rounded-3xl border border-outline/40 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="text-xs uppercase tracking-wider text-on-surface-variant bg-surface-container/40 border-b border-outline/40">
          <tr>
            <th class="text-left py-3 px-4">Order</th>
            <th class="text-left">Customer</th>
            <th class="text-left">Ship To</th>
            <th class="text-right">Items</th>
            <th class="text-right">Qty</th>
            <th class="text-right">Subtotal</th>
            <th class="text-left">Payment</th>
            <th class="text-left">Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-outline/40">
          <?php foreach ($rows as $o): ?>
            <tr class="hover:bg-surface-container/30">
              <td class="py-3 px-4">
                <div class="font-bold">#<?= h($o['order_number']) ?></div>
                <div class="text-xs text-on-surface-variant"><?= h(date('M j, Y', strtotime($o['created_at']))) ?></div>
              </td>
              <td><?= h($o['customer_name'] ?? 'Customer') ?></td>
              <td class="max-w-[220px]">
                <div class="truncate text-xs"><?= h($o['shipping_address'] ?? '—') ?></div>
                <div class="text-[11px] text-on-surface-variant"><?= h(trim(($o['upazila'] ?? '') . ', ' . ($o['district'] ?? ''), ', ')) ?></div>
              </td>
              <td class="text-right"><?= (int)$o['item_count'] ?></td>
              <td class="text-right"><?= (int)$o['item_qty'] ?></td>
              <td class="text-right font-bold text-primary"><?= money_tk($o['item_subtotal']) ?></td>
              <td>
                <span class="hm-pill bg-surface-container/60 text-on-surface">
                  <?= strtoupper(h($o['payment_method'])) ?>
                </span>
              </td>
              <td>
                <span class="hm-pill <?= status_pill_class($o['order_status'], 'order') ?>">
                  <?= format_status_label($o['order_status']) ?>
                </span>
              </td>
              <td class="pr-4 text-right">
                <a href="order-details.php?id=<?= (int)$o['order_id'] ?>" class="text-primary font-bold text-xs hover:underline">View</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
