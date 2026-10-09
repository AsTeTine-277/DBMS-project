<?php
$page_title = 'My Orders — HerMart';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/customer-auth.php';

customer_only();

$user = current_user();
$orders = db_select(
    "SELECT o.*, d.tracking_number, d.status AS delivery_status
     FROM orders o
     LEFT JOIN deliveries d ON d.order_id = o.id
     WHERE o.customer_id = ?
     ORDER BY o.created_at DESC",
    'i', [$user['id']]
);

include __DIR__ . '/../includes/header.php';
?>

<section class="bg-gradient-to-b from-primary-light/40 to-transparent">
  <div class="max-w-7xl mx-auto px-4 md:px-6 py-10">
    <p class="text-xs uppercase tracking-widest font-bold text-primary">My Orders</p>
    <h1 class="font-serif text-3xl md:text-4xl font-bold mt-2">Order History</h1>
    <p class="text-on-surface-variant mt-2">Track and review all your HerMart orders in one place.</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 md:px-6 pb-12">
  <?php if (empty($orders)): ?>
    <div class="bg-white rounded-3xl border border-outline/40 p-12 text-center">
      <span class="material-symbols-outlined text-primary text-5xl">receipt_long</span>
      <h2 class="font-bold text-xl mt-3">No orders yet</h2>
      <p class="text-on-surface-variant mt-1">Start exploring and place your first order.</p>
      <a href="home.php" class="hm-btn hm-btn-primary mt-4 inline-flex">Start Shopping</a>
    </div>
  <?php else: ?>
    <div class="space-y-4">
      <?php foreach ($orders as $o):
          $items = db_select(
              "SELECT oi.*, p.image FROM order_items oi
               LEFT JOIN products p ON p.id = oi.product_id
               WHERE oi.order_id = ?", 'i', [$o['id']]
          );
      ?>
        <div class="bg-white rounded-3xl border border-outline/40 p-5 md:p-6">
          <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-outline/40">
            <div>
              <h3 class="font-bold">Order #<?= h($o['order_number']) ?></h3>
              <p class="text-xs text-on-surface-variant">
                Placed on <?= h(date('M j, Y \a\t g:i A', strtotime($o['created_at']))) ?> · <?= h($o['shipping_address']) ?>
              </p>
              <?php if ($o['tracking_number']): ?>
                <p class="text-xs text-on-surface-variant mt-1">Tracking: <strong><?= h($o['tracking_number']) ?></strong></p>
              <?php endif; ?>
            </div>
            <div class="flex items-center gap-3">
              <span class="hm-pill <?= status_pill_class($o['order_status'], 'order') ?>">
                <?= format_status_label($o['order_status']) ?>
              </span>
              <span class="font-bold text-primary text-lg"><?= money_tk($o['total_amount']) ?></span>
            </div>
          </div>
          <div class="mt-4 grid sm:grid-cols-2 md:grid-cols-3 gap-3">
            <?php foreach ($items as $it): ?>
              <a href="product-details.php?id=<?= (int)$it['product_id'] ?>" class="flex items-center gap-3 p-2 border border-outline/40 rounded-xl">
                <img <?= product_image_attrs($it['image']) ?> class="w-12 h-12 rounded-lg object-cover">
                <div class="min-w-0 flex-1">
                  <div class="text-sm font-bold truncate"><?= h($it['product_name']) ?></div>
                  <div class="text-xs text-on-surface-variant">Qty <?= (int)$it['quantity'] ?> · <?= money_tk($it['unit_price']) ?></div>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
          <div class="mt-4 text-right">
            <a href="order-details.php?id=<?= (int)$o['id'] ?>" class="hm-btn hm-btn-light text-xs">
              <span class="material-symbols-outlined text-base">receipt_long</span>
              View Details
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
