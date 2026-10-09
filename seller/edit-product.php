<?php

$page_title = 'Edit Product — HerMart';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/seller-auth.php';

seller_only();

$user = current_user();
$biz  = current_business($user['id']);
$biz_id = (int)$biz['id'];

$product_id = safe_int($_GET['id'] ?? ($_POST['id'] ?? 0));
if ($product_id <= 0) {
    flash_set('error', 'Invalid product id.');
    redirect('seller/products.php');
}

$product = db_select_one(
    "SELECT * FROM products WHERE id = ? AND seller_id = ?",
    'ii', [$product_id, $biz_id]
);

if (!$product) {
    // Ownership failed or doesn't exist — give a friendly 404-ish message.
    http_response_code(404);
    $page_title = 'Not found — HerMart';
    include __DIR__ . '/../includes/header.php';
    include __DIR__ . '/_layout_top.php';
    ?>
    <div class="bg-white rounded-3xl border border-outline/40 p-12 text-center">
      <span class="material-symbols-outlined text-primary text-5xl">lock</span>
      <h3 class="font-bold text-xl mt-3">Product not found</h3>
      <p class="text-on-surface-variant mt-1">It may have been removed or belongs to another seller.</p>
      <a href="products.php" class="hm-btn hm-btn-primary mt-4 inline-flex">Back to my products</a>
    </div>
    <?php
    include __DIR__ . '/_layout_bottom.php';
    include __DIR__ . '/../includes/footer.php';
    exit;
}

$cats = categories_all();
$error   = '';
$success = '';

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

    if ($name === '') {
        $error = 'Product name is required.';
    } elseif ($price < 0) {
        $error = 'Price must be a positive number.';
    } elseif ($discount !== null && $discount > $price) {
        $error = 'Discount price cannot exceed the original price.';
    } elseif ($stock < 0) {
        $error = 'Stock cannot be negative.';
    } elseif (!empty($sku)) {
        $dup = db_select_one(
            "SELECT id FROM products WHERE sku = ? AND id <> ?",
            'si', [$sku, $product_id]
        );
        if ($dup) $error = 'That SKU is already in use.';
    }

    
    $newImagePath = null;
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
                    $newImagePath = 'uploads/products/' . $safe;
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

        if ($newImagePath) {
            $ok = db_execute(
              "UPDATE products SET category_id=?, name=?, description=?, price=?, discount_price=?,
               stock=?, sku=?, material=?, color=?, size=?, image=?, status=? WHERE id=? AND seller_id=?",
              'issddissssssii',
              [
                $category_id ?: null, $name, $description, $price, $discount,
                $stock, $sku ?: null, $material ?: null, $color ?: null, $size ?: null,
                $newImagePath, $status, $product_id, $biz_id
              ]
            );

            if ($ok) {
                
                db_execute(
                    "UPDATE product_images SET is_primary = 0 WHERE product_id = ?",
                    'i', [$product_id]
                );
                db_execute(
                    "INSERT INTO product_images (product_id, image_path, is_primary, sort_order)
                     VALUES (?, ?, 1, 0)",
                    'is', [$product_id, $newImagePath]
                );
            }
        } else {
            $ok = db_execute(
              "UPDATE products SET category_id=?, name=?, description=?, price=?, discount_price=?,
               stock=?, sku=?, material=?, color=?, size=?, status=? WHERE id=? AND seller_id=?",
              'issddisssssii',
              [
                $category_id ?: null, $name, $description, $price, $discount,
                $stock, $sku ?: null, $material ?: null, $color ?: null, $size ?: null,
                $status, $product_id, $biz_id
              ]
            );
        }

        if ($ok) {
            flash_set('success', 'Product updated successfully.');
            redirect('seller/products.php');
        } else {
            $error = 'Could not save changes. Please try again.';
        }
    }

    
    $product['name']         = $name;
    $product['category_id']  = $category_id;
    $product['description']  = $description;
    $product['price']        = $price;
    $product['discount_price']= $discount;
    $product['stock']        = $stock;
    $product['sku']          = $sku;
    $product['material']     = $material;
    $product['color']        = $color;
    $product['size']         = $size;
    $product['status']       = $status;
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/_layout_top.php';
?>

