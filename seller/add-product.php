<?php

$page_title = 'Add Product — HerMart';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/seller-auth.php';

seller_only();

$user = current_user();
$biz  = current_business($user['id']);
$biz_id = (int)$biz['id'];

$error   = '';
$success = '';

$cats = categories_all();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name         = trim($_POST['name'] ?? '');
    $category_id  = safe_int($_POST['category_id'] ?? 0);
    $description  = trim($_POST['description'] ?? '');
    $price        = (float)($_POST['price'] ?? 0);
    $discount     = ($_POST['discount_price'] ?? '') !== '' ? (float)$_POST['discount_price'] : null;
    $stock        = max(0, safe_int($_POST['stock'] ?? 0));
    $sku          = trim($_POST['sku'] ?? '');
    $material     = trim($_POST['material'] ?? '');
    $color        = trim($_POST['color'] ?? '');
    $size         = trim($_POST['size'] ?? '');
    $status       = in_array($_POST['status'] ?? '', ['active','draft','out_of_stock','inactive'], true)
                        ? $_POST['status'] : 'active';
   
    if (!empty($_POST['save_draft'])) {
        $status = 'draft';
    }

   
    if ($name === '') {
        $error = 'Product name is required.';
    } elseif ($price < 0) {
        $error = 'Price must be a positive number.';
    } elseif ($discount !== null && $discount > $price) {
        $error = 'Discount price cannot exceed the original price.';
    } elseif ($stock < 0) {
        $error = 'Stock cannot be negative.';
    } elseif (!empty($sku)) {
        $dup = db_select_one("SELECT id FROM products WHERE sku = ? AND id <> ?", 'si', [$sku, 0]);
        if ($dup) $error = 'That SKU is already in use.';
    }

    
    $imagePath = null;
    if (!$error && !empty($_FILES['image']['name'])) {
        $f = $_FILES['image'];
        if ($f['error'] !== UPLOAD_ERR_OK) {
            $error = 'Image upload failed (code ' . (int)$f['error'] . ').';
        } else {
            $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
            $mime = mime_content_type($f['tmp_name']);
            if (!in_array($mime, $allowed, true)) {
                $error = 'Image must be a JPG, PNG, WEBP or GIF file.';
            } elseif ($f['size'] > 5 * 1024 * 1024) {
                $error = 'Image must be smaller than 5MB.';
            } else {
                $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg','jpeg','png','webp','gif'], true)) {
                    $ext = 'jpg';
                }
                $safe = 'p_' . bin2hex(random_bytes(6)) . '.' . $ext;
                $uploadDir = __DIR__ . '/../uploads/products/';
                if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
                $dest = $uploadDir . $safe;
                if (move_uploaded_file($f['tmp_name'], $dest)) {
                    $imagePath = 'uploads/products/' . $safe;
                } else {
                    $error = 'Could not save uploaded image.';
                }
            }
        }
    }

    if (!$error) {
        
        if ($stock <= 0 && $status === 'active') {
            $status = 'out_of_stock';
        }
        $ok = db_execute(
          "INSERT INTO products
           (seller_id, category_id, name, description, price, discount_price, stock, sku, material, color, size, image, status)
           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
          'iissddissssss',
          [$biz_id, $category_id ?: null, $name, $description, $price, $discount, $stock, $sku ?: null, $material ?: null, $color ?: null, $size ?: null, $imagePath, $status]
        );
        if ($ok) {
            $newId = db_insert_id();
            if ($imagePath) {
                db_execute(
                    "INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, 1, 0)",
                    'is', [$newId, $imagePath]
                );
            }
            flash_set('success', 'Product published successfully!');
            redirect('seller/products.php');
        } else {
            $error = 'Could not save the product. Please try again.';
        }
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/_layout_top.php';
?>

<?php if ($error): ?>
  <div class="rounded-xl px-4 py-3 text-sm font-medium border bg-red-50 text-red-700 border-red-200"><?= h($error) ?></div>
<?php endif; ?>

<div class="bg-white rounded-3xl border border-outline/40 p-6 md:p-8">
  <div class="flex items-center gap-3 mb-5">
    <span class="material-symbols-outlined text-primary text-3xl">add_box</span>
    <div>
      <h2 class="text-xl font-bold">Add New Product</h2>
      <p class="text-xs text-on-surface-variant">Publish a new product to your shop.</p>
    </div>
  </div>

  <form method="post" enctype="multipart/form-data" class="space-y-5" data-validate>
    <div>
      <label class="hm-label">Product Image</label>
      <input type="file" name="image" accept="image/*" class="hm-input">
      <p class="text-xs text-on-surface-variant mt-1">JPG, PNG or WEBP up to 5MB.</p>
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label class="hm-label">Product Name *</label>
        <input name="name" type="text" required class="hm-input" placeholder="e.g. Embroidered cushion cover">
      </div>
      <div>
        <label class="hm-label">Category</label>
        <select name="category_id" class="hm-select">
          <option value="0">-- Select --</option>
          <?php foreach ($cats as $c): ?>
            <option value="<?= (int)$c['id'] ?>"><?= h($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div>
      <label class="hm-label">Description</label>
      <textarea name="description" class="hm-textarea" rows="4" placeholder="Describe your product..."></textarea>
    </div>

    <div class="grid sm:grid-cols-3 gap-4">
      <div>
        <label class="hm-label">Price (৳) *</label>
        <input name="price" type="number" min="0" step="1" required class="hm-input">
      </div>
      <div>
        <label class="hm-label">Discount Price (৳)</label>
        <input name="discount_price" type="number" min="0" step="1" class="hm-input" placeholder="Optional">
      </div>
      <div>
        <label class="hm-label">Stock Quantity *</label>
        <input name="stock" type="number" min="0" required class="hm-input" value="1">
      </div>
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label class="hm-label">SKU</label>
        <input name="sku" type="text" class="hm-input" placeholder="e.g. NK-001">
      </div>
      <div>
        <label class="hm-label">Status</label>
        <select name="status" class="hm-select">
          <option value="active">Active (visible to customers)</option>
          <option value="draft">Draft (hidden)</option>
          <option value="out_of_stock">Out of stock</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>
    </div>

    <div class="grid sm:grid-cols-3 gap-4">
      <div>
        <label class="hm-label">Color</label>
        <input name="color" type="text" class="hm-input" placeholder="e.g. Red">
      </div>
      <div>
        <label class="hm-label">Size</label>
        <input name="size" type="text" class="hm-input" placeholder="e.g. M / 16x16">
      </div>
      <div>
        <label class="hm-label">Material</label>
        <input name="material" type="text" class="hm-input" placeholder="e.g. Cotton">
      </div>
    </div>

    <div class="flex justify-end gap-3 pt-3 border-t border-outline/40">
      <a href="products.php" class="hm-btn hm-btn-outline">Cancel</a>
      <button type="submit" name="save_draft" value="1" class="hm-btn hm-btn-light">
        <span class="material-symbols-outlined text-base">draft</span>
        Save Draft
      </button>
      <button type="submit" class="hm-btn hm-btn-primary">
        <span class="material-symbols-outlined text-base">publish</span>
        Publish Product
      </button>
    </div>
  </form>
</div>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
