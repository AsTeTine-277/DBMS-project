<?php

$page_title = 'Seller Dashboard — HerMart';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/seller-auth.php';

seller_only();

$user = current_user();
$biz  = current_business($user['id']);


$stats = [
    'total_products'    => 0,
    'active_products'   => 0,
    'out_of_stock'      => 0,
    'total_orders'      => 0,
    'pending_orders'    => 0,
    'completed_orders'  => 0,
    'total_bookings'    => 0,
    'pending_bookings'  => 0,
    'total_custom'      => 0,
    'pending_custom'    => 0,
    'revenue'           => 0,
];

$stats['total_products']   = (int)(db_select_one("SELECT COUNT(*) AS c FROM products WHERE seller_id = ?", 'i', [$biz['id']])['c'] ?? 0);
$stats['active_products']  = (int)(db_select_one("SELECT COUNT(*) AS c FROM products WHERE seller_id = ? AND status = 'active'", 'i', [$biz['id']])['c'] ?? 0);
$stats['out_of_stock']     = (int)(db_select_one("SELECT COUNT(*) AS c FROM products WHERE seller_id = ? AND (status = 'out_of_stock' OR stock = 0)", 'i', [$biz['id']])['c'] ?? 0);

$revRow = db_select_one(
    "SELECT COUNT(*) AS c, COALESCE(SUM(oi.subtotal), 0) AS rev
     FROM order_items oi WHERE oi.seller_id = ?",
    'i', [$biz['id']]
);
$stats['total_orders'] = (int)($revRow['c'] ?? 0);
$stats['revenue']      = (float)($revRow['rev'] ?? 0);

$stats['pending_orders'] = (int)(db_select_one(
    "SELECT COUNT(*) AS c FROM order_items oi
     JOIN orders o ON o.id = oi.order_id
     WHERE oi.seller_id = ? AND o.order_status IN ('pending','confirmed','processing')",
    'i', [$biz['id']]
)['c'] ?? 0);
$stats['completed_orders'] = (int)(db_select_one(
    "SELECT COUNT(*) AS c FROM order_items oi
     JOIN orders o ON o.id = oi.order_id
     WHERE oi.seller_id = ? AND o.order_status = 'delivered'",
    'i', [$biz['id']]
)['c'] ?? 0);

$stats['total_bookings']   = (int)(db_select_one("SELECT COUNT(*) AS c FROM bookings WHERE vendor_id = ?", 'i', [$biz['id']])['c'] ?? 0);
$stats['pending_bookings'] = (int)(db_select_one("SELECT COUNT(*) AS c FROM bookings WHERE vendor_id = ? AND status = 'pending'", 'i', [$biz['id']])['c'] ?? 0);

$stats['total_custom']    = (int)(db_select_one("SELECT COUNT(*) AS c FROM custom_orders WHERE seller_id = ?", 'i', [$biz['id']])['c'] ?? 0);
$stats['pending_custom']  = (int)(db_select_one("SELECT COUNT(*) AS c FROM custom_orders WHERE seller_id = ? AND status IN ('pending','reviewed')", 'i', [$biz['id']])['c'] ?? 0);

$recent_orders = db_select(
    "SELECT o.id AS order_id, o.order_number, o.created_at, o.order_status, o.shipping_address,
            p.name AS product_name, oi.quantity, oi.unit_price, oi.subtotal,
            u.name AS customer_name
     FROM order_items oi
     JOIN products p ON p.id = oi.product_id
     JOIN orders   o ON o.id = oi.order_id
     LEFT JOIN users u ON u.id = o.customer_id
     WHERE oi.seller_id = ?
     ORDER BY o.created_at DESC LIMIT 6",
    'i', [$biz['id']]
);

$recent_bookings = db_select(
    "SELECT bk.*, u.name AS customer_name FROM bookings bk
     LEFT JOIN users u ON u.id = bk.customer_id
     WHERE bk.vendor_id = ? ORDER BY bk.created_at DESC LIMIT 6",
    'i', [$biz['id']]
);

$recent_custom = db_select(
    "SELECT co.*, u.name AS customer_name FROM custom_orders co
     LEFT JOIN users u ON u.id = co.customer_id
     WHERE co.seller_id = ? ORDER BY co.created_at DESC LIMIT 6",
    'i', [$biz['id']]
);

$my_purchases = db_select(
  "SELECT o.id, o.order_number, o.created_at, o.order_status, o.total_amount,
      COUNT(oi.id) AS item_count
   FROM orders o
   LEFT JOIN order_items oi ON oi.order_id = o.id
   WHERE o.customer_id = ?
   GROUP BY o.id, o.order_number, o.created_at, o.order_status, o.total_amount
   ORDER BY o.created_at DESC LIMIT 5",
  'i', [$user['id']]
);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/_layout_top.php';
?>

