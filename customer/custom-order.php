<?php
/**
 * HerMart — Custom Order page
 *
 * Lets customers submit a custom artisan order, stored in
 * the `custom_orders` table. Includes validation, success state,
 * image upload, and listing of the user's previous custom orders.
 */
$page_title = 'Custom Order — HerMart';
$page_desc  = 'Request a custom handmade product from our women artisans.';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/customer-auth.php';

customer_only();

$user  = current_user();
$error = '';
$success = '';

// Make sure uploads/products directory exists.
$uploadDir = __DIR__ . '/../uploads/custom_orders/';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title           = trim($_POST['title'] ?? '');
    $description     = trim($_POST['description'] ?? '');
    $quantity        = max(1, safe_int($_POST['quantity'] ?? 1));
    $budget          = (float)($_POST['budget'] ?? 0);
    $preferred_date  = $_POST['preferred_date'] ?? '';
    $location        = trim($_POST['location'] ?? '');
    $seller_id       = safe_int($_POST['seller_id'] ?? 0);

    if ($user['role'] !== 'customer') {
        $error = 'Only customer accounts can submit custom orders.';
    } elseif ($title === '' || $description === '' || $quantity < 1) {
        $error = 'Please fill all required fields.';
    } else {
        // Optional image upload
        $refImage = null;
        if (!empty($_FILES['reference_image']['name'])) {
            $f = $_FILES['reference_image'];
            if ($f['error'] === UPLOAD_ERR_OK) {
                $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
                if (!in_array(mime_content_type($f['tmp_name']), $allowed, true)) {
                    $error = 'Image must be JPG, PNG, WEBP or GIF.';
                } elseif ($f['size'] > 4 * 1024 * 1024) {
                    $error = 'Image must be smaller than 4MB.';
                } else {
                    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                    $safe = 'co_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    if (move_uploaded_file($f['tmp_name'], $uploadDir . $safe)) {
                        $refImage = 'uploads/custom_orders/' . $safe;
                    }
                }
            }
        }

        if (!$error) {
            $ok = db_execute(
                "INSERT INTO custom_orders (customer_id, seller_id, title, description, budget, quantity, preferred_date, location, reference_image)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                'iiisdisss',
                [$user['id'], $seller_id ?: null, $title, $description, $budget, $quantity, $preferred_date ?: null, $location ?: null, $refImage]
            );
            if ($ok) {
                $coId = db_insert_id();
                // Notify seller
                if ($seller_id) {
                    $biz = business_by_id($seller_id);
                    if ($biz && $biz['owner_id']) {
                        db_execute(
                            "INSERT INTO notifications (user_id, type, title, body, link) VALUES (?, 'custom_order', ?, ?, ?)",
                            'isss',
                            [$biz['owner_id'], 'New custom order request', 'A customer submitted: ' . $title, 'seller/custom-orders.php']
                        );
                    }
                }
                flash_set('success', 'Your custom order has been submitted! Sellers will respond shortly.');
                redirect('customer/custom-order.php');
            } else {
                $error = 'Could not submit your order. Please try again.';
            }
        }
    }
}

$my_orders = db_select(
    "SELECT co.*, b.shop_name AS seller_name FROM custom_orders co
     LEFT JOIN businesses b ON b.id = co.seller_id
     WHERE co.customer_id = ? ORDER BY co.created_at DESC LIMIT 20",
    'i',
    [$user['id']]
);

$sellers = db_select("SELECT id, shop_name, district, division FROM businesses WHERE status = 'active' ORDER BY verified DESC LIMIT 30");
$preselectedSeller = safe_int($_GET['seller'] ?? 0);

include __DIR__ . '/../includes/header.php';
?>

