<?php
/**
 * HerMart — Admin: Reports
 *
 * Date-range-aware platform aggregations:
 *   - Revenue (sum of payments paid in the window)
 *   - Orders by status
 *   - New users by role
 *   - Top 5 sellers by GMV (order_items subtotal)
 *   - Custom-order funnel (count by status)
 *   - Booking funnel (count by status)
 *
 * Default window = last 30 days.
 */
$page_title = 'Reports — Admin — HerMart';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/admin-auth.php';

admin_only();

// Range
$from = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
$to   = $_GET['to']   ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) $from = date('Y-m-d', strtotime('-30 days'));
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))   $to   = date('Y-m-d');

// Window is [from 00:00:00, to+1 day 00:00:00)
$fromDt = $from . ' 00:00:00';
$toDt   = date('Y-m-d', strtotime($to . ' +1 day')) . ' 00:00:00';

// Helper to run a date-windowed aggregate.
function agg($sql, $types, $args) {
    return db_select($sql, $types, $args);
}

$revenue = (float)(agg(
    "SELECT COALESCE(SUM(p.amount),0) AS rev, COUNT(*) AS n
     FROM payments p
     WHERE p.payment_status = 'paid' AND p.paid_at >= ? AND p.paid_at < ?",
    'ss', [$fromDt, $toDt]
)[0]['rev'] ?? 0);

$ordersByStatus = agg(
    "SELECT order_status AS status, COUNT(*) AS c
     FROM orders WHERE created_at >= ? AND created_at < ?
     GROUP BY order_status ORDER BY c DESC",
    'ss', [$fromDt, $toDt]
);

$newUsers = agg(
    "SELECT role, COUNT(*) AS c FROM users
     WHERE created_at >= ? AND created_at < ?
     GROUP BY role ORDER BY c DESC",
    'ss', [$fromDt, $toDt]
);

$topSellers = agg(
    "SELECT b.shop_name AS seller, SUM(oi.subtotal) AS gmv, COUNT(DISTINCT oi.order_id) AS order_count, SUM(oi.quantity) AS units
     FROM order_items oi
     JOIN products   p ON p.id = oi.product_id
     JOIN businesses b ON b.id = p.seller_id
     JOIN orders     o ON o.id = oi.order_id
     WHERE o.created_at >= ? AND o.created_at < ?
       AND o.order_status NOT IN ('cancelled')
     GROUP BY b.id, b.shop_name
     ORDER BY gmv DESC LIMIT 5",
    'ss', [$fromDt, $toDt]
);

$customFunnel = agg(
    "SELECT status, COUNT(*) AS c FROM custom_orders
     WHERE created_at >= ? AND created_at < ?
     GROUP BY status ORDER BY c DESC",
    'ss', [$fromDt, $toDt]
);

$bookingsFunnel = agg(
    "SELECT status, COUNT(*) AS c FROM bookings
     WHERE created_at >= ? AND created_at < ?
     GROUP BY status ORDER BY c DESC",
    'ss', [$fromDt, $toDt]
);

$orderCount = (int)(agg(
    "SELECT COUNT(*) AS c FROM orders WHERE created_at >= ? AND created_at < ?",
    'ss', [$fromDt, $toDt]
)[0]['c'] ?? 0);

$avgOrder = $orderCount > 0 ? ($revenue / max(1, $orderCount)) : 0;

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/_layout_top.php';
?>