<!-- Stat cards -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
  <div class="hm-card p-5">
    <span class="material-symbols-outlined text-primary">inventory_2</span>
    <div class="text-2xl font-bold mt-2"><?= $stats['total_products'] ?></div>
    <div class="text-xs text-on-surface-variant">Total Products</div>
    <div class="text-[11px] text-emerald-600 mt-1"><?= $stats['active_products'] ?> active</div>
  </div>
  <div class="hm-card p-5">
    <span class="material-symbols-outlined text-amber-500">pending_actions</span>
    <div class="text-2xl font-bold mt-2"><?= $stats['pending_orders'] ?></div>
    <div class="text-xs text-on-surface-variant">Pending Orders</div>
    <div class="text-[11px] text-on-surface-variant mt-1"><?= $stats['total_orders'] ?> total</div>
  </div>
  <div class="hm-card p-5">
    <span class="material-symbols-outlined text-indigo-500">event</span>
    <div class="text-2xl font-bold mt-2"><?= $stats['pending_bookings'] ?></div>
    <div class="text-xs text-on-surface-variant">Pending Bookings</div>
    <div class="text-[11px] text-on-surface-variant mt-1"><?= $stats['total_bookings'] ?> total</div>
  </div>
  <div class="hm-card p-5">
    <span class="material-symbols-outlined text-emerald-500">payments</span>
    <div class="text-2xl font-bold mt-2"><?= money_tk($stats['revenue']) ?></div>
    <div class="text-xs text-on-surface-variant">Total Revenue</div>
    <div class="text-[11px] text-on-surface-variant mt-1"><?= $stats['completed_orders'] ?> delivered</div>
  </div>
</div>

<!-- Quick links -->
<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
  <a href="add-product.php" class="hm-card p-5 flex items-center gap-3">
    <span class="w-10 h-10 rounded-xl bg-primary-light text-primary flex items-center justify-center">
      <span class="material-symbols-outlined">add_box</span>
    </span>
    <div>
      <div class="font-bold">Add Product</div>
      <div class="text-xs text-on-surface-variant">Publish to your shop</div>
    </div>
  </a>
  <a href="orders.php" class="hm-card p-5 flex items-center gap-3">
    <span class="w-10 h-10 rounded-xl bg-primary-light text-primary flex items-center justify-center">
      <span class="material-symbols-outlined">receipt_long</span>
    </span>
    <div>
      <div class="font-bold">Manage Orders</div>
      <div class="text-xs text-on-surface-variant">Update status & shipping</div>
    </div>
  </a>
  <a href="custom-orders.php" class="hm-card p-5 flex items-center gap-3">
    <span class="w-10 h-10 rounded-xl bg-primary-light text-primary flex items-center justify-center">
      <span class="material-symbols-outlined">auto_awesome</span>
    </span>
    <div>
      <div class="font-bold">Custom Requests</div>
      <div class="text-xs text-on-surface-variant"><?= $stats['pending_custom'] ?> awaiting response</div>
    </div>
  </a>
  <a href="../customer/orders.php" class="hm-card p-5 flex items-center gap-3">
    <span class="w-10 h-10 rounded-xl bg-primary-light text-primary flex items-center justify-center">
      <span class="material-symbols-outlined">shopping_bag</span>
    </span>
    <div>
      <div class="font-bold">My Purchases</div>
      <div class="text-xs text-on-surface-variant">View items you bought</div>
    </div>
  </a>
</div>

