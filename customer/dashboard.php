<?php
/**
 * HerMart — Customer Dashboard
 *
 * The customer's "My Dashboard" landing page after login. Shows recent
 * orders, recent custom orders, recent bookings, and quick links.
 */
$page_title = 'My Dashboard — HerMart';
$page_desc  = 'Your HerMart dashboard: recent orders, custom orders, bookings, and quick links.';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/customer-auth.php';

customer_only();

$user = current_user();
$uid  = (int)$user['id'];

$recentOrders = db_select(
    "SELECT id, order_number, total_amount, order_status, payment_status, created_at
     FROM orders WHERE customer_id = ? ORDER BY created_at DESC LIMIT 5",
    'i', [$uid]
);

$recentCustom = db_select(
    "SELECT id, title, status, created_at FROM custom_orders
     WHERE customer_id = ? ORDER BY created_at DESC LIMIT 5",
    'i', [$uid]
);

$recentBookings = db_select(
    "SELECT b.id, sv.name AS service_name, b.event_type, b.booking_date, b.status
     FROM bookings b LEFT JOIN services sv ON sv.id = b.service_id
     WHERE b.customer_id = ? ORDER BY b.created_at DESC LIMIT 5",
    'i', [$uid]
);

$cartCount = 0;
$cart = db_select_one("SELECT COUNT(*) AS c FROM cart_items WHERE cart_id = (SELECT id FROM cart WHERE customer_id = ?)", 'i', [$uid]);
if ($cart) $cartCount = (int)$cart['c'];

$notifRows = db_select(
    "SELECT id, type, title, body, link, is_read, created_at
     FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 6",
    'i', [$uid]
);

include __DIR__ . '/../includes/header.php';
?>

<section class="bg-gradient-to-b from-primary-light/40 to-transparent">
  <div class="max-w-7xl mx-auto px-4 md:px-6 py-10">
    <p class="text-xs uppercase tracking-widest font-bold text-primary">Welcome back</p>
    <h1 class="font-serif text-3xl md:text-4xl font-bold mt-2">Hello, <?= h($user['name']) ?></h1>
    <p class="text-on-surface-variant mt-2 max-w-2xl">Here's a quick look at your activity on HerMart.</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 md:px-6 py-6 grid grid-cols-2 md:grid-cols-4 gap-3">
  <a href="cart.php" class="hm-card p-4 flex flex-col items-start gap-1">
    <span class="material-symbols-outlined text-primary text-2xl">shopping_cart</span>
    <div class="text-xs text-on-surface-variant">Cart items</div>
    <div class="text-2xl font-bold"><?= (int)$cartCount ?></div>
  </a>
  <a href="orders.php" class="hm-card p-4 flex flex-col items-start gap-1">
    <span class="material-symbols-outlined text-primary text-2xl">receipt_long</span>
    <div class="text-xs text-on-surface-variant">Orders</div>
    <div class="text-2xl font-bold"><?= count($recentOrders) ?></div>
  </a>
  <a href="custom-order.php" class="hm-card p-4 flex flex-col items-start gap-1">
    <span class="material-symbols-outlined text-primary text-2xl">auto_awesome</span>
    <div class="text-xs text-on-surface-variant">Custom orders</div>
    <div class="text-2xl font-bold"><?= count($recentCustom) ?></div>
  </a>
  <a href="profile.php" class="hm-card p-4 flex flex-col items-start gap-1">
    <span class="material-symbols-outlined text-primary text-2xl">person</span>
    <div class="text-xs text-on-surface-variant">Profile</div>
    <div class="text-sm font-semibold">Manage</div>
  </a>
</section>

