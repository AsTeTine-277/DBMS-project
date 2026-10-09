<?php

$page_title = 'My Products — HerMart';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/seller-auth.php';

seller_only();

$user = current_user();
$biz  = current_business($user['id']);

$status_filter = $_GET['status'] ?? '';
$allowed = ['active','draft','out_of_stock','inactive'];
if (!in_array($status_filter, $allowed, true)) {
    $status_filter = '';
}

$where = ["p.seller_id = ?"];
$args  = [$biz['id']];
$types = 'i';
if ($status_filter !== '') {
    $where[] = "p.status = ?";
    $args[]  = $status_filter;
    $types  .= 's';
}
$whereSql = implode(' AND ', $where);

$products = db_select(
    "SELECT p.*, c.name AS category_name FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE $whereSql
     ORDER BY p.created_at DESC",
    $types,
    $args
);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/_layout_top.php';
?>

<div class="flex items-center justify-between gap-3 flex-wrap">
  <div>
    <h2 class="text-xl font-bold">My Products</h2>
    <p class="text-xs text-on-surface-variant"><?= count($products) ?> listed</p>
  </div>
  <a href="add-product.php" class="hm-btn hm-btn-primary">
    <span class="material-symbols-outlined text-base">add</span>
    Add New Product
  </a>
</div>

<form method="get" class="flex items-center gap-2 bg-white rounded-2xl border border-outline/40 p-3">
  <label class="text-xs font-bold text-on-surface-variant">Filter by status:</label>
  <select name="status" class="hm-select w-auto" onchange="this.form.submit()">
    <option value="">All</option>
    <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Active</option>
    <option value="draft" <?= $status_filter === 'draft' ? 'selected' : '' ?>>Draft</option>
    <option value="out_of_stock" <?= $status_filter === 'out_of_stock' ? 'selected' : '' ?>>Out of stock</option>
    <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
  </select>
</form>

<?php if (empty($products)): ?>
  <div class="bg-white rounded-3xl border border-outline/40 p-12 text-center">
    <span class="material-symbols-outlined text-primary text-5xl">inventory_2</span>
    <h3 class="font-bold text-xl mt-3">No products yet</h3>
    <p class="text-on-surface-variant mt-1">Add your first product to start selling.</p>
    <a href="add-product.php" class="hm-btn hm-btn-primary mt-4 inline-flex">Add Product</a>
  </div>
<?php else: ?>
  <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
    <?php foreach ($products as $p): ?>
      <div class="bg-white rounded-2xl border border-outline/40 overflow-hidden">
        <img <?= product_image_attrs($p['image']) ?> alt="<?= h($p['name']) ?>" class="w-full aspect-square object-cover">
        <div class="p-3">
          <div class="flex items-center justify-between gap-2 mb-1">
            <span class="hm-pill <?= $p['status'] === 'active' ? 'bg-emerald-100 text-emerald-700' : ($p['status'] === 'out_of_stock' ? 'bg-amber-100 text-amber-700' : ($p['status'] === 'draft' ? 'bg-gray-100 text-gray-700' : 'bg-red-100 text-red-700')) ?>">
              <?= format_status_label($p['status']) ?>
            </span>
            <span class="text-xs text-on-surface-variant">Stock <?= (int)$p['stock'] ?></span>
          </div>
          <h3 class="font-bold text-sm line-clamp-2 mt-1"><?= h($p['name']) ?></h3>
          <p class="text-xs text-on-surface-variant"><?= h($p['category_name'] ?? '—') ?></p>
          <div class="mt-2 flex items-center justify-between">
            <span class="font-bold text-primary"><?= money_tk($p['discount_price'] ?? $p['price']) ?></span>
          </div>
          <div class="mt-3 flex gap-2">
            <a href="edit-product.php?id=<?= (int)$p['id'] ?>" class="hm-btn hm-btn-light text-xs flex-1 justify-center">
              <span class="material-symbols-outlined text-base">edit</span> Edit
            </a>
            <form method="post" action="delete-product.php" class="inline" onsubmit="return confirm('Remove this product?');">
              <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
              <button type="submit" class="hm-btn hm-btn-light text-xs text-red-600 border-red-200 justify-center">
                <span class="material-symbols-outlined text-base">delete</span>
              </button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
