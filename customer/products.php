<?php
$page_title = 'All Products — HerMart';
$page_desc  = 'Browse all handmade, traditional and Pahari products from women artisans in Bangladesh.';
require_once __DIR__ . '/../includes/init.php';

$cat  = $_GET['cat'] ?? '';
$q    = trim($_GET['q'] ?? '');
$sort = $_GET['sort'] ?? 'newest';

$where = [];
$args  = [];
$types = '';

$sql = "SELECT p.*, b.shop_name AS brand, b.division, b.district, c.name AS category_name, c.slug AS category_slug
        FROM products p
        LEFT JOIN businesses b ON b.id = p.seller_id
        LEFT JOIN categories  c ON c.id = p.category_id
        WHERE p.status IN ('active','out_of_stock')";

if ($cat !== '') {
    $where[] = "c.slug = ?";
    $args[]  = $cat; $types .= 's';
}
if ($q !== '') {
  $q_like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%';
  $where[] = "(p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ? OR b.shop_name LIKE ? OR c.name LIKE ?)";
  $args[]  = $q_like; $args[] = $q_like; $args[] = $q_like; $args[] = $q_like; $args[] = $q_like; $types .= 'sssss';
}
if ($where) $sql .= ' AND ' . implode(' AND ', $where);

$order = match ($sort) {
    'price_asc'  => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    default      => 'p.created_at DESC',
};
$sql .= " ORDER BY $order";

$products = db_select($sql, $types, $args);
$cats = categories_all();

include __DIR__ . '/../includes/header.php';
?>

<section class="bg-gradient-to-b from-primary-light/40 to-transparent">
  <div class="max-w-7xl mx-auto px-4 md:px-6 py-10">
    <p class="text-xs uppercase tracking-widest font-bold text-primary">All Products</p>
    <h1 class="font-serif text-3xl md:text-4xl font-bold mt-2">Browse the marketplace</h1>
    <p class="text-on-surface-variant mt-2 max-w-2xl">Authentic handmade products from women entrepreneurs across Bangladesh.</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 md:px-6 pb-12 grid lg:grid-cols-4 gap-6">

  <!-- Filter sidebar -->
  <aside class="lg:col-span-1">
    <form method="get" class="bg-white rounded-3xl border border-outline/40 p-5 sticky top-24">
      <h3 class="font-bold mb-3">Filter</h3>

      <label class="hm-label">Search</label>
      <input type="text" name="q" value="<?= h($q) ?>" placeholder="e.g. saree, pickle..." class="hm-input mb-3">

      <label class="hm-label">Category</label>
      <select name="cat" class="hm-select mb-3">
        <option value="">All categories</option>
        <?php foreach ($cats as $c): ?>
          <option value="<?= h($c['slug']) ?>" <?= $cat === $c['slug'] ? 'selected' : '' ?>><?= h($c['name']) ?></option>
        <?php endforeach; ?>
      </select>

      <label class="hm-label">Sort</label>
      <select name="sort" class="hm-select mb-3">
        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
        <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low → High</option>
        <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High → Low</option>
      </select>

      <button type="submit" class="hm-btn hm-btn-primary w-full justify-center">
        <span class="material-symbols-outlined text-base">search</span>
        Apply
      </button>
    </form>
  </aside>

  <!-- Listing -->
  <div class="lg:col-span-3">
    <p class="text-sm text-on-surface-variant mb-4"><?= count($products) ?> products found</p>

    <?php if (empty($products)): ?>
      <div class="bg-white rounded-3xl border border-outline/40 p-10 text-center">
        <span class="material-symbols-outlined text-primary text-5xl">inventory_2</span>
        <h3 class="text-xl font-bold mt-3">No products match your filter</h3>
        <p class="text-on-surface-variant mt-1">Try clearing filters or browse all categories.</p>
        <a href="products.php" class="hm-btn hm-btn-primary mt-4 inline-flex">Clear filters</a>
      </div>
    <?php else: ?>
      <div class="products-page-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
        <?php foreach ($products as $p): ?>
          <?php render_product_card($p, cart_action_url()); ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>