<?php
/**
 * HerMart — Global Search
 *
 * Searches across products, businesses (sellers) and categories. The result
 * is grouped into three sections so customers can pick what they want.
 */
$page_title = 'Search — HerMart';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/customer-auth.php';
customer_only();

$q = trim($_GET['q'] ?? '');
$q_like = '%' . str_replace(['%','_'], ['\%','\_'], $q) . '%';

$products = [];
$businesses = [];
$categories = [];

if ($q !== '') {
    $products = db_select(
        "SELECT p.*, b.shop_name AS brand_name, c.name AS category_name
         FROM products p
         LEFT JOIN businesses b ON b.id = p.seller_id
         LEFT JOIN categories  c ON c.id = p.category_id
         WHERE p.status = 'active' AND (
             p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ?
             OR b.shop_name LIKE ? OR c.name LIKE ?
         )
         ORDER BY p.created_at DESC LIMIT 24",
        'sssss', [$q_like, $q_like, $q_like, $q_like, $q_like]
    );

    $businesses = db_select(
        "SELECT b.*, c.name AS category_name
         FROM businesses b
         LEFT JOIN categories c ON c.id = b.category_id
         WHERE b.shop_name LIKE ? OR b.description LIKE ? OR b.division LIKE ? OR b.district LIKE ?
         ORDER BY b.shop_name LIMIT 24",
        'ssss', [$q_like, $q_like, $q_like, $q_like]
    );

    $categories = db_select(
        "SELECT * FROM categories
         WHERE name LIKE ? OR description LIKE ?
         ORDER BY name LIMIT 24",
        'ss', [$q_like, $q_like]
    );
}

include __DIR__ . '/../includes/header.php';
?>

<div class="hm-container py-8">
  <div class="mb-3">
    <button type="button" onclick="if(history.length>1){history.back();}else{location.href='home.php';}" class="hm-btn-back" aria-label="Go back">
      <span class="material-symbols-outlined">arrow_back</span>
      Back
    </button>
  </div>
  <form method="get" class="bg-white rounded-3xl border border-outline/40 p-4 flex items-center gap-2">
    <span class="material-symbols-outlined text-primary">search</span>
    <input type="text" name="q" value="<?= h($q) ?>" placeholder="Search products, sellers, categories…" class="hm-input flex-1" autofocus>
    <button type="submit" class="hm-btn hm-btn-primary">Search</button>
  </form>

  <?php if ($q === ''): ?>
    <div class="bg-white rounded-3xl border border-outline/40 p-12 text-center mt-6">
      <span class="material-symbols-outlined text-primary text-5xl">travel_explore</span>
      <h3 class="font-bold text-xl mt-3">Find anything on HerMart</h3>
      <p class="text-on-surface-variant mt-1">Try a product name, seller, or category like "embroidery", "Dhaka", "food".</p>
    </div>
  <?php else: ?>
    <p class="text-xs text-on-surface-variant mt-4">
      <?= count($products) ?> products ·
      <?= count($businesses) ?> sellers ·
      <?= count($categories) ?> categories for "<strong><?= h($q) ?></strong>"
    </p>

    <?php if (!empty($categories)): ?>
      <section class="mt-6">
        <h3 class="font-bold mb-3">Categories</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
          <?php foreach ($categories as $c): ?>
            <a href="categories.php?cat=<?= (int)$c['id'] ?>" class="bg-white rounded-2xl border border-outline/40 p-4 flex items-center gap-3 hover:border-primary">
              <span class="material-symbols-outlined text-primary"><?= h($c['icon'] ?: 'category') ?></span>
              <div>
                <div class="font-bold"><?= h($c['name']) ?></div>
                <div class="text-[11px] text-on-surface-variant">Browse →</div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <?php if (!empty($businesses)): ?>
      <section class="mt-8">
        <h3 class="font-bold mb-3">Sellers</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
          <?php foreach ($businesses as $b): ?>
            <a href="seller-details.php?id=<?= (int)$b['id'] ?>" class="bg-white rounded-2xl border border-outline/40 p-4 hover:border-primary">
              <div class="font-bold"><?= h($b['shop_name']) ?></div>
              <div class="text-xs text-on-surface-variant"><?= h($b['category_name'] ?? '—') ?></div>
              <div class="text-xs mt-1"><?= h($b['division'] ?? '') ?><?= !empty($b['district']) ? ', ' . h($b['district']) : '' ?></div>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <?php if (!empty($products)): ?>
      <section class="mt-8">
        <h3 class="font-bold mb-3">Products</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
          <?php foreach ($products as $p): ?>
            <?php render_product_card($p, cart_action_url()); ?>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <?php if (empty($products) && empty($businesses) && empty($categories)): ?>
      <div class="bg-white rounded-3xl border border-outline/40 p-12 text-center mt-6">
        <span class="material-symbols-outlined text-primary text-5xl">search_off</span>
        <h3 class="font-bold text-xl mt-3">No results</h3>
        <p class="text-on-surface-variant mt-1">Try a different keyword.</p>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
