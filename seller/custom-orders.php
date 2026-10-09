<?php

$page_title = 'Custom Requests — HerMart';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/seller-auth.php';

seller_only();

$user = current_user();
$biz  = current_business($user['id']);
$biz_id = (int)$biz['id'];

$status_filter = $_GET['status'] ?? '';
$allowed = ['pending','reviewed','quoted','accepted','in_progress','completed','rejected','cancelled'];
if (!in_array($status_filter, $allowed, true)) {
    $status_filter = '';
}

$where  = ['co.seller_id = ?'];
$args   = [$biz_id];
$types  = 'i';
if ($status_filter !== '') {
    $where[] = 'co.status = ?';
    $args[]  = $status_filter;
    $types  .= 's';
}
$whereSql = implode(' AND ', $where);

$rows = db_select(
    "SELECT co.*, u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone
     FROM custom_orders co
     LEFT JOIN users u ON u.id = co.customer_id
     WHERE $whereSql
     ORDER BY co.created_at DESC",
    $types, $args
);

$counts = db_select_one(
    "SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status IN ('pending','reviewed') THEN 1 ELSE 0 END) AS awaiting,
        SUM(CASE WHEN status = 'quoted'    THEN 1 ELSE 0 END) AS quoted,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed
     FROM custom_orders WHERE seller_id = ?",
    'i', [$biz_id]
);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/_layout_top.php';
?>

<div class="flex items-center justify-between gap-3 flex-wrap">
  <div>
    <h2 class="text-xl font-bold">Custom Requests</h2>
    <p class="text-xs text-on-surface-variant">
      <?= (int)($counts['total'] ?? 0) ?> total ·
      <?= (int)($counts['awaiting'] ?? 0) ?> awaiting response ·
      <?= (int)($counts['quoted'] ?? 0) ?> quoted ·
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
    <span class="material-symbols-outlined text-primary text-5xl">auto_awesome</span>
    <h3 class="font-bold text-xl mt-3">No custom requests yet</h3>
    <p class="text-on-surface-variant mt-1">When customers request a custom order, it'll appear here.</p>
  </div>