<?php if ($error): ?>
  <div class="rounded-xl px-4 py-3 text-sm font-medium border bg-red-50 text-red-700 border-red-200"><?= h($error) ?></div>
<?php endif; ?>

<div class="bg-white rounded-3xl border border-outline/40 p-6 md:p-8">
  <div class="flex items-center gap-3 mb-5">
    <span class="material-symbols-outlined text-primary text-3xl">edit</span>
    <div>
      <h2 class="text-xl font-bold">Edit Product</h2>
      <p class="text-xs text-on-surface-variant">Update details, price, stock, or replace the image.</p>
    </div>
  </div>

  <form method="post" enctype="multipart/form-data" class="space-y-5" data-validate>
    <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">

    <div>
      <label class="hm-label">Current Image</label>
      <div class="flex items-center gap-3">
        <img <?= product_image_attrs($product['image']) ?> alt="" class="w-24 h-24 rounded-xl object-cover border border-outline/40">
        <p class="text-xs text-on-surface-variant">Upload a new image below to replace it.</p>
      </div>
    </div>

    <div>
      <label class="hm-label">Replace Image (optional)</label>
      <input type="file" name="image" accept="image/*" class="hm-input">
      <p class="text-xs text-on-surface-variant mt-1">JPG, PNG or WEBP up to 5MB.</p>
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label class="hm-label">Product Name *</label>
        <input name="name" type="text" required class="hm-input" value="<?= h($product['name']) ?>">
      </div>
      <div>
        <label class="hm-label">Category</label>
        <select name="category_id" class="hm-select">
          <option value="0">-- Select --</option>
          <?php foreach ($cats as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= (int)$product['category_id'] === (int)$c['id'] ? 'selected' : '' ?>>
              <?= h($c['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div>
      <label class="hm-label">Description</label>
      <textarea name="description" class="hm-textarea" rows="4"><?= h($product['description']) ?></textarea>
    </div>

    <div class="grid sm:grid-cols-3 gap-4">
      <div>
        <label class="hm-label">Price (৳) *</label>
        <input name="price" type="number" min="0" step="1" required class="hm-input" value="<?= h($product['price']) ?>">
      </div>
      <div>
        <label class="hm-label">Discount Price (৳)</label>
        <input name="discount_price" type="number" min="0" step="1" class="hm-input"
               value="<?= $product['discount_price'] !== null ? h($product['discount_price']) : '' ?>" placeholder="Optional">
      </div>
      <div>
        <label class="hm-label">Stock Quantity *</label>
        <input name="stock" type="number" min="0" required class="hm-input" value="<?= (int)$product['stock'] ?>">
      </div>
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label class="hm-label">SKU</label>
        <input name="sku" type="text" class="hm-input" value="<?= h($product['sku']) ?>" placeholder="e.g. NK-001">
      </div>
      <div>
        <label class="hm-label">Status</label>
        <select name="status" class="hm-select">
          <?php
            $opts = ['active'=>'Active (visible to customers)','draft'=>'Draft (hidden)','out_of_stock'=>'Out of stock','inactive'=>'Inactive'];
            foreach ($opts as $k => $lbl):
          ?>
            <option value="<?= $k ?>" <?= $product['status'] === $k ? 'selected' : '' ?>><?= h($lbl) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="grid sm:grid-cols-3 gap-4">
      <div>
        <label class="hm-label">Color</label>
        <input name="color" type="text" class="hm-input" value="<?= h($product['color']) ?>" placeholder="e.g. Red">
      </div>
      <div>
        <label class="hm-label">Size</label>
        <input name="size" type="text" class="hm-input" value="<?= h($product['size']) ?>" placeholder="e.g. M / 16x16">
      </div>
      <div>
        <label class="hm-label">Material</label>
        <input name="material" type="text" class="hm-input" value="<?= h($product['material']) ?>" placeholder="e.g. Cotton">
      </div>
    </div>

    <div class="flex justify-end gap-3 pt-3 border-t border-outline/40">
      <a href="products.php" class="hm-btn hm-btn-outline">Cancel</a>
      <button type="submit" class="hm-btn hm-btn-primary">
        <span class="material-symbols-outlined text-base">save</span>
        Save Changes
      </button>
    </div>
  </form>
</div>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