<section class="bg-gradient-to-b from-primary-light/40 to-transparent">
  <div class="max-w-7xl mx-auto px-4 md:px-6 py-10 md:py-14">
    <div class="mb-4">
      <button type="button" onclick="if(history.length>1){history.back();}else{location.href='home.php';}" class="hm-btn-back" aria-label="Go back">
        <span class="material-symbols-outlined">arrow_back</span>
        Back
      </button>
    </div>
    <p class="text-xs uppercase tracking-widest font-bold text-primary mb-2">Custom Order</p>
    <h1 class="font-serif text-3xl md:text-4xl font-bold text-on-surface">Request a custom artisan creation</h1>
    <p class="text-on-surface-variant max-w-2xl mt-3">
      Have something special in mind? Tell our women artisans what you'd like made.
      We'll match you with the right seller and you'll get a quote within 24 hours.
    </p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 md:px-6 py-10 grid lg:grid-cols-5 gap-8">

  <!-- FORM -->
  <div class="lg:col-span-3">
    <div class="bg-white rounded-3xl border border-outline/40 p-6 md:p-8 shadow-sm-custom">
      <h2 class="text-xl font-bold mb-1">Order details</h2>
      <p class="text-sm text-on-surface-variant mb-5">All fields marked * are required.</p>

      <?php if ($error): ?>
        <div class="mb-4 rounded-xl px-4 py-3 text-sm font-medium border bg-red-50 text-red-700 border-red-200">
          <?= h($error) ?>
        </div>
      <?php endif; ?>

      <form method="post" enctype="multipart/form-data" class="space-y-4" data-validate>
        <div>
          <label class="hm-label" for="title">Request Title *</label>
          <input id="title" name="title" type="text" required class="hm-input" placeholder="e.g. Custom Wedding Saree">
        </div>

        <div>
          <label class="hm-label" for="seller_id">Preferred Seller</label>
          <select id="seller_id" name="seller_id" class="hm-select">
            <option value="0">No preference (auto-match)</option>
            <?php foreach ($sellers as $s): ?>
              <option value="<?= (int)$s['id'] ?>" <?= $preselectedSeller === (int)$s['id'] ? 'selected' : '' ?>>
                <?= h($s['shop_name']) ?> — <?= h($s['district']) ?>, <?= h($s['division']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="hm-label" for="description">Customization Details *</label>
          <textarea id="description" name="description" required class="hm-textarea" rows="6"
            placeholder="Describe size, colors, fabric, design references, occasions..."></textarea>
        </div>

        <div class="grid sm:grid-cols-3 gap-4">
          <div>
            <label class="hm-label" for="quantity">Quantity *</label>
            <input id="quantity" name="quantity" type="number" min="1" max="500" required class="hm-input" value="1">
          </div>
          <div>
            <label class="hm-label" for="budget">Budget (৳)</label>
            <input id="budget" name="budget" type="number" min="0" step="50" class="hm-input" placeholder="e.g. 5000">
          </div>
          <div>
            <label class="hm-label" for="preferred_date">Preferred by</label>
            <input id="preferred_date" name="preferred_date" type="date" min="<?= date('Y-m-d') ?>" class="hm-input">
          </div>
        </div>

        <div>
          <label class="hm-label" for="location">Delivery Location</label>
          <input id="location" name="location" type="text" class="hm-input" placeholder="City, area">
        </div>

        <div>
          <label class="hm-label" for="reference_image">Reference Image (optional)</label>
          <input id="reference_image" name="reference_image" type="file" accept="image/*" class="hm-input">
        </div>

        <div class="flex flex-wrap items-center gap-3 pt-2">
          <button type="submit" class="hm-btn hm-btn-primary">
            <span class="material-symbols-outlined text-base">send</span>
            Submit Custom Order
          </button>
          <a href="home.php" class="hm-btn hm-btn-outline">Cancel</a>
        </div>
      </form>
    </div>

    <!-- MY ORDERS -->
    <div class="mt-8 bg-white rounded-3xl border border-outline/40 p-6 md:p-8 shadow-sm-custom">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold">My Custom Orders</h2>
        <span class="text-xs text-on-surface-variant"><?= count($my_orders) ?> total</span>
      </div>

      <?php if (empty($my_orders)): ?>
        <p class="text-sm text-on-surface-variant py-6 text-center">You haven't placed any custom orders yet.</p>
      <?php else: ?>
        <div class="divide-y divide-outline/40">
          <?php foreach ($my_orders as $o): ?>
            <div class="py-4 flex items-start gap-4">
              <?php if (!empty($o['reference_image'])): ?>
                <img <?= site_image_attrs($o['reference_image']) ?> class="w-14 h-14 rounded-xl object-cover border border-outline/40 shrink-0" alt="">
              <?php else: ?>
                <div class="w-11 h-11 rounded-xl bg-primary-light flex items-center justify-center shrink-0">
                  <span class="material-symbols-outlined text-primary">auto_awesome</span>
                </div>
              <?php endif; ?>
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between flex-wrap gap-2">
                  <h3 class="font-bold text-on-surface"><?= h($o['title']) ?></h3>
                  <span class="hm-pill <?= status_pill_class($o['status'], 'custom') ?>">
                    <?= format_status_label($o['status']) ?>
                  </span>
                </div>
                <p class="text-sm text-on-surface-variant mt-1 line-clamp-2"><?= h($o['description']) ?></p>
                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-on-surface-variant">
                  <span>Qty: <strong class="text-on-surface"><?= (int)$o['quantity'] ?></strong></span>
                  <?php if ($o['budget']): ?><span>Budget: <strong class="text-on-surface"><?= money_tk($o['budget']) ?></strong></span><?php endif; ?>
                  <?php if ($o['preferred_date']): ?><span>By: <strong class="text-on-surface"><?= h($o['preferred_date']) ?></strong></span><?php endif; ?>
                  <?php if ($o['seller_name']): ?><span>Seller: <strong class="text-on-surface"><?= h($o['seller_name']) ?></strong></span><?php endif; ?>
                  <span>Submitted: <?= h(date('M j, Y', strtotime($o['created_at']))) ?></span>
                </div>
                <?php if ($o['quoted_price']): ?>
                  <p class="text-xs mt-2 text-emerald-700 font-semibold">Quoted price: <?= money_tk($o['quoted_price']) ?></p>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- INFO -->
  <aside class="lg:col-span-2 space-y-4">
    <div class="bg-white rounded-3xl border border-outline/40 p-6">
      <h3 class="font-bold mb-3 flex items-center gap-2">
        <span class="material-symbols-outlined text-primary">workspace_premium</span>
        Why custom order?
      </h3>
      <ul class="space-y-2 text-sm text-on-surface-variant">
        <li class="flex gap-2"><span class="material-symbols-outlined text-primary text-base">check_circle</span>Made-to-measure by skilled women artisans</li>
        <li class="flex gap-2"><span class="material-symbols-outlined text-primary text-base">check_circle</span>Direct conversation with the maker</li>
        <li class="flex gap-2"><span class="material-symbols-outlined text-primary text-base">check_circle</span>Flexible budget & delivery date</li>
        <li class="flex gap-2"><span class="material-symbols-outlined text-primary text-base">check_circle</span>Authentic, one-of-a-kind product</li>
      </ul>
    </div>

    <div class="bg-primary text-white rounded-3xl p-6">
      <h3 class="font-bold mb-2">How it works</h3>
      <ol class="space-y-3 text-sm text-pink-100/90">
        <li class="flex gap-3"><span class="font-bold text-white">1.</span> Submit your request with details</li>
        <li class="flex gap-3"><span class="font-bold text-white">2.</span> We match you with a verified artisan</li>
        <li class="flex gap-3"><span class="font-bold text-white">3.</span> Receive a quote & timeline</li>
        <li class="flex gap-3"><span class="font-bold text-white">4.</span> Approve & track delivery</li>
      </ol>
    </div>

    <div class="bg-white rounded-3xl border border-outline/40 p-6">
      <h3 class="font-bold mb-3 flex items-center gap-2">
        <span class="material-symbols-outlined text-primary">support_agent</span>
        Need help?
      </h3>
      <p class="text-sm text-on-surface-variant mb-3">Our support team can help you craft the perfect order brief.</p>
      <a href="#" class="hm-btn hm-btn-light w-full justify-center">
        <span class="material-symbols-outlined text-base">call</span>
        Contact Support
      </a>
    </div>
  </aside>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
