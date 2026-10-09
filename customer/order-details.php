<?php
$page_title = 'Order Details — HerMart';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/customer-auth.php';
require_once __DIR__ . '/../includes/seller-auth.php';
require_once __DIR__ . '/../includes/admin-auth.php';

// Allow customers, sellers (if they have items in the order), and admins.
require_login();
if (!in_array($_SESSION['user']['role'], ['customer', 'seller', 'admin'], true)) {
    flash_set('error', 'Please sign in to view this order.');
    redirect('login.php');
}

$user = current_user();
$order_id = safe_int($_GET['id'] ?? 0);

$order = $order_id > 0 ? db_select_one(
    "SELECT * FROM orders WHERE id = ?",
    'i', [$order_id]
) : null;

// Authorization: customer must own the order; seller may view if it contains their products; admin can see all.
if (!$order) {
    $page_title = 'Order not found';
    include __DIR__ . '/../includes/header.php';
    ?>
    <section class="max-w-7xl mx-auto px-4 py-20 text-center">
      <span class="material-symbols-outlined text-primary text-6xl">receipt_long</span>
      <h1 class="font-serif text-3xl font-bold mt-3">Order not found</h1>
      <a href="orders.php" class="hm-btn hm-btn-primary mt-4 inline-flex">My orders</a>
    </section>
    <?php
    include __DIR__ . '/../includes/footer.php';
    exit;
}

$is_owner  = ((int)$order['customer_id'] === (int)$user['id']);
$is_seller = false;
if (!$is_owner && $user['role'] === 'seller') {
    $biz = current_business($user['id']);
    if ($biz) {
        $hit = db_select_one(
            "SELECT id FROM order_items WHERE order_id = ? AND seller_id = ? LIMIT 1",
            'ii', [$order_id, $biz['id']]
        );
        $is_seller = (bool)$hit;
    }
}
$is_admin = $user['role'] === 'admin';

if (!$is_owner && !$is_seller && !$is_admin) {
    flash_set('error', 'You cannot view this order.');
    redirect('customer/orders.php');
}

$items = db_select(
    "SELECT oi.*, p.image FROM order_items oi
     LEFT JOIN products p ON p.id = oi.product_id
     WHERE oi.order_id = ? ORDER BY oi.id ASC",
    'i', [$order_id]
);
$payment = db_select_one("SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1", 'i', [$order_id]);
$delivery = db_select_one("SELECT * FROM deliveries WHERE order_id = ? ORDER BY id DESC LIMIT 1", 'i', [$order_id]);
$customer = $order['customer_id'] ? user_by_id((int)$order['customer_id']) : null;

$steps = ['pending','confirmed','processing','shipped','out_for_delivery','delivered'];
$currentIdx = array_search($order['order_status'], $steps);
if ($currentIdx === false) $currentIdx = -1;

include __DIR__ . '/../includes/header.php';
?>

