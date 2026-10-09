<?php

$page_title = 'Order Details — HerMart';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/seller-auth.php';

seller_only();

$user = current_user();
$biz  = current_business($user['id']);
$biz_id = (int)$biz['id'];

$order_id = safe_int($_GET['id'] ?? ($_POST['id'] ?? 0));
if ($order_id <= 0) {
    flash_set('error', 'Invalid order id.');
    redirect('seller/orders.php');
}


$ownsRow = db_select_one(
    "SELECT COUNT(*) AS c FROM order_items WHERE order_id = ? AND seller_id = ?",
    'ii', [$order_id, $biz_id]
);

if (!$ownsRow || (int)$ownsRow['c'] === 0) {
    flash_set('error', 'Order not found.');
    redirect('seller/orders.php');
}

$error   = '';
$success = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_order_status') {
        $next = $_POST['order_status'] ?? '';
        $allowed = ['pending','confirmed','processing','shipped','out_for_delivery','delivered','cancelled'];
        if (!in_array($next, $allowed, true)) {
            $error = 'Invalid order status.';
        } else {
            $ok = db_execute(
                "UPDATE orders SET order_status = ? WHERE id = ?",
                'si', [$next, $order_id]
            );
            if ($ok) {
                db_execute(
                    "UPDATE deliveries SET status = ? WHERE order_id = ?",
                    'si', [$next, $order_id]
                );
                if ($next === 'delivered') {
                    db_execute(
                        "UPDATE deliveries SET delivered_at = NOW() WHERE order_id = ? AND delivered_at IS NULL",
                        'i', [$order_id]
                    );
                }
                
                $customerRow = db_select_one(
                    "SELECT customer_id, order_number FROM orders WHERE id = ?",
                    'i', [$order_id]
                );
                if ($customerRow && !empty($customerRow['customer_id'])) {
                    db_execute(
                        "INSERT INTO notifications (user_id, type, title, body, link)
                         VALUES (?, 'order', ?, ?, ?)",
                        'ssss',
                        [
                            (int)$customerRow['customer_id'],
                            'Order updated: #' . $customerRow['order_number'],
                            'Your order is now "' . format_status_label($next) . '".',
                            'customer/order-details.php?id=' . $order_id
                        ]
                    );
                }
                $success = 'Order status updated.';
            } else {
                $error = 'Could not update order status.';
            }
        }
    } elseif ($action === 'update_delivery') {
        $tracking = trim($_POST['tracking_number'] ?? '');
        $carrier  = trim($_POST['carrier'] ?? '');
        $eta      = trim($_POST['estimated_delivery'] ?? '');
        $delStat  = $_POST['delivery_status'] ?? '';
        $delAllowed = ['pending','confirmed','processing','shipped','out_for_delivery','delivered','cancelled'];
        if (!in_array($delStat, $delAllowed, true)) {
            $error = 'Invalid delivery status.';
        } else {
            $etaSql = $eta !== '' ? $eta : null;
            db_execute(
                "UPDATE deliveries
                 SET tracking_number = ?, carrier = ?, estimated_delivery = ?, status = ?,
                     delivered_at = CASE WHEN ? = 'delivered' AND delivered_at IS NULL THEN NOW() ELSE delivered_at END
                 WHERE order_id = ?",
                'sssssi',
                [$tracking ?: null, $carrier ?: null, $etaSql, $delStat, $delStat, $order_id]
            );
            
            if ($delStat !== 'cancelled') {
                db_execute(
                    "UPDATE orders SET order_status = ? WHERE id = ?",
                    'si', [$delStat, $order_id]
                );
            }
            $success = 'Delivery updated.';
        }
    } elseif ($action === 'update_payment') {
        $payStat = $_POST['payment_status'] ?? '';
        $allowed = ['pending','paid','failed','refunded'];
        if (!in_array($payStat, $allowed, true)) {
            $error = 'Invalid payment status.';
        } else {
            db_execute(
                "UPDATE orders SET payment_status = ? WHERE id = ?",
                'si', [$payStat, $order_id]
            );
            db_execute(
                "UPDATE payments SET payment_status = ?, paid_at = CASE WHEN ? = 'paid' AND paid_at IS NULL THEN NOW() ELSE paid_at END
                 WHERE order_id = ?",
                'ssi', [$payStat, $payStat, $order_id]
            );
            $success = 'Payment updated.';
        }
    }
}


$ownsRow = db_select_one(
    "SELECT COUNT(*) AS c FROM order_items WHERE order_id = ? AND seller_id = ?",
    'ii', [$order_id, $biz_id]
);

if (!$ownsRow || (int)$ownsRow['c'] === 0) {
    flash_set('error', 'Order not found.');
    redirect('seller/orders.php');
}

