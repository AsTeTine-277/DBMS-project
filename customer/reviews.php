<?php
$page_title = 'Reviews — HerMart';
require_once __DIR__ . '/../includes/init.php';

$reviews = db_select(
    "SELECT r.*, u.name AS customer_name, b.shop_name AS brand, p.name AS product_name
     FROM reviews r
     LEFT JOIN users u      ON u.id = r.customer_id
     LEFT JOIN businesses b ON b.id = r.seller_id
     LEFT JOIN products p   ON p.id = r.product_id
     WHERE r.status = 'active'
     ORDER BY r.created_at DESC LIMIT 20"
);

include __DIR__ . '/../includes/header.php';
?>

<section class="bg-gradient-to-b from-primary-light/40 to-transparent">
  <div class="max-w-7xl mx-auto px-4 md:px-6 py-10">
    <p class="text-xs uppercase tracking-widest font-bold text-primary">Reviews</p>
    <h1 class="font-serif text-3xl md:text-4xl font-bold mt-2">Customer Reviews</h1>
    <p class="text-on-surface-variant mt-2 max-w-2xl">What real customers say about HerMart's women artisans.</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 md:px-6 pb-12">
  <?php if (empty($reviews)): ?>
    <div class="bg-white rounded-3xl border border-outline/40 p-10 text-center">
      <p class="text-on-surface-variant">No reviews yet.</p>
    </div>
  <?php else: ?>
    <div class="grid md:grid-cols-2 gap-4">
      <?php foreach ($reviews as $r): ?>
        <div class="bg-white rounded-3xl border border-outline/40 p-5 md:p-6">
          <div class="flex items-start gap-3 mb-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-primary to-pink-300 text-white flex items-center justify-center font-bold">
              <?= strtoupper(substr($r['customer_name'] ?? 'C', 0, 1)) ?>
            </div>
            <div class="flex-1">
              <div class="font-bold"><?= h($r['customer_name'] ?? 'Customer') ?></div>
              <div class="text-xs text-on-surface-variant"><?= h(date('M j, Y', strtotime($r['created_at']))) ?></div>
            </div>
            <span class="text-amber-400 text-xs">
              <?php for ($i = 0; $i < (int)$r['rating']; $i++): ?><span class="material-symbols-outlined text-base">star</span><?php endfor; ?>
            </span>
          </div>
          <p class="text-sm"><?= h($r['review_text'] ?? '') ?></p>
          <?php if ($r['product_name']): ?>
            <p class="text-xs text-on-surface-variant mt-3">For <strong class="text-on-surface"><?= h($r['product_name']) ?></strong> · by <a class="text-primary hover:underline" href="seller-details.php?id=<?= (int)$r['seller_id'] ?>"><?= h($r['brand']) ?></a></p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>