<?php
/**
 * HerMart — Admin: Dashboard
 *
 * Aggregates platform-wide stats and recent activity for admin users.
 */
$page_title = 'Admin Dashboard — HerMart';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/admin-auth.php';

admin_only();

$user = current_user();

$stats = [
    'total_users'        => (int)(db_select_one("SELECT COUNT(*) AS c FROM users")['c'] ?? 0),
    'customers'          => (int)(db_select_one("SELECT COUNT(*) AS c FROM users WHERE role = 'customer'")['c'] ?? 0),
    'sellers'            => (int)(db_select_one("SELECT COUNT(*) AS c FROM users WHERE role = 'seller'")['c'] ?? 0),
    'total_products'     => (int)(db_select_one("SELECT COUNT(*) AS c FROM products")['c'] ?? 0),
    'active_products'    => (int)(db_select_one("SELECT COUNT(*) AS c FROM products WHERE status = 'active'")['c'] ?? 0),
    'total_orders'       => (int)(db_select_one("SELECT COUNT(*) AS c FROM orders")['c'] ?? 0),
    'pending_orders'     => (int)(db_select_one("SELECT COUNT(*) AS c FROM orders WHERE order_status IN ('pending','confirmed','processing')")['c'] ?? 0),
    'total_bookings'     => (int)(db_select_one("SELECT COUNT(*) AS c FROM bookings")['c'] ?? 0),
    'total_custom'       => (int)(db_select_one("SELECT COUNT(*) AS c FROM custom_orders")['c'] ?? 0),
    'revenue'            => (float)(db_select_one("SELECT COALESCE(SUM(total_amount),0) AS r FROM orders WHERE order_status NOT IN ('cancelled')")['r'] ?? 0),
];

$recentOrders = db_select(
    "SELECT o.id, o.order_number, o.created_at, o.order_status, o.total_amount,
            u.name AS customer_name
     FROM orders o LEFT JOIN users u ON u.id = o.customer_id
     ORDER BY o.created_at DESC LIMIT 8"
);

$recentUsers = db_select(
    "SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 6"
);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/_layout_top.php';
?>

<div>
  <h2 class="text-xl font-bold">Admin Dashboard</h2>
  <p class="text-xs text-on-surface-variant">Platform-wide overview · <?= h(date('M j, Y')) ?></p>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
  <div class="hm-card p-5">
    <span class="material-symbols-outlined text-primary">group</span>
    <div class="text-2xl font-bold mt-2"><?= $stats['total_users'] ?></div>
    <div class="text-xs text-on-surface-variant">Total Users</div>
    <div class="text-[11px] text-on-surface-variant mt-1"><?= $stats['customers'] ?> customers · <?= $stats['sellers'] ?> sellers</div>
  </div>
  <div class="hm-card p-5">
    <span class="material-symbols-outlined text-indigo-500">inventory_2</span>
    <div class="text-2xl font-bold mt-2"><?= $stats['total_products'] ?></div>
    <div class="text-xs text-on-surface-variant">Products</div>
    <div class="text-[11px] text-on-surface-variant mt-1"><?= $stats['active_products'] ?> active</div>
  </div>
  <div class="hm-card p-5">
    <span class="material-symbols-outlined text-amber-500">receipt_long</span>
    <div class="text-2xl font-bold mt-2"><?= $stats['total_orders'] ?></div>
    <div class="text-xs text-on-surface-variant">Orders</div>
    <div class="text-[11px] text-on-surface-variant mt-1"><?= $stats['pending_orders'] ?> pending</div>
  </div>
  <div class="hm-card p-5">
    <span class="material-symbols-outlined text-emerald-500">payments</span>
    <div class="text-2xl font-bold mt-2"><?= money_tk($stats['revenue']) ?></div>
    <div class="text-xs text-on-surface-variant">Revenue</div>
    <div class="text-[11px] text-on-surface-variant mt-1"><?= $stats['total_bookings'] ?> bookings · <?= $stats['total_custom'] ?> custom</div>
  </div>
</div>

<div class="grid lg:grid-cols-2 gap-4">
  <div class="bg-white rounded-3xl border border-outline/40 p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-bold">Recent Orders</h3>
      <a href="orders.php" class="text-xs font-bold text-primary hover:underline">View all →</a>
    </div>
    <?php if (empty($recentOrders)): ?>
      <p class="text-sm text-on-surface-variant py-6 text-center">No orders yet.</p>
    <?php else: ?>
      <div class="divide-y divide-outline/40">
        <?php foreach ($recentOrders as $o): ?>
          <div class="py-3 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-primary-light flex items-center justify-center">
              <span class="material-symbols-outlined text-primary">package_2</span>
            </div>
            <div class="flex-1 min-w-0">
              <div class="font-semibold text-sm truncate">#<?= h($o['order_number']) ?></div>
              <div class="text-xs text-on-surface-variant"><?= h($o['customer_name'] ?? 'Customer') ?> · <?= h(date('M j', strtotime($o['created_at']))) ?></div>
            </div>
            <span class="hm-pill <?= status_pill_class($o['order_status'], 'order') ?>"><?= format_status_label($o['order_status']) ?></span>
            <span class="text-sm font-bold text-primary"><?= money_tk($o['total_amount']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="bg-white rounded-3xl border border-outline/40 p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-bold">Recent Users</h3>
      <a href="users.php" class="text-xs font-bold text-primary hover:underline">View all →</a>
    </div>
    <?php if (empty($recentUsers)): ?>
      <p class="text-sm text-on-surface-variant py-6 text-center">No users yet.</p>
    <?php else: ?>
      <div class="divide-y divide-outline/40">
        <?php foreach ($recentUsers as $u): ?>
          <div class="py-3 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-primary-light flex items-center justify-center">
              <span class="material-symbols-outlined text-primary">person</span>
            </div>
            <div class="flex-1 min-w-0">
              <div class="font-semibold text-sm truncate"><?= h($u['name']) ?></div>
              <div class="text-xs text-on-surface-variant truncate"><?= h($u['email']) ?></div>
            </div>
            <span class="hm-pill bg-surface-container/60 text-on-surface"><?= h($u['role']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