$order = db_select_one(
    "SELECT o.*, u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone
     FROM orders o LEFT JOIN users u ON u.id = o.customer_id
     WHERE o.id = ?",
    'i', [$order_id]
);

$items = db_select(
    "SELECT oi.*, p.image AS product_image
     FROM order_items oi
     LEFT JOIN products p ON p.id = oi.product_id
     WHERE oi.order_id = ? AND oi.seller_id = ?
     ORDER BY oi.id ASC",
    'ii', [$order_id, $biz_id]
);

$delivery = db_select_one(
    "SELECT * FROM deliveries WHERE order_id = ?",
    'i', [$order_id]
);

$payment = db_select_one(
    "SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1",
    'i', [$order_id]
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

<div class="flex items-center gap-3 flex-wrap justify-between">
  <div>
    <h2 class="text-xl font-bold">Order #<?= h($order['order_number']) ?></h2>
    <p class="text-xs text-on-surface-variant">
      Placed on <?= h(date('M j, Y g:i A', strtotime($order['created_at']))) ?>
    </p>
  </div>
  <a href="orders.php" class="hm-btn hm-btn-light">
    <span class="material-symbols-outlined text-base">arrow_back</span>
    All orders
  </a>
</div>

<div class="grid lg:grid-cols-3 gap-4">
  <div class="lg:col-span-2 space-y-4">
    <!-- Items (only this seller's slice) -->
    <div class="bg-white rounded-3xl border border-outline/40 p-6">
      <h3 class="font-bold mb-3">Your items in this order</h3>
      <div class="divide-y divide-outline/40">
        <?php foreach ($items as $it): ?>
          <div class="py-3 flex items-center gap-3">
            <img <?= product_image_attrs($it['product_image']) ?> alt="" class="w-14 h-14 rounded-xl object-cover border border-outline/40">
            <div class="flex-1 min-w-0">
              <div class="font-semibold text-sm truncate"><?= h($it['product_name']) ?></div>
              <div class="text-xs text-on-surface-variant">Qty <?= (int)$it['quantity'] ?> · <?= money_tk($it['unit_price']) ?> each</div>
            </div>
            <div class="font-bold text-primary"><?= money_tk($it['subtotal']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="border-t border-outline/40 mt-3 pt-3 text-right text-sm">
        <span class="text-on-surface-variant">Your subtotal:</span>
        <span class="font-bold text-primary ml-2"><?= money_tk(array_sum(array_column($items, 'subtotal'))) ?></span>
      </div>
    </div>

    <!-- Update order status -->
    <div class="bg-white rounded-3xl border border-outline/40 p-6">
      <h3 class="font-bold mb-3">Update Order Status</h3>
      <form method="post" class="flex flex-wrap items-end gap-3">
        <input type="hidden" name="action" value="update_order_status">
        <input type="hidden" name="id" value="<?= (int)$order['id'] ?>">
        <div class="flex-1 min-w-[180px]">
          <label class="hm-label">Status</label>
          <select name="order_status" class="hm-select">
            <?php
              $opts = ['pending','confirmed','processing','shipped','out_for_delivery','delivered','cancelled'];
              foreach ($opts as $o):
            ?>
              <option value="<?= $o ?>" <?= $order['order_status'] === $o ? 'selected' : '' ?>>
                <?= format_status_label($o) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="hm-btn hm-btn-primary">
          <span class="material-symbols-outlined text-base">save</span>
          Save Status
        </button>
      </form>
      <p class="text-xs text-on-surface-variant mt-2">
        Current: <span class="hm-pill <?= status_pill_class($order['order_status'], 'order') ?>"><?= format_status_label($order['order_status']) ?></span>
      </p>
    </div>

    <!-- Delivery -->
    <div class="bg-white rounded-3xl border border-outline/40 p-6">
      <h3 class="font-bold mb-3">Delivery</h3>
      <?php if ($delivery): ?>
        <form method="post" class="grid sm:grid-cols-2 gap-3">
          <input type="hidden" name="action" value="update_delivery">
          <input type="hidden" name="id" value="<?= (int)$order['id'] ?>">
          <div>
            <label class="hm-label">Tracking Number</label>
            <input name="tracking_number" type="text" class="hm-input" value="<?= h($delivery['tracking_number'] ?? '') ?>" placeholder="HM-XXXXX">
          </div>
          <div>
            <label class="hm-label">Carrier</label>
            <input name="carrier" type="text" class="hm-input" value="<?= h($delivery['carrier'] ?? '') ?>" placeholder="HerMart Logistics">
          </div>
          <div>
            <label class="hm-label">Estimated Delivery</label>
            <input name="estimated_delivery" type="date" class="hm-input" value="<?= h($delivery['estimated_delivery'] ?? '') ?>">
          </div>
          <div>
            <label class="hm-label">Status</label>
            <select name="delivery_status" class="hm-select">
              <?php
                $opts = ['pending','confirmed','processing','shipped','out_for_delivery','delivered','cancelled'];
                foreach ($opts as $o):
              ?>
                <option value="<?= $o ?>" <?= ($delivery['status'] ?? '') === $o ? 'selected' : '' ?>>
                  <?= format_status_label($o) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="sm:col-span-2 flex justify-end">
            <button type="submit" class="hm-btn hm-btn-primary">
              <span class="material-symbols-outlined text-base">local_shipping</span>
              Update Delivery
            </button>
          </div>
        </form>
      <?php else: ?>
        <p class="text-sm text-on-surface-variant">No delivery row found for this order.</p>
      <?php endif; ?>
    </div>

    <!-- Payment -->
    <div class="bg-white rounded-3xl border border-outline/40 p-6">
      <h3 class="font-bold mb-3">Payment</h3>
      <div class="text-sm mb-3">
        <div>Method: <span class="font-bold"><?= strtoupper(h($order['payment_method'])) ?></span></div>
        <?php if ($payment): ?>
          <div class="text-on-surface-variant mt-1">
            Amount: <?= money_tk($payment['amount']) ?> ·
            Status: <span class="hm-pill <?= status_pill_class($payment['payment_status'], 'payment') ?>"><?= format_status_label($payment['payment_status']) ?></span>
          </div>
          <?php if (!empty($payment['transaction_reference'])): ?>
            <div class="text-xs text-on-surface-variant">Ref: <?= h($payment['transaction_reference']) ?></div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
      <form method="post" class="flex flex-wrap items-end gap-3">
        <input type="hidden" name="action" value="update_payment">
        <input type="hidden" name="id" value="<?= (int)$order['id'] ?>">
        <div class="flex-1 min-w-[180px]">
          <label class="hm-label">Mark as</label>
          <select name="payment_status" class="hm-select">
            <?php foreach (['pending','paid','failed','refunded'] as $s): ?>
              <option value="<?= $s ?>" <?= ($payment['payment_status'] ?? '') === $s ? 'selected' : '' ?>><?= format_status_label($s) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="hm-btn hm-btn-primary">
          <span class="material-symbols-outlined text-base">payments</span>
          Update Payment
        </button>
      </form>
    </div>
  </div>

  <aside class="space-y-4">
    <div class="bg-white rounded-3xl border border-outline/40 p-6">
      <h3 class="font-bold mb-2">Customer</h3>
      <div class="text-sm">
        <div class="font-semibold"><?= h($order['customer_name'] ?? 'Customer') ?></div>
        <?php if (!empty($order['customer_email'])): ?>
          <div class="text-on-surface-variant text-xs"><?= h($order['customer_email']) ?></div>
        <?php endif; ?>
        <?php if (!empty($order['customer_phone'])): ?>
          <div class="text-xs">📞 <?= h($order['customer_phone']) ?></div>
        <?php endif; ?>
        <?php if (!empty($order['phone'])): ?>
          <div class="text-xs">📞 <?= h($order['phone']) ?></div>
        <?php endif; ?>
      </div>
    </div>
    <div class="bg-white rounded-3xl border border-outline/40 p-6">
      <h3 class="font-bold mb-2">Shipping Address</h3>
      <div class="text-sm">
        <div><?= h($order['shipping_address'] ?? '—') ?></div>
        <div class="text-xs text-on-surface-variant mt-1">
          <?= h(trim(($order['upazila'] ?? '') . ', ' . ($order['district'] ?? ''), ', ')) ?>
        </div>
      </div>
      <?php if (!empty($order['notes'])): ?>
        <div class="mt-3 text-xs">
          <div class="font-bold">Customer notes:</div>
          <div class="text-on-surface-variant"><?= h($order['notes']) ?></div>
        </div>
      <?php endif; ?>
    </div>
    <div class="bg-white rounded-3xl border border-outline/40 p-6">
      <h3 class="font-bold mb-2">Order Summary</h3>
      <div class="text-sm space-y-1">
        <div class="flex justify-between"><span class="text-on-surface-variant">Items</span><span><?= count($items) ?></span></div>
        <div class="flex justify-between"><span class="text-on-surface-variant">Quantity</span><span><?= array_sum(array_column($items, 'quantity')) ?></span></div>
        <div class="flex justify-between font-bold text-primary">
          <span>Your subtotal</span>
          <span><?= money_tk(array_sum(array_column($items, 'subtotal'))) ?></span>
        </div>
      </div>
    </div>
  </aside>
</div>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
