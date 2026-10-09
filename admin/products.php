<?php
/**
 * HerMart — Admin: Products
 *
 * Platform-wide product list. Admin can update status and delete any product.
 */
$page_title = 'Products — Admin — HerMart';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/admin-auth.php';

admin_only();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = safe_int($_POST['product_id'] ?? 0);
    $action     = $_POST['action'] ?? '';

    if ($product_id > 0) {
        if ($action === 'change_status') {
            $next = $_POST['status'] ?? '';
            $allowed = ['active','draft','out_of_stock','inactive'];
            if (in_array($next, $allowed, true)) {
                db_execute("UPDATE products SET status = ? WHERE id = ?", 'si', [$next, $product_id]);
                flash_set('success', 'Product status updated.');
            }
        } elseif ($action === 'delete') {
            db_execute("DELETE FROM product_images WHERE product_id = ?", 'i', [$product_id]);
            db_execute("DELETE FROM products WHERE id = ?", 'i', [$product_id]);
            flash_set('success', 'Product removed.');
        }
    }
    redirect('admin/products.php' . (!empty($_GET['status']) ? '?status=' . urlencode($_GET['status']) : ''));
}

$status_filter = $_GET['status'] ?? '';
$allowed = ['active','draft','out_of_stock','inactive'];
if (!in_array($status_filter, $allowed, true)) {
    $status_filter = '';
}

$where = ['1=1'];
$args  = [];
$types = '';
if ($status_filter !== '') {
    $where[] = 'p.status = ?';
    $args[]  = $status_filter;
    $types  .= 's';
}
$whereSql = implode(' AND ', $where);

$rows = db_select(
    "SELECT p.*, b.shop_name AS brand_name, c.name AS category_name
     FROM products p
     LEFT JOIN businesses b ON b.id = p.seller_id
     LEFT JOIN categories  c ON c.id = p.category_id
     WHERE $whereSql
     ORDER BY p.created_at DESC LIMIT 200",
    $types, $args
);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/_layout_top.php';
?>

<div>
  <h2 class="text-xl font-bold">Products</h2>
  <p class="text-xs text-on-surface-variant"><?= count($rows) ?> listed</p>
</div>

<form method="get" class="bg-white rounded-2xl border border-outline/40 p-3 flex items-center gap-2">
  <label class="text-xs font-bold text-on-surface-variant">Status:</label>
  <select name="status" class="hm-select w-auto" onchange="this.form.submit()">
    <option value="">All</option>
    <?php foreach ($allowed as $s): ?>
      <option value="<?= $s ?>" <?= $status_filter === $s ? 'selected' : '' ?>><?= format_status_label($s) ?></option>
    <?php endforeach; ?>
  </select>
</form>

<div class="bg-white rounded-3xl border border-outline/40 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-xs uppercase tracking-wider text-on-surface-variant bg-surface-container/40 border-b border-outline/40">
        <tr>
          <th class="text-left py-3 px-4">Product</th>
          <th class="text-left">Seller</th>
          <th class="text-left">Category</th>
          <th class="text-right">Price</th>
          <th class="text-right">Stock</th>
          <th class="text-left">Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-outline/40">
        <?php foreach ($rows as $p): ?>
          <tr>
            <td class="py-3 px-4">
              <div class="flex items-center gap-3">
                <img <?= product_image_attrs($p['image']) ?> class="w-10 h-10 rounded-lg object-cover border border-outline/40" alt="">
                <div>
                  <div class="font-semibold line-clamp-1"><?= h($p['name']) ?></div>
                  <div class="text-xs text-on-surface-variant">#<?= (int)$p['id'] ?> · <?= h(date('M j, Y', strtotime($p['created_at']))) ?></div>
                </div>
              </div>
            </td>
            <td class="text-xs"><?= h($p['brand_name'] ?? '—') ?></td>
            <td class="text-xs"><?= h($p['category_name'] ?? '—') ?></td>
            <td class="text-right font-bold text-primary"><?= money_tk($p['discount_price'] ?? $p['price']) ?></td>
            <td class="text-right"><?= (int)$p['stock'] ?></td>
            <td class="text-xs"><?= format_status_label($p['status']) ?></td>
            <td class="pr-4 text-right">
              <div class="admin-actions">
                <form method="post">
                  <input type="hidden" name="action" value="change_status">
                  <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                  <select name="status" class="hm-select w-auto py-1 text-xs">
                    <?php foreach ($allowed as $s): ?>
                      <option value="<?= $s ?>" <?= $p['status'] === $s ? 'selected' : '' ?>><?= format_status_label($s) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <button type="submit" class="hm-btn hm-btn-outline text-xs mt-1">Save</button>
                </form>

                <form method="post" onsubmit="return confirm('Delete this product?');">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                  <button type="submit" class="hm-btn hm-btn-outline text-red-500 text-xs">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
