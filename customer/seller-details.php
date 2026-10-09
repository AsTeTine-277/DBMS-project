<?php
$page_title = 'Seller — HerMart';
require_once __DIR__ . '/../includes/init.php';

$id = safe_int($_GET['id'] ?? 0);
$biz = $id > 0 ? business_by_id($id) : null;

$products = $biz ? db_select(
    "SELECT p.*, c.name AS category_name, b.shop_name AS brand, b.district FROM products p
     LEFT JOIN categories  c ON c.id = p.category_id
     LEFT JOIN businesses  b ON b.id = p.seller_id
     WHERE p.seller_id = ? AND p.status IN ('active','out_of_stock') ORDER BY p.created_at DESC",
    'i', [$biz['id']]
) : [];

$reviews = $biz ? db_select(
    "SELECT r.*, u.name AS customer_name FROM reviews r
     LEFT JOIN users u ON u.id = r.customer_id
     WHERE r.seller_id = ? AND r.status = 'active' ORDER BY r.created_at DESC LIMIT 10",
    'i', [$biz['id']]
) : [];

if (!$biz) {
    $page_title = 'Seller not found';
}

include __DIR__ . '/../includes/header.php';
?>

<?php if (!$biz): ?>
  <section class="max-w-7xl mx-auto px-4 py-20 text-center">
    <span class="material-symbols-outlined text-primary text-6xl">storefront</span>
    <h1 class="font-serif text-3xl font-bold mt-3">Seller not found</h1>
    <a href="sellers.php" class="hm-btn hm-btn-primary mt-4 inline-flex">All sellers</a>
  </section>
<?php else: ?>

<section class="bg-gradient-to-br from-[#8e1242] via-[#b01e56] to-[#6d133b] text-white">
  <div class="max-w-7xl mx-auto px-4 md:px-6 py-12 flex flex-wrap items-center gap-6">
    <button type="button" onclick="if(history.length>1){history.back();}else{location.href='sellers.php';}" class="hm-btn-back-icon" aria-label="Go back" style="background:rgba(255,255,255,0.12);border-color:rgba(255,255,255,0.3);color:#fff;">
      <span class="material-symbols-outlined">arrow_back</span>
    </button>
    <div class="w-24 h-24 rounded-3xl bg-white/15 border-2 border-white/30 flex items-center justify-center text-4xl font-bold">
      <?= strtoupper(substr($biz['shop_name'], 0, 1)) ?>
    </div>
    <div class="flex-1 min-w-0">
      <p class="text-xs uppercase tracking-widest font-bold text-pink-200"><?= h($biz['category_name'] ?? 'Verified Business') ?></p>
      <h1 class="font-serif text-3xl md:text-4xl font-bold"><?= h($biz['shop_name']) ?>
        <?php if ($biz['verified']): ?>
          <span class="inline-flex items-center gap-1 ml-2 align-middle text-emerald-300 text-base"><span class="material-symbols-outlined">verified</span> Verified</span>
        <?php endif; ?>
      </h1>
      <p class="text-pink-100/80 mt-1">
        <?= h($biz['upazila']) ? h($biz['upazila']) . ', ' : '' ?><?= h($biz['district']) ?>, <?= h($biz['division']) ?>
      </p>
    </div>
    <a href="custom-order.php" class="hm-btn bg-white text-primary hover:bg-pink-100">
      <span class="material-symbols-outlined text-base">auto_awesome</span>
      Request Custom
    </a>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 md:px-6 py-10 grid lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2">
    <div class="bg-white rounded-3xl border border-outline/40 p-6 md:p-8">
      <h2 class="font-bold text-xl mb-3">About this business</h2>
      <p class="text-on-surface leading-relaxed"><?= nl2br(h($biz['description'])) ?: 'This seller has not added a description yet.' ?></p>
    </div>

    <div class="mt-8">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-serif text-2xl font-bold">Products (<?= count($products) ?>)</h2>
      </div>
      <?php if (empty($products)): ?>
        <div class="bg-white rounded-3xl border border-outline/40 p-8 text-center text-on-surface-variant">No products listed yet.</div>
      <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5">
          <?php
            // The seller-details query doesn't always alias the seller's
            // shop_name into the row, so pre-fill brand/district from $biz
            // when the product row is missing them.
            foreach ($products as &$p) {
                if (empty($p['brand']))     $p['brand']     = $biz['shop_name'];
                if (empty($p['district']))  $p['district']  = $biz['district'];
            }
            unset($p);
          ?>
          <?php foreach ($products as $p): ?>
            <?php render_product_card($p, cart_action_url()); ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="mt-10">
      <h2 class="font-serif text-2xl font-bold mb-4">Reviews</h2>
      <?php if (empty($reviews)): ?>
        <p class="text-sm text-on-surface-variant">No reviews yet.</p>
      <?php else: ?>
        <div class="space-y-3">
          <?php foreach ($reviews as $r): ?>
            <div class="bg-white rounded-2xl border border-outline/40 p-5">
              <div class="flex items-center justify-between">
                <strong class="text-sm"><?= h($r['customer_name'] ?? 'Customer') ?></strong>
                <span class="text-amber-400 text-xs">
                  <?php for ($i = 0; $i < (int)$r['rating']; $i++): ?><span class="material-symbols-outlined text-sm">star</span><?php endfor; ?>
                </span>
              </div>
              <p class="text-sm mt-2"><?= h($r['review_text'] ?? '') ?></p>
              <p class="text-xs text-on-surface-variant mt-2"><?= h(date('M j, Y', strtotime($r['created_at']))) ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <aside class="lg:col-span-1">
    <div class="bg-white rounded-3xl border border-outline/40 p-6 sticky top-24">
      <h3 class="font-bold mb-3">Contact & Info</h3>
      <ul class="space-y-3 text-sm">
        <?php if ($biz['phone']): ?>
        <li class="flex gap-2 items-start">
          <span class="material-symbols-outlined text-primary text-base">call</span>
          <a href="tel:<?= h($biz['phone']) ?>" class="hover:underline"><?= h($biz['phone']) ?></a>
        </li>
        <?php endif; ?>
        <?php if ($biz['address']): ?>
        <li class="flex gap-2 items-start">
          <span class="material-symbols-outlined text-primary text-base">location_on</span>
          <?= h($biz['address']) ?>
        </li>
        <?php endif; ?>
        <?php if ($biz['facebook']): ?>
        <li class="flex gap-2 items-start">
          <span class="material-symbols-outlined text-primary text-base">facebook</span>
          <a href="<?= h($biz['facebook']) ?>" target="_blank" rel="noopener" class="hover:underline break-all">Facebook Page</a>
        </li>
        <?php endif; ?>
        <?php if ($biz['online_shop']): ?>
        <li class="flex gap-2 items-start">
          <span class="material-symbols-outlined text-primary text-base">storefront</span>
          <a href="<?= h($biz['online_shop']) ?>" target="_blank" rel="noopener" class="hover:underline break-all">Online Shop</a>
        </li>
        <?php endif; ?>
      </ul>
    </div>
  </aside>
</section>

<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>