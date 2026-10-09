<?php
$page_title = 'Categories — HerMart';
require_once __DIR__ . '/../includes/init.php';

$cats = db_select("SELECT c.*, COUNT(p.id) AS product_count FROM categories c
                   LEFT JOIN products p ON p.category_id = c.id
                   GROUP BY c.id ORDER BY c.name ASC");

$active = $_GET['cat'] ?? '';
$catInfo = null;
$products = [];

if ($active !== '') {
    $catInfo = db_select_one("SELECT * FROM categories WHERE slug = ?", 's', [$active]);
    if ($catInfo) {
        $products = db_select(
            "SELECT p.*, b.shop_name AS brand, b.district, c.name AS category_name FROM products p
             LEFT JOIN businesses b ON b.id = p.seller_id
             LEFT JOIN categories  c ON c.id = p.category_id
             WHERE p.category_id = ? AND p.status IN ('active','out_of_stock') ORDER BY p.created_at DESC LIMIT 60",
            'i', [(int)$catInfo['id']]
        );
    }
}

include __DIR__ . '/../includes/header.php';
?>

<section class="bg-gradient-to-b from-primary-light/40 to-transparent">
  <div class="max-w-7xl mx-auto px-4 md:px-6 py-10">
    <p class="text-xs uppercase tracking-widest font-bold text-primary">Categories</p>
    <h1 class="font-serif text-3xl md:text-4xl font-bold mt-2">
      <?= $catInfo ? h($catInfo['name']) : 'Browse by category' ?>
    </h1>
    <p class="text-on-surface-variant mt-2 max-w-2xl">
      <?= $catInfo ? 'Explore all products in this category.' : 'Pick a category to discover products from women artisans.' ?>
    </p>
  </div>
</section>

<?php if (!$catInfo): ?>
<section class="max-w-7xl mx-auto px-4 md:px-6 pb-12">
  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
    <?php foreach ($cats as $c): ?>
      <a href="?cat=<?= h($c['slug']) ?>" class="hm-card p-6 flex flex-col items-center text-center group">
        <div class="w-16 h-16 rounded-full bg-primary-light flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
          <span class="material-symbols-outlined text-primary text-4xl"><?= h($c['icon']) ?></span>
        </div>
        <h3 class="font-bold text-on-surface"><?= h($c['name']) ?></h3>
        <p class="text-xs text-on-surface-variant mt-1"><?= (int)$c['product_count'] ?> products</p>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php else: ?>

<section class="max-w-7xl mx-auto px-4 md:px-6 pb-12">
  <div class="mb-4 flex items-center justify-between flex-wrap gap-2">
    <p class="text-sm text-on-surface-variant"><?= count($products) ?> products in <strong class="text-on-surface"><?= h($catInfo['name']) ?></strong></p>
    <a href="categories.php" class="text-sm text-primary font-bold hover:underline">← All categories</a>
  </div>

  <?php if (empty($products)): ?>
    <div class="bg-white rounded-3xl border border-outline/40 p-10 text-center">
      <p class="text-on-surface-variant">No products in this category yet.</p>
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
      <?php foreach ($products as $p): ?>
        <?php render_product_card($p, cart_action_url()); ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>