<section class="max-w-7xl mx-auto px-4 md:px-6 py-6 grid lg:grid-cols-2 gap-6">
  <div class="bg-white rounded-3xl border border-outline/40 p-5">
    <div class="flex items-center justify-between mb-3">
      <h2 class="font-bold text-lg">Recent orders</h2>
      <a href="orders.php" class="text-xs font-bold text-primary hover:underline">View all</a>
    </div>
    <?php if (empty($recentOrders)): ?>
      <p class="text-sm text-on-surface-variant">You haven't placed any orders yet. <a href="home.php" class="text-primary font-bold">Browse the shop</a>.</p>
    <?php else: ?>
      <ul class="divide-y divide-outline/40">
        <?php foreach ($recentOrders as $o): ?>
          <li class="py-3 flex items-center justify-between gap-3">
            <div>
              <a href="order-details.php?id=<?= (int)$o['id'] ?>" class="font-semibold text-sm hover:underline"><?= h($o['order_number']) ?></a>
              <div class="text-xs text-on-surface-variant"><?= h(date('M j, Y', strtotime($o['created_at']))) ?> · <?= money_tk($o['total_amount']) ?></div>
            </div>
            <span class="px-2 py-1 rounded-full text-[11px] font-semibold <?= status_pill_class($o['order_status'], 'order') ?>"><?= format_status_label($o['order_status']) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>

  <div class="bg-white rounded-3xl border border-outline/40 p-5">
    <div class="flex items-center justify-between mb-3">
      <h2 class="font-bold text-lg">Custom orders</h2>
      <a href="custom-order.php" class="text-xs font-bold text-primary hover:underline">View all</a>
    </div>
    <?php if (empty($recentCustom)): ?>
      <p class="text-sm text-on-surface-variant">No custom orders yet. <a href="custom-order.php" class="text-primary font-bold">Request a custom creation</a>.</p>
    <?php else: ?>
      <ul class="divide-y divide-outline/40">
        <?php foreach ($recentCustom as $c): ?>
          <li class="py-3 flex items-center justify-between gap-3">
            <div>
              <div class="font-semibold text-sm"><?= h($c['title']) ?></div>
              <div class="text-xs text-on-surface-variant"><?= h(date('M j, Y', strtotime($c['created_at']))) ?></div>
            </div>
            <span class="px-2 py-1 rounded-full text-[11px] font-semibold <?= status_pill_class($c['status'], 'custom') ?>"><?= format_status_label($c['status']) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 md:px-6 py-6 grid lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2 bg-white rounded-3xl border border-outline/40 p-5">
    <h2 class="font-bold text-lg mb-3">Quick actions</h2>
    <div class="grid sm:grid-cols-2 gap-3">
      <a href="home.php" class="hm-card p-4 flex items-center gap-3">
        <span class="material-symbols-outlined text-primary">storefront</span>
        <span class="font-semibold text-sm">Browse shop</span>
      </a>
      <a href="products.php" class="hm-card p-4 flex items-center gap-3">
        <span class="material-symbols-outlined text-primary">inventory_2</span>
        <span class="font-semibold text-sm">All products</span>
      </a>
      <a href="vendor-booking.php" class="hm-card p-4 flex items-center gap-3">
        <span class="material-symbols-outlined text-primary">event</span>
        <span class="font-semibold text-sm">Book a vendor</span>
      </a>
      <a href="services.php" class="hm-card p-4 flex items-center gap-3">
        <span class="material-symbols-outlined text-primary">design_services</span>
        <span class="font-semibold text-sm">Explore services</span>
      </a>
    </div>
  </div>

  <div class="bg-white rounded-3xl border border-outline/40 p-5">
    <h2 class="font-bold text-lg mb-3">Notifications</h2>
    <?php if (empty($notifRows)): ?>
      <p class="text-sm text-on-surface-variant">No notifications yet.</p>
    <?php else: ?>
      <ul class="space-y-3 text-sm">
        <?php foreach ($notifRows as $n): ?>
          <li class="flex items-start gap-3">
            <span class="material-symbols-outlined text-primary text-base mt-0.5"><?= $n['is_read'] ? 'circle' : 'circle_notifications' ?></span>
            <div>
              <div class="font-semibold leading-tight"><?= h($n['title']) ?></div>
              <?php if (!empty($n['body'])): ?>
                <p class="text-xs text-on-surface-variant mt-0.5"><?= h($n['body']) ?></p>
              <?php endif; ?>
              <?php if (!empty($n['link'])): ?>
                <a class="text-xs text-primary font-semibold hover:underline" href="../<?= h($n['link']) ?>">View →</a>
              <?php endif; ?>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 md:px-6 py-6">
  <div class="bg-white rounded-3xl border border-outline/40 p-5">
    <h2 class="font-bold text-lg mb-3">Recent bookings</h2>
    <?php if (empty($recentBookings)): ?>
      <p class="text-sm text-on-surface-variant">No bookings yet. <a href="vendor-booking.php" class="text-primary font-bold">Book a vendor</a> for your next event.</p>
    <?php else: ?>
      <ul class="divide-y divide-outline/40">
        <?php foreach ($recentBookings as $b): ?>
          <li class="py-3 flex items-center justify-between gap-3">
            <div>
              <div class="font-semibold text-sm"><?= h($b['service_name'] ?: $b['event_type']) ?></div>
              <div class="text-xs text-on-surface-variant"><?= h($b['booking_date']) ?></div>
            </div>
            <span class="px-2 py-1 rounded-full text-[11px] font-semibold <?= status_pill_class($b['status'], 'booking') ?>"><?= format_status_label($b['status']) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