<?php else: ?>
  <div class="space-y-4">
    <?php foreach ($rows as $c): ?>
      <div class="bg-white rounded-2xl border border-outline/40 p-5">
        <div class="flex items-start justify-between gap-3 flex-wrap">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <h3 class="font-bold"><?= h($c['title']) ?></h3>
              <span class="hm-pill <?= status_pill_class($c['status'], 'custom') ?>"><?= format_status_label($c['status']) ?></span>
            </div>
            <p class="text-xs text-on-surface-variant mt-1">
              From <?= h($c['customer_name'] ?? 'Customer') ?>
              · <?= h(date('M j, Y', strtotime($c['created_at']))) ?>
              <?php if (!empty($c['preferred_date'])): ?>
                · Needed by <?= h(date('M j, Y', strtotime($c['preferred_date']))) ?>
              <?php endif; ?>
            </p>

            <p class="text-sm mt-2 whitespace-pre-line"><?= h($c['description']) ?></p>

            <div class="grid sm:grid-cols-3 gap-3 mt-3 text-xs">
              <div><span class="text-on-surface-variant">Budget:</span> <span class="font-semibold"><?= $c['budget'] !== null ? money_tk($c['budget']) : '—' ?></span></div>
              <div><span class="text-on-surface-variant">Quantity:</span> <span class="font-semibold"><?= (int)$c['quantity'] ?></span></div>
              <div><span class="text-on-surface-variant">Location:</span> <span class="font-semibold"><?= h($c['location'] ?? '—') ?></span></div>
            </div>

            <?php if (!empty($c['quoted_price'])): ?>
              <div class="mt-3 text-xs">
                <span class="text-on-surface-variant">Your quote:</span>
                <span class="font-bold text-primary"><?= money_tk($c['quoted_price']) ?></span>
                <?php if (!empty($c['estimated_completion'])): ?>
                  · ETA <?= h(date('M j, Y', strtotime($c['estimated_completion']))) ?>
                <?php endif; ?>
              </div>
            <?php endif; ?>

            <?php if (!empty($c['reference_image'])): ?>
              <a href="<?= h(site_image_url($c['reference_image'])) ?>" target="_blank" class="inline-block mt-2">
                <img <?= site_image_attrs($c['reference_image']) ?> alt="" class="w-24 h-24 rounded-xl object-cover border border-outline/40">
              </a>
            <?php endif; ?>
          </div>
        </div>

        <!-- Quick actions -->
        <div class="mt-4 pt-4 border-t border-outline/40 flex flex-wrap gap-2">
          <?php if ($c['status'] === 'pending'): ?>
            <form method="post" action="custom-order-action.php" class="inline">
              <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
              <input type="hidden" name="action" value="review">
              <button type="submit" class="hm-btn hm-btn-light text-xs">
                <span class="material-symbols-outlined text-base">visibility</span> Mark Reviewed
              </button>
            </form>
            <form method="post" action="custom-order-action.php" class="inline">
              <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
              <input type="hidden" name="action" value="reject">
              <button type="submit" class="hm-btn hm-btn-outline text-xs text-red-600 border-red-200">
                <span class="material-symbols-outlined text-base">close</span> Reject
              </button>
            </form>
          <?php endif; ?>

          <?php if (in_array($c['status'], ['pending','reviewed'], true)): ?>
            <button type="button" class="hm-btn hm-btn-primary text-xs" onclick="document.getElementById('quote-form-<?= (int)$c['id'] ?>').classList.toggle('hidden')">
              <span class="material-symbols-outlined text-base">request_quote</span> Send Quote
            </button>
          <?php endif; ?>

          <?php if ($c['status'] === 'quoted'): ?>
            <form method="post" action="custom-order-action.php" class="inline">
              <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
              <input type="hidden" name="action" value="accept">
              <button type="submit" class="hm-btn hm-btn-primary text-xs">
                <span class="material-symbols-outlined text-base">check</span> Accept
              </button>
            </form>
            <form method="post" action="custom-order-action.php" class="inline">
              <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
              <input type="hidden" name="action" value="reject">
              <button type="submit" class="hm-btn hm-btn-outline text-xs text-red-600 border-red-200">
                <span class="material-symbols-outlined text-base">close</span> Reject
              </button>
            </form>
          <?php endif; ?>

          <?php if ($c['status'] === 'accepted'): ?>
            <form method="post" action="custom-order-action.php" class="inline">
              <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
              <input type="hidden" name="action" value="start">
              <button type="submit" class="hm-btn hm-btn-primary text-xs">
                <span class="material-symbols-outlined text-base">play_arrow</span> Start Work
              </button>
            </form>
          <?php endif; ?>

          <?php if ($c['status'] === 'in_progress'): ?>
            <form method="post" action="custom-order-action.php" class="inline">
              <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
              <input type="hidden" name="action" value="complete">
              <button type="submit" class="hm-btn hm-btn-primary text-xs">
                <span class="material-symbols-outlined text-base">task_alt</span> Mark Completed
              </button>
            </form>
          <?php endif; ?>

          <?php if (in_array($c['status'], ['rejected','cancelled','completed'], true)): ?>
            <form method="post" action="custom-order-action.php" class="inline">
              <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
              <input type="hidden" name="action" value="reopen">
              <button type="submit" class="hm-btn hm-btn-light text-xs">
                <span class="material-symbols-outlined text-base">refresh</span> Re-open
              </button>
            </form>
          <?php endif; ?>
        </div>

        <?php if (in_array($c['status'], ['pending','reviewed'], true)): ?>
          <form id="quote-form-<?= (int)$c['id'] ?>" method="post" action="custom-order-action.php" class="mt-3 hidden grid sm:grid-cols-3 gap-3 p-3 bg-surface-container/30 rounded-xl">
            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
            <input type="hidden" name="action" value="quote">
            <div>
              <label class="hm-label">Quote Price (৳) *</label>
              <input type="number" min="0" step="1" name="quoted_price" required class="hm-input">
            </div>
            <div>
              <label class="hm-label">Estimated Completion</label>
              <input type="date" name="estimated_completion" class="hm-input">
            </div>
            <div class="flex items-end">
              <button type="submit" class="hm-btn hm-btn-primary w-full">
                <span class="material-symbols-outlined text-base">send</span>
                Send Quote
              </button>
            </div>
          </form>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