<!-- Purchases made from this seller account -->
<div class="bg-white rounded-3xl border border-outline/40 p-6">
  <div class="flex items-center justify-between mb-4">
    <h2 class="font-bold text-lg">My Purchases</h2>
    <a href="../customer/orders.php" class="text-xs font-bold text-primary hover:underline">View all →</a>
  </div>
  <?php if (empty($my_purchases)): ?>
    <p class="text-on-surface-variant text-sm py-6 text-center">You have not bought anything yet.</p>
  <?php else: ?>
    <div class="divide-y divide-outline/40">
      <?php foreach ($my_purchases as $purchase): ?>
        <a href="../customer/order-details.php?id=<?= (int)$purchase['id'] ?>" class="py-3 flex items-center gap-3 hover:bg-primary-light/40 rounded-xl px-2">
          <div class="w-9 h-9 rounded-lg bg-primary-light flex items-center justify-center">
            <span class="material-symbols-outlined text-primary">shopping_bag</span>
          </div>
          <div class="flex-1 min-w-0">
            <div class="font-semibold text-sm truncate">Order #<?= h($purchase['order_number']) ?></div>
            <div class="text-xs text-on-surface-variant"><?= (int)$purchase['item_count'] ?> item<?= (int)$purchase['item_count'] === 1 ? '' : 's' ?> · <?= h(date('M j, Y', strtotime($purchase['created_at']))) ?></div>
          </div>
          <span class="hm-pill <?= status_pill_class($purchase['order_status'], 'order') ?>"><?= format_status_label($purchase['order_status']) ?></span>
          <span class="text-sm font-bold text-primary"><?= money_tk($purchase['total_amount']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- Recent orders -->
<div class="bg-white rounded-3xl border border-outline/40 p-6">
  <div class="flex items-center justify-between mb-4">
    <h2 class="font-bold text-lg">Recent Orders</h2>
    <a href="orders.php" class="text-xs font-bold text-primary hover:underline">View all →</a>
  </div>
  <?php if (empty($recent_orders)): ?>
    <p class="text-on-surface-variant text-sm py-6 text-center">No orders yet. They'll appear here when customers buy your products.</p>
  <?php else: ?>
    <div class="divide-y divide-outline/40">
      <?php foreach ($recent_orders as $o): ?>
        <div class="py-3 flex items-center gap-3">
          <div class="w-9 h-9 rounded-lg bg-primary-light flex items-center justify-center">
            <span class="material-symbols-outlined text-primary">package_2</span>
          </div>
          <div class="flex-1 min-w-0">
            <div class="font-semibold text-sm truncate"><?= h($o['product_name']) ?></div>
            <div class="text-xs text-on-surface-variant">#<?= h($o['order_number']) ?> · <?= h($o['customer_name'] ?? 'Customer') ?> · <?= h(date('M j', strtotime($o['created_at']))) ?></div>
          </div>
          <span class="hm-pill <?= status_pill_class($o['order_status'], 'order') ?>"><?= format_status_label($o['order_status']) ?></span>
          <span class="text-sm font-bold text-primary"><?= money_tk($o['subtotal']) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- Recent bookings -->
<div class="bg-white rounded-3xl border border-outline/40 p-6">
  <div class="flex items-center justify-between mb-4">
    <h2 class="font-bold text-lg">Recent Bookings</h2>
    <a href="bookings.php" class="text-xs font-bold text-primary hover:underline">View all →</a>
  </div>
  <?php if (empty($recent_bookings)): ?>
    <p class="text-on-surface-variant text-sm py-6 text-center">No bookings yet.</p>
  <?php else: ?>
    <div class="divide-y divide-outline/40">
      <?php foreach ($recent_bookings as $b): ?>
        <div class="py-3 flex items-center gap-3">
          <div class="w-9 h-9 rounded-lg bg-primary-light flex items-center justify-center">
            <span class="material-symbols-outlined text-primary">event</span>
          </div>
          <div class="flex-1 min-w-0">
            <div class="font-semibold text-sm truncate"><?= h($b['event_type']) ?></div>
            <div class="text-xs text-on-surface-variant"><?= h($b['customer_name'] ?? 'Customer') ?> · <?= h($b['booking_date']) ?> <?= h(substr($b['booking_time'] ?? '', 0, 5)) ?></div>
          </div>
          <span class="hm-pill <?= status_pill_class($b['status'], 'booking') ?>"><?= format_status_label($b['status']) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- Recent custom orders -->
<div class="bg-white rounded-3xl border border-outline/40 p-6">
  <div class="flex items-center justify-between mb-4">
    <h2 class="font-bold text-lg">Recent Custom Requests</h2>
    <a href="custom-orders.php" class="text-xs font-bold text-primary hover:underline">View all →</a>
  </div>
  <?php if (empty($recent_custom)): ?>
    <p class="text-on-surface-variant text-sm py-6 text-center">No custom requests yet.</p>
  <?php else: ?>
    <div class="divide-y divide-outline/40">
      <?php foreach ($recent_custom as $c): ?>
        <div class="py-3 flex items-center gap-3">
          <div class="w-9 h-9 rounded-lg bg-primary-light flex items-center justify-center">
            <span class="material-symbols-outlined text-primary">auto_awesome</span>
          </div>
          <div class="flex-1 min-w-0">
            <div class="font-semibold text-sm truncate"><?= h($c['title']) ?></div>
            <div class="text-xs text-on-surface-variant"><?= h($c['customer_name'] ?? 'Customer') ?> · <?= h(date('M j', strtotime($c['created_at']))) ?></div>
          </div>
          <span class="hm-pill <?= status_pill_class($c['status'], 'custom') ?>"><?= format_status_label($c['status']) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