<section class="hm-container py-6">
  <div class="bg-white rounded-3xl border border-outline/40 p-6">
    <div class="flex flex-wrap items-end justify-between gap-3 mb-4">
      <h2 class="font-bold text-xl">Reports</h2>
      <form method="get" class="flex flex-wrap items-end gap-2 text-xs">
        <div>
          <label class="hm-label">From</label>
          <input type="date" name="from" value="<?= h($from) ?>" class="hm-input">
        </div>
        <div>
          <label class="hm-label">To</label>
          <input type="date" name="to" value="<?= h($to) ?>" class="hm-input">
        </div>
        <button class="hm-btn hm-btn-primary">Apply</button>
        <a href="reports.php" class="hm-btn hm-btn-outline">Reset</a>
      </form>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
      <div class="hm-card p-4">
        <div class="text-xs uppercase tracking-wider text-on-surface-variant">Revenue (paid)</div>
        <div class="text-2xl font-bold text-primary mt-1"><?= money_tk($revenue) ?></div>
      </div>
      <div class="hm-card p-4">
        <div class="text-xs uppercase tracking-wider text-on-surface-variant">Orders</div>
        <div class="text-2xl font-bold mt-1"><?= $orderCount ?></div>
      </div>
      <div class="hm-card p-4">
        <div class="text-xs uppercase tracking-wider text-on-surface-variant">Avg order</div>
        <div class="text-2xl font-bold mt-1"><?= money_tk($avgOrder) ?></div>
      </div>
      <div class="hm-card p-4">
        <div class="text-xs uppercase tracking-wider text-on-surface-variant">Top seller GMV</div>
        <div class="text-2xl font-bold mt-1"><?= !empty($topSellers) ? money_tk($topSellers[0]['gmv']) : '৳0' ?></div>
        <div class="text-[11px] text-on-surface-variant"><?= h($topSellers[0]['seller'] ?? '—') ?></div>
      </div>
    </div>
  </div>

  <div class="grid lg:grid-cols-2 gap-4 mt-4">
    <div class="bg-white rounded-3xl border border-outline/40 p-6">
      <h3 class="font-bold text-lg mb-3">Orders by status</h3>
      <?php if (empty($ordersByStatus)): ?>
        <p class="text-sm text-on-surface-variant">No orders in this window.</p>
      <?php else: ?>
        <ul class="space-y-2 text-sm">
          <?php foreach ($ordersByStatus as $r): ?>
            <li class="flex items-center justify-between">
              <span class="px-2 py-1 rounded-full text-[11px] font-semibold <?= status_pill_class($r['status'], 'order') ?>"><?= format_status_label($r['status']) ?></span>
              <span class="font-semibold"><?= (int)$r['c'] ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <div class="bg-white rounded-3xl border border-outline/40 p-6">
      <h3 class="font-bold text-lg mb-3">New users by role</h3>
      <?php if (empty($newUsers)): ?>
        <p class="text-sm text-on-surface-variant">No new users in this window.</p>
      <?php else: ?>
        <ul class="space-y-2 text-sm">
          <?php foreach ($newUsers as $r): ?>
            <li class="flex items-center justify-between">
              <span class="px-2 py-1 rounded-full text-[11px] font-semibold bg-primary-light text-primary"><?= ucfirst($r['role']) ?></span>
              <span class="font-semibold"><?= (int)$r['c'] ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>

  <div class="bg-white rounded-3xl border border-outline/40 p-6 mt-4">
    <h3 class="font-bold text-lg mb-3">Top 5 sellers by GMV</h3>
    <?php if (empty($topSellers)): ?>
      <p class="text-sm text-on-surface-variant">No completed orders in this window.</p>
    <?php else: ?>
      <table class="w-full text-sm">
        <thead class="text-left text-xs uppercase text-on-surface-variant border-b border-outline/40">
          <tr>
            <th class="py-2">#</th>
            <th>Seller</th>
            <th>Orders</th>
            <th>Units</th>
            <th class="text-right">GMV</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($topSellers as $i => $s): ?>
            <tr class="border-b border-outline/20">
              <td class="py-2"><?= $i + 1 ?></td>
              <td><?= h($s['seller']) ?></td>
              <td><?= (int)$s['order_count'] ?></td>
              <td><?= (int)$s['units'] ?></td>
              <td class="text-right font-bold text-primary"><?= money_tk($s['gmv']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <div class="grid lg:grid-cols-2 gap-4 mt-4">
    <div class="bg-white rounded-3xl border border-outline/40 p-6">
      <h3 class="font-bold text-lg mb-3">Custom-order funnel</h3>
      <?php if (empty($customFunnel)): ?>
        <p class="text-sm text-on-surface-variant">No custom orders in this window.</p>
      <?php else: ?>
        <ul class="space-y-2 text-sm">
          <?php foreach ($customFunnel as $r): ?>
            <li class="flex items-center justify-between">
              <span class="px-2 py-1 rounded-full text-[11px] font-semibold <?= status_pill_class($r['status'], 'custom') ?>"><?= format_status_label($r['status']) ?></span>
              <span class="font-semibold"><?= (int)$r['c'] ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <div class="bg-white rounded-3xl border border-outline/40 p-6">
      <h3 class="font-bold text-lg mb-3">Booking funnel</h3>
      <?php if (empty($bookingsFunnel)): ?>
        <p class="text-sm text-on-surface-variant">No bookings in this window.</p>
      <?php else: ?>
        <ul class="space-y-2 text-sm">
          <?php foreach ($bookingsFunnel as $r): ?>
            <li class="flex items-center justify-between">
              <span class="px-2 py-1 rounded-full text-[11px] font-semibold <?= status_pill_class($r['status'], 'booking') ?>"><?= format_status_label($r['status']) ?></span>
              <span class="font-semibold"><?= (int)$r['c'] ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php
include __DIR__ . '/_layout_bottom.php';
include __DIR__ . '/../includes/footer.php';
?>
