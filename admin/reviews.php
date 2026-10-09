<?php
/**
 * HerMart — Admin: Reviews
 *
 * Platform-wide review moderation. Admins can toggle `status` between
 * 'active' and 'hidden'.
 */
$page_title = 'Reviews — Admin — HerMart';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/admin-auth.php';

admin_only();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rid  = safe_int($_POST['review_id'] ?? 0);
    $new  = $_POST['status'] ?? '';
    if ($rid > 0 && in_array($new, ['active','hidden'], true)) {
        db_execute("UPDATE reviews SET status = ? WHERE id = ?", 'si', [$new, $rid]);
        flash_set('success', 'Review updated.');
    }
    redirect('admin/reviews.php');
}

$rows = db_select(
    "SELECT r.*, u.name AS customer_name, b.shop_name AS seller_name, p.name AS product_name
     FROM reviews r
     LEFT JOIN users      u ON u.id = r.customer_id
     LEFT JOIN businesses b ON b.id = r.seller_id
     LEFT JOIN products   p ON p.id = r.product_id
     ORDER BY r.id DESC LIMIT 100"
);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/_layout_top.php';
?>

<section class="hm-container py-6">
  <div class="bg-white rounded-3xl border border-outline/40 p-6">
    <h2 class="font-bold text-xl mb-4">Reviews</h2>

    <?php if (empty($rows)): ?>
      <p class="text-sm text-on-surface-variant py-8 text-center">No reviews yet.</p>
    <?php else: ?>
      <ul class="divide-y divide-outline/40">
        <?php foreach ($rows as $r): ?>
          <li class="py-4 flex items-start gap-3">
            <div class="w-10 h-10 rounded-full bg-primary-light text-primary flex items-center justify-center font-bold">
              <?= h(strtoupper(substr((string)$r['customer_name'], 0, 1))) ?>
            </div>
            <div class="flex-1">
              <div class="flex items-center justify-between gap-2">
                <div class="font-semibold text-sm"><?= h($r['customer_name']) ?>
                  <span class="text-xs text-on-surface-variant ml-2">on
                    <?= h($r['product_name'] ?: $r['seller_name'] ?: 'item') ?>
                  </span>
                </div>
                <div class="text-amber-500 text-sm">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="material-symbols-outlined text-base align-middle" style="font-variation-settings: 'FILL' <?= $i <= (int)$r['rating'] ? 1 : 0 ?>;">star</span>
                  <?php endfor; ?>
                </div>
              </div>
              <?php if (!empty($r['review_text'])): ?>
                <p class="text-sm text-on-surface mt-1"><?= h($r['review_text']) ?></p>
              <?php endif; ?>
              <div class="text-[11px] text-on-surface-variant mt-1"><?= h(date('M j, Y', strtotime($r['created_at']))) ?></div>
              <div class="admin-actions mt-2">
                <form method="post">
                  <input type="hidden" name="review_id" value="<?= (int)$r['id'] ?>">
                  <input type="hidden" name="status" value="<?= $r['status'] === 'active' ? 'hidden' : 'active' ?>">
                  <button class="hm-btn hm-btn-outline text-xs <?= $r['status'] === 'active' ? 'text-red-600' : 'text-emerald-700' ?>">
                    <?= $r['status'] === 'active' ? 'Hide review' : 'Approve review' ?>
                  </button>
                </form>
                <div class="text-[11px] text-on-surface-variant uppercase tracking-wider"><?= h($r['status']) ?></div>
              </div>
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
