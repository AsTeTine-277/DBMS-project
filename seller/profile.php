<?php

$page_title = 'My Business — HerMart';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/seller-auth.php';

seller_only();

$user = current_user();
$biz  = current_business($user['id']);
$biz_id = (int)$biz['id'];

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_business') {
    $brand       = trim($_POST['shop_name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $division    = trim($_POST['division'] ?? '');
    $district    = trim($_POST['district'] ?? '');
    $upazila     = trim($_POST['upazila'] ?? '');
    $address     = trim($_POST['address'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $facebook    = trim($_POST['facebook'] ?? '');
    $online_shop = trim($_POST['online_shop'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($brand === '' || $phone === '') {
        $error = 'Shop name and phone are required.';
    } else {
        db_execute(
            "UPDATE businesses SET shop_name=?, category_id=?, division=?, district=?, upazila=?, address=?, phone=?, facebook=?, online_shop=?, description=? WHERE id=?",
            'sissssssssi',
            [$brand, $category_id ?: null, $division, $district, $upazila, $address, $phone, $facebook, $online_shop, $description, $biz_id]
        );
        $success = 'Business profile updated successfully.';
    }
}

$biz = db_select_one(
    "SELECT b.*, c.name AS category_name FROM businesses b LEFT JOIN categories c ON c.id = b.category_id WHERE b.id = ?",
    'i', [$biz_id]
);
$cats = categories_all();
$products = db_select(
    "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.seller_id = ? ORDER BY p.created_at DESC LIMIT 30",
    'i', [$biz_id]
);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/_layout_top.php';
?>

<?php if ($error): ?>
  <div class="rounded-xl px-4 py-3 text-sm font-medium border bg-red-50 text-red-700 border-red-200"><?= h($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
  <div class="rounded-xl px-4 py-3 text-sm font-medium border bg-emerald-50 text-emerald-700 border-emerald-200"><?= h($success) ?></div>
<?php endif; ?>

<div class="bg-white rounded-3xl border border-outline/40 p-6 md:p-8">
  <h2 class="text-xl font-bold mb-4">Business Information</h2>
  <form method="post" class="grid sm:grid-cols-2 gap-4" data-validate>
    <input type="hidden" name="action" value="update_business">
    <div class="sm:col-span-2">
      <label class="hm-label">Shop Name *</label>
      <input name="shop_name" type="text" required class="hm-input" value="<?= h($biz['shop_name']) ?>">
    </div>
    <div>
      <label class="hm-label">Category</label>
      <select name="category_id" class="hm-select">
        <option value="">-- Select --</option>
        <?php foreach ($cats as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= (int)$biz['category_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= h($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="hm-label">Phone *</label>
      <input name="phone" type="text" required class="hm-input" value="<?= h($biz['phone']) ?>">
    </div>
    <div>
      <label class="hm-label">Division</label>
      <input name="division" type="text" class="hm-input" value="<?= h($biz['division']) ?>">
    </div>
    <div>
      <label class="hm-label">District</label>
      <input name="district" type="text" class="hm-input" value="<?= h($biz['district']) ?>">
    </div>
    <div>
      <label class="hm-label">Upazila</label>
      <input name="upazila" type="text" class="hm-input" value="<?= h($biz['upazila']) ?>">
    </div>
    <div>
      <label class="hm-label">Address</label>
      <input name="address" type="text" class="hm-input" value="<?= h($biz['address']) ?>">
    </div>
    <div class="sm:col-span-2">
      <label class="hm-label">Description</label>
      <textarea name="description" class="hm-textarea" rows="3"><?= h($biz['description']) ?></textarea>
    </div>
    <div>
      <label class="hm-label">Facebook Page</label>
      <input name="facebook" type="url" class="hm-input" value="<?= h($biz['facebook']) ?>">
    </div>
    <div>
      <label class="hm-label">Online Shop URL</label>
      <input name="online_shop" type="url" class="hm-input" value="<?= h($biz['online_shop']) ?>">
    </div>
    <div class="sm:col-span-2 flex justify-end">
      <button type="submit" class="hm-btn hm-btn-primary">
        <span class="material-symbols-outlined text-base">save</span>
        Save Business Info
      </button>
    </div>
  </form>
</div>

<div class="bg-white rounded-3xl border border-outline/40 p-6 md:p-8">
  <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
    <div>
      <h2 class="text-xl font-bold">My Products</h2>
      <p class="text-xs text-on-surface-variant"><?= count($products) ?> listed</p>
    </div>
    <a href="add-product.php" class="hm-btn hm-btn-primary">
      <span class="material-symbols-outlined text-base">add</span>
      Add New Product
    </a>
  </div>

  <?php if (empty($products)): ?>
    <p class="text-sm text-on-surface-variant py-6 text-center">No products yet. <a href="add-product.php" class="text-primary font-semibold hover:underline">Add your first →</a></p>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="text-xs uppercase tracking-wider text-on-surface-variant border-b border-outline/40">
          <tr>
            <th class="text-left py-2">Product</th>
            <th class="text-left">Category</th>
            <th class="text-right">Price</th>
            <th class="text-right">Stock</th>
            <th class="text-right">Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-outline/40">
          <?php foreach ($products as $p): ?>
            <tr>
              <td class="py-3">
                <div class="flex items-center gap-3">
                  <img <?= product_image_attrs($p['image']) ?> class="w-12 h-12 rounded-lg object-cover border border-outline/40" alt="">
                  <div>
                    <div class="font-semibold"><?= h($p['name']) ?></div>
                    <div class="text-xs text-on-surface-variant"><?= h(substr($p['description'] ?? '', 0, 50)) ?></div>
                  </div>
                </div>
              </td>
              <td class="text-on-surface-variant"><?= h($p['category_name'] ?? '—') ?></td>
              <td class="text-right font-bold text-primary"><?= money_tk($p['discount_price'] ?? $p['price']) ?></td>
              <td class="text-right"><?= (int)$p['stock'] ?></td>
              <td class="text-right">
                <span class="hm-pill <?= $p['status'] === 'active' ? 'bg-emerald-100 text-emerald-700' : ($p['status'] === 'out_of_stock' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-700') ?>">
                  <?= format_status_label($p['status']) ?>
                </span>
              </td>
              <td class="text-right">
                <a href="edit-product.php?id=<?= (int)$p['id'] ?>" class="text-primary font-bold text-xs mr-2 hover:underline">Edit</a>
                <form method="post" action="delete-product.php" class="inline" onsubmit="return confirm('Remove this product?');">
                  <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                  <button class="text-red-500 font-bold text-xs hover:underline" type="submit">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
