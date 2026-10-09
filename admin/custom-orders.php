<?php
/**
 * HerMart — Admin: Custom Orders
 *
 * Read-mostly view of every custom order. Status updates available;
 * per-seller triage still lives in `seller/custom-orders.php`.
 */
$page_title = 'Custom Orders — Admin — HerMart';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/admin-auth.php';

admin_only();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cid = safe_int($_POST['custom_id'] ?? 0);
    $new = $_POST['status'] ?? '';
    if ($cid > 0 && in_array($new, ['pending','reviewed','quoted','accepted','in_progress','completed','rejected','cancelled'], true)) {
        db_execute("UPDATE custom_orders SET status = ? WHERE id = ?", 'si', [$new, $cid]);
        flash_set('success', 'Custom order updated.');
    }
    redirect('admin/custom-orders.php');
}

$rows = db_select(
    "SELECT co.*, u.name AS customer_name, b.shop_name AS seller_name
     FROM custom_orders co
     LEFT JOIN users      u ON u.id = co.customer_id
     LEFT JOIN businesses b ON b.id = co.seller_id
     ORDER BY co.id DESC LIMIT 100"
);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/_layout_top.php';
?>

<section class="hm-container py-6">
  <div class="bg-white rounded-3xl border border-outline/40 p-6">
    <h2 class="font-bold text-xl mb-4">Custom Orders</h2>

    <?php if (empty($rows)): ?>
      <p class="text-sm text-on-surface-variant py-8 text-center">No custom orders yet.</p>
    <?php else: ?>
      <ul class="divide-y divide-outline/40">
        <?php foreach ($rows as $r): ?>
          <li class="py-4 flex items-start gap-4">
            <div class="flex-1">
              <div class="flex items-center justify-between gap-2">
                <div class="font-semibold"><?= h($r['title']) ?></div>
                <span class="px-2 py-1 rounded-full text-[11px] font-semibold <?= status_pill_class($r['status'], 'custom') ?>"><?= format_status_label($r['status']) ?></span>
              </div>
              <p class="text-sm text-on-surface-variant mt-1 line-clamp-2"><?= h($r['description']) ?></p>
              <div class="text-xs text-on-surface-variant mt-1">
                <?= h($r['customer_name']) ?> → <?= h($r['seller_name']) ?>
                · <?= h(date('M j, Y', strtotime($r['created_at']))) ?>
                · Qty <?= (int)$r['quantity'] ?>
                · Budget <?= $r['budget'] ? money_tk($r['budget']) : '—' ?>
              </div>
            </div>
            <div class="admin-actions">
              <form method="post">
                <input type="hidden" name="custom_id" value="<?= (int)$r['id'] ?>">
                <select name="status" class="hm-input text-xs py-1">
                  <?php foreach (['pending','reviewed','quoted','accepted','in_progress','completed','rejected','cancelled'] as $s): ?>
                    <option value="<?= $s ?>" <?= $r['status'] === $s ? 'selected' : '' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option>
                  <?php endforeach; ?>
                </select>
                <button class="hm-btn hm-btn-outline py-1 px-2 text-xs mt-1">Save</button>
              </form>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</section>

<?php
include __DIR__ . '/_layout_bottom.php';
include __DIR__ . '/../includes/footer.php';
?>