<section class="bg-gradient-to-b from-primary-light/40 to-transparent">
  <div class="max-w-7xl mx-auto px-4 md:px-6 py-10">
    <p class="text-xs uppercase tracking-widest font-bold text-primary">Order</p>
    <h1 class="font-serif text-3xl md:text-4xl font-bold mt-2">Order #<?= h($order['order_number']) ?></h1>
    <p class="text-on-surface-variant mt-2">
      Placed on <?= h(date('M j, Y \a\t g:i A', strtotime($order['created_at']))) ?>
    </p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 md:px-6 pb-12 space-y-6">

  <!-- Tracker -->
  <?php if ($order['order_status'] !== 'cancelled'): ?>
  <div class="bg-white rounded-3xl border border-outline/40 p-6">
    <h2 class="font-bold mb-3">Delivery Tracker</h2>
    <div class="hm-tracker">
      <?php foreach ($steps as $i => $step): ?>
        <div class="hm-tracker-step <?= $i <= $currentIdx ? 'is-done' : '' ?>">
          <?= h(ucwords(str_replace('_',' ', $step))) ?>
        </div>
      <?php endforeach; ?>
    </div>
    <?php if ($delivery): ?>
      <div class="mt-4 grid sm:grid-cols-3 gap-3 text-sm">
        <div class="bg-surface-dim rounded-xl p-3">
          <div class="text-xs text-on-surface-variant">Tracking #</div>
          <div class="font-bold"><?= h($delivery['tracking_number']) ?></div>
        </div>
        <div class="bg-surface-dim rounded-xl p-3">
          <div class="text-xs text-on-surface-variant">Carrier</div>
          <div class="font-bold"><?= h($delivery['carrier']) ?></div>
        </div>
        <div class="bg-surface-dim rounded-xl p-3">
          <div class="text-xs text-on-surface-variant">Estimated Delivery</div>
          <div class="font-bold"><?= h($delivery['estimated_delivery'] ?? '—') ?></div>
        </div>
      </div>
    <?php endif; ?>
  </div>
  <?php else: ?>
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-2xl px-4 py-3 text-sm">
      This order was cancelled.
    </div>
  <?php endif; ?>

  <div class="grid lg:grid-cols-3 gap-6">
    <!-- Items -->
    <div class="lg:col-span-2 bg-white rounded-3xl border border-outline/40 p-6">
      <div class="flex items-center justify-between mb-3">
        <h2 class="font-bold text-lg">Items (<?= count($items) ?>)</h2>
        <span class="hm-pill <?= status_pill_class($order['order_status'], 'order') ?>">
          <?= format_status_label($order['order_status']) ?>
        </span>
      </div>
      <div class="divide-y divide-outline/40">
        <?php foreach ($items as $it): ?>
          <div class="py-3 flex items-center gap-3">
            <img <?= product_image_attrs($it['image']) ?> class="w-14 h-14 rounded-lg object-cover border border-outline/40" alt="">
            <div class="flex-1 min-w-0">
              <div class="font-semibold truncate"><?= h($it['product_name']) ?></div>
              <div class="text-xs text-on-surface-variant">Qty <?= (int)$it['quantity'] ?> · <?= money_tk($it['unit_price']) ?></div>
            </div>
            <div class="font-bold text-primary"><?= money_tk($it['subtotal']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="mt-4 border-t border-outline/40 pt-4 text-base space-y-1 text-right">
        <div class="text-on-surface-variant">Items total: <?= money_tk(array_sum(array_column($items, 'subtotal'))) ?></div>
        <div class="text-on-surface-variant">Delivery: <?= money_tk(80) ?></div>
        <div class="text-xl font-bold text-primary">Total: <?= money_tk($order['total_amount']) ?></div>
      </div>
    </div>

    <!-- Side -->
    <div class="space-y-4">
      <div class="bg-white rounded-3xl border border-outline/40 p-5">
        <h3 class="font-bold mb-2">Delivery Address</h3>
        <p class="text-sm text-on-surface"><?= h($order['shipping_address']) ?></p>
        <p class="text-xs text-on-surface-variant mt-2">Phone: <?= h($order['phone']) ?></p>
      </div>

      <div class="bg-white rounded-3xl border border-outline/40 p-5">
        <h3 class="font-bold mb-2">Payment</h3>
        <p class="text-sm">Method: <strong><?= h(strtoupper($order['payment_method'])) ?></strong></p>
        <p class="text-sm">Amount: <strong class="text-primary"><?= money_tk($order['total_amount']) ?></strong></p>
        <p class="text-xs mt-2">Status:
          <span class="hm-pill <?= status_pill_class($order['payment_status'], 'payment') ?>"><?= format_status_label($order['payment_status']) ?></span>
        </p>
        <?php if ($payment): ?>
          <p class="text-xs text-on-surface-variant mt-2">Reference: <?= h($payment['transaction_reference']) ?></p>
        <?php endif; ?>
      </div>

      <?php if ($customer): ?>
        <div class="bg-white rounded-3xl border border-outline/40 p-5">
          <h3 class="font-bold mb-2">Customer</h3>
          <p class="text-sm"><?= h($customer['name']) ?></p>
          <p class="text-xs text-on-surface-variant"><?= h($customer['email']) ?></p>
          <p class="text-xs text-on-surface-variant"><?= h($customer['phone']) ?></p>
        </div>
      <?php endif; ?>

      <a href="orders.php" class="hm-btn hm-btn-outline w-full justify-center">
        <span class="material-symbols-outlined text-base">arrow_back</span>
        Back to orders
      </a>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
