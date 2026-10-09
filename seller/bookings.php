<?php

$page_title = 'My Bookings — HerMart';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/seller-auth.php';

seller_only();

$user = current_user();
$biz  = current_business($user['id']);
$biz_id = (int)$biz['id'];

$status_filter = $_GET['status'] ?? '';
$allowed = ['pending','accepted','rejected','completed','cancelled'];
if (!in_array($status_filter, $allowed, true)) {
    $status_filter = '';
}

$where  = ['bk.vendor_id = ?'];
$args   = [$biz_id];
$types  = 'i';
if ($status_filter !== '') {
    $where[] = 'bk.status = ?';
    $args[]  = $status_filter;
    $types  .= 's';
}
$whereSql = implode(' AND ', $where);

$rows = db_select(
    "SELECT bk.*, u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone,
            s.name AS service_name
     FROM bookings bk
     LEFT JOIN users u ON u.id = bk.customer_id
     LEFT JOIN services s ON s.id = bk.service_id
     WHERE $whereSql
     ORDER BY bk.created_at DESC",
    $types, $args
);

$counts = db_select_one(
    "SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'pending'   THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN status = 'accepted'  THEN 1 ELSE 0 END) AS accepted,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed
     FROM bookings WHERE vendor_id = ?",
    'i', [$biz_id]
);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/_layout_top.php';
?>

<div class="flex items-center justify-between gap-3 flex-wrap">
  <div>
    <h2 class="text-xl font-bold">My Bookings</h2>
    <p class="text-xs text-on-surface-variant">
      <?= (int)($counts['total'] ?? 0) ?> total ·
      <?= (int)($counts['pending'] ?? 0) ?> pending ·
      <?= (int)($counts['accepted'] ?? 0) ?> accepted ·
      <?= (int)($counts['completed'] ?? 0) ?> completed
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
    <span class="material-symbols-outlined text-primary text-5xl">event</span>
    <h3 class="font-bold text-xl mt-3">No bookings yet</h3>
    <p class="text-on-surface-variant mt-1">Customer booking requests will appear here.</p>
  </div>
<?php else: ?>
  <div class="space-y-3">
    <?php foreach ($rows as $b): ?>
      <div class="bg-white rounded-2xl border border-outline/40 p-5">
        <div class="flex items-start justify-between gap-3 flex-wrap">
          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <h3 class="font-bold"><?= h($b['event_type'] ?: ($b['service_name'] ?: 'Booking')) ?></h3>
              <span class="hm-pill <?= status_pill_class($b['status'], 'booking') ?>"><?= format_status_label($b['status']) ?></span>
            </div>
            <p class="text-xs text-on-surface-variant mt-1">
              From <?= h($b['customer_name'] ?? 'Customer') ?>
              · <?= h(date('M j, Y', strtotime($b['booking_date']))) ?>
              <?php if (!empty($b['booking_time'])): ?> at <?= h(substr($b['booking_time'], 0, 5)) ?><?php endif; ?>
              · <?= h($b['location'] ?? '—') ?>
            </p>
            <?php if (!empty($b['notes'])): ?>
              <p class="text-sm mt-2 whitespace-pre-line"><?= h($b['notes']) ?></p>
            <?php endif; ?>
            <div class="text-xs text-on-surface-variant mt-2">
              <?php if (!empty($b['customer_email'])): ?>✉ <?= h($b['customer_email']) ?> · <?php endif; ?>
              <?php if (!empty($b['customer_phone'])): ?>📞 <?= h($b['customer_phone']) ?><?php endif; ?>
            </div>
          </div>
          <form method="post" action="bookings-action.php" class="flex flex-wrap gap-2 justify-end">
            <input type="hidden" name="booking_id" value="<?= (int)$b['id'] ?>">
            <input type="hidden" name="redirect_to" value="seller/bookings.php">
            <?php if ($b['status'] === 'pending'): ?>
              <button type="submit" name="action" value="accept" class="hm-btn hm-btn-primary text-xs">
                <span class="material-symbols-outlined text-base">check</span> Accept
              </button>
              <button type="submit" name="action" value="reject" class="hm-btn hm-btn-outline text-xs text-red-600 border-red-200">
                <span class="material-symbols-outlined text-base">close</span> Reject
              </button>
            <?php elseif ($b['status'] === 'accepted'): ?>
              <button type="submit" name="action" value="complete" class="hm-btn hm-btn-primary text-xs">
                <span class="material-symbols-outlined text-base">task_alt</span> Mark Completed
              </button>
              <button type="submit" name="action" value="cancel" class="hm-btn hm-btn-outline text-xs">
                <span class="material-symbols-outlined text-base">block</span> Cancel
              </button>
            <?php elseif ($b['status'] === 'rejected' || $b['status'] === 'cancelled'): ?>
              <button type="submit" name="action" value="reopen" class="hm-btn hm-btn-light text-xs">
                <span class="material-symbols-outlined text-base">refresh</span> Re-open
              </button>
            <?php endif; ?>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
