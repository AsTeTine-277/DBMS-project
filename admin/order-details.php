<?php
/**
 * HerMart — Admin: Order details (read-only)
 *
 * Drill-in for `admin/orders.php`. Shows the order, items, payments
 * and delivery. No mutation actions — admins update via the parent
 * orders page.
 */
$page_title = 'Order Details — Admin — HerMart';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/admin-auth.php';

admin_only();

$id = safe_int($_GET['id'] ?? 0);
if ($id <= 0) {
    flash_set('error', 'Invalid order id.');
    redirect('admin/orders.php');
}

$order = db_select_one(
    "SELECT o.*, u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone
     FROM orders o LEFT JOIN users u ON u.id = o.customer_id
     WHERE o.id = ? LIMIT 1",
    'i', [$id]
);
if (!$order) {
    flash_set('error', 'Order not found.');
    redirect('admin/orders.php');
}

$items = db_select(
    "SELECT oi.*, p.name AS product_name, p.image AS product_image,
            b.shop_name AS seller_name
     FROM order_items oi
     LEFT JOIN products p    ON p.id = oi.product_id
     LEFT JOIN businesses b  ON b.id = oi.seller_id
     WHERE oi.order_id = ?
     ORDER BY oi.id ASC",
    'i', [$id]
);

$payment = db_select_one("SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1", 'i', [$id]);
$delivery = db_select_one("SELECT * FROM deliveries WHERE order_id = ? ORDER BY id DESC LIMIT 1", 'i', [$id]);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/_layout_top.php';
?>

<section class="hm-container py-6">
  <div class="bg-white rounded-3xl border border-outline/40 p-6 space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <a href="orders.php" class="text-xs font-semibold text-primary hover:underline">← Back to orders</a>
        <h2 class="font-bold text-xl mt-1">Order <?= h($order['order_number'] ?? '#' . $order['id']) ?></h2>
        <p class="text-xs text-on-surface-variant mt-1">Placed <?= h(date('M j, Y g:i a', strtotime($order['created_at']))) ?></p>
      </div>
      <div class="flex flex-wrap gap-2 text-xs">
        <span class="px-3 py-1 rounded-full font-semibold <?= status_pill_class($order['order_status'], 'order') ?>"><?= format_status_label($order['order_status']) ?></span>
        <span class="px-3 py-1 rounded-full font-semibold <?= status_pill_class($order['payment_status'] ?? 'pending', 'payment') ?>">Payment: <?= format_status_label($order['payment_status'] ?? 'pending') ?></span>
      </div>
    </div>

    <div class="grid md:grid-cols-3 gap-4 text-sm">
      <div class="rounded-2xl bg-surface-dim p-4">
        <div class="text-xs uppercase tracking-wider text-on-surface-variant font-bold">Customer</div>
        <div class="font-semibold mt-1"><?= h($order['customer_name']) ?></div>
        <div class="text-xs"><?= h($order['customer_email']) ?></div>
        <div class="text-xs"><?= h($order['customer_phone']) ?></div>
      </div>
      <div class="rounded-2xl bg-surface-dim p-4">
        <div class="text-xs uppercase tracking-wider text-on-surface-variant font-bold">Shipping</div>
        <div class="font-semibold mt-1"><?= h($order['shipping_address'] ?: '—') ?></div>
        <div class="text-xs"><?= h(trim(($order['district'] ?? '') . ' ' . ($order['upazila'] ?? ''))) ?: '—' ?></div>
        <div class="text-xs">Phone: <?= h($order['phone'] ?? '—') ?></div>
      </div>
      <div class="rounded-2xl bg-surface-dim p-4">
        <div class="text-xs uppercase tracking-wider text-on-surface-variant font-bold">Totals</div>
        <div class="text-xs mt-1">Items: <?= count($items) ?></div>
        <div class="text-xs">Total: <span class="font-bold text-primary"><?= money_tk($order['total_amount']) ?></span></div>
        <?php if ($delivery): ?>
          <div class="text-xs mt-1">Tracking: <?= h($delivery['tracking_number']) ?></div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="bg-white rounded-3xl border border-outline/40 p-6 mt-4">
    <h3 class="font-bold text-lg mb-3">Items</h3>
    <?php if (empty($items)): ?>
      <p class="text-sm text-on-surface-variant">No items for this order.</p>
    <?php else: ?>
      <ul class="divide-y divide-outline/40">
        <?php foreach ($items as $it): ?>
          <li class="py-3 flex items-center gap-4">
            <img <?= product_image_attrs($it['product_image']) ?> alt="" class="w-12 h-12 rounded-lg object-cover">
            <div class="flex-1">
              <div class="font-semibold text-sm"><?= h($it['product_name'] ?? 'Item #'.$it['product_id']) ?></div>
              <div class="text-xs text-on-surface-variant">Seller: <?= h($it['seller_name'] ?: '—') ?></div>
            </div>
            <div class="text-right text-sm">
              <div><?= (int)$it['quantity'] ?> × <?= money_tk($it['unit_price']) ?></div>
              <div class="font-bold"><?= money_tk($it['subtotal']) ?></div>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>

  <div class="grid md:grid-cols-2 gap-4 mt-4">
    <div class="bg-white rounded-3xl border border-outline/40 p-5">
      <h3 class="font-bold text-lg mb-2">Payment</h3>
      <?php if ($payment): ?>
        <div class="text-sm">
          <div>Method: <span class="font-semibold"><?= h(strtoupper($payment['payment_method'])) ?></span></div>
          <div>Amount: <span class="font-bold text-primary"><?= money_tk($payment['amount']) ?></span></div>
          <div>Reference: <?= h($payment['transaction_reference']) ?></div>
          <div>Status: <span class="px-2 py-1 rounded-full text-xs font-semibold <?= status_pill_class($payment['payment_status'], 'payment') ?>"><?= format_status_label($payment['payment_status']) ?></span></div>
        </div>
      <?php else: ?>
        <p class="text-sm text-on-surface-variant">No payment record yet.</p>
      <?php endif; ?>
    </div>
    <div class="bg-white rounded-3xl border border-outline/40 p-5">
      <h3 class="font-bold text-lg mb-2">Delivery</h3>
      <?php if ($delivery): ?>
        <div class="text-sm">
          <div>Tracking: <span class="font-semibold"><?= h($delivery['tracking_number']) ?></span></div>
          <div>Status: <span class="px-2 py-1 rounded-full text-xs font-semibold <?= status_pill_class($delivery['status'], 'order') ?>"><?= format_status_label($delivery['status']) ?></span></div>
          <div>ETA: <?= h($delivery['estimated_delivery'] ?: '—') ?></div>
        </div>
      <?php else: ?>
        <p class="text-sm text-on-surface-variant">No delivery record yet.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php
include __DIR__ . '/_layout_bottom.php';
include __DIR__ . '/../includes/footer.php';
?>
