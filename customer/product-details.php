<?php
$page_title = 'Product — HerMart';
require_once __DIR__ . '/../includes/init.php';

$id = safe_int($_GET['id'] ?? 0);
$product = $id > 0 ? db_select_one(
    "SELECT p.*, b.id AS seller_id, b.owner_id AS seller_owner_id, b.shop_name AS brand, b.phone AS seller_phone, b.division, b.district, b.upazila,
            b.address AS seller_address, b.facebook, b.online_shop, b.rating AS seller_rating, b.verified,
            c.name AS category_name, c.slug AS category_slug
     FROM products p
     LEFT JOIN businesses b ON b.id = p.seller_id
     LEFT JOIN categories  c ON c.id = p.category_id
     WHERE p.id = ?",
    'i',
    [$id]
) : null;

if (!$product) {
    $page_title = 'Product not found';
}

$related = $product ? db_select(
    "SELECT p.*, b.shop_name AS brand, b.district, c.name AS category_name FROM products p
     LEFT JOIN businesses b ON b.id = p.seller_id
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.category_id = ? AND p.id <> ? AND p.status = 'active' LIMIT 4",
    'ii',
    [$product['category_id'], $product['id']]
) : [];

$reviews = $product ? db_select(
    "SELECT r.*, u.name AS customer_name FROM reviews r
     LEFT JOIN users u ON u.id = r.customer_id
     WHERE r.product_id = ? AND r.status = 'active'
     ORDER BY r.created_at DESC LIMIT 5",
    'i',
    [$product['id']]
) : [];

$is_own_product = $product && is_logged_in()
    && (current_user()['role'] ?? '') === 'seller'
    && (int)$product['seller_owner_id'] === (int)current_user()['id'];
$can_buy = $product && is_in_stock($product) && !$is_own_product;

// Has the current user purchased this product and not yet reviewed?
$can_review = false;
$has_reviewed = false;
if ($product && is_logged_in() && current_user()['role'] === 'customer') {
    $purchased = db_select_one(
        "SELECT oi.order_id FROM order_items oi
         JOIN orders o ON o.id = oi.order_id
         WHERE oi.product_id = ? AND o.customer_id = ? AND o.order_status = 'delivered'
         LIMIT 1",
        'ii', [$product['id'], current_user()['id']]
    );
    if ($purchased) {
        $can_review = true;
        $existing = db_select_one(
            "SELECT id FROM reviews WHERE customer_id = ? AND product_id = ?",
            'ii', [current_user()['id'], $product['id']]
        );
        $has_reviewed = (bool)$existing;
    }
}

include __DIR__ . '/../includes/header.php';
?>

<?php if (!$product): ?>
  <section class="max-w-7xl mx-auto px-4 py-20 text-center">
    <span class="material-symbols-outlined text-primary text-6xl">inventory_2</span>
    <h1 class="font-serif text-3xl font-bold mt-3">Product not found</h1>
    <p class="text-on-surface-variant mt-1">It may have been removed.</p>
    <a href="products.php" class="hm-btn hm-btn-primary mt-4 inline-flex">Browse products</a>
  </section>
<?php else: ?>

<section class="bg-gradient-to-b from-primary-light/40 to-transparent">
  <div class="max-w-7xl mx-auto px-4 md:px-6 py-10">
    <div class="flex items-center justify-between flex-wrap gap-3 mb-3">
      <button type="button" onclick="if(history.length>1){history.back();}else{location.href='products.php';}" class="hm-btn-back" aria-label="Go back">
        <span class="material-symbols-outlined">arrow_back</span>
        Back
      </button>
    </div>
    <nav class="text-xs text-on-surface-variant">
      <a href="../index.php" class="hover:text-primary">Home</a> ·
      <a href="products.php" class="hover:text-primary">Products</a> ·
      <a href="categories.php?cat=<?= h($product['category_slug']) ?>" class="hover:text-primary"><?= h($product['category_name']) ?></a>
      <span class="text-on-surface"> · <?= h($product['name']) ?></span>
    </nav>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 md:px-6 pb-12 grid lg:grid-cols-2 gap-10">
  <div class="bg-white rounded-3xl border border-outline/40 overflow-hidden shadow-sm-custom">
    <img <?= product_image_attrs($product['image']) ?> alt="<?= h($product['name']) ?>" class="w-full aspect-square object-cover">
  </div>

  <div>
    <span class="text-xs font-bold text-primary uppercase tracking-widest"><?= h($product['category_name']) ?></span>
    <h1 class="font-serif text-3xl md:text-4xl font-bold mt-2"><?= h($product['name']) ?></h1>
    <p class="text-on-surface-variant mt-2">by <a href="seller-details.php?id=<?= (int)$product['seller_id'] ?>" class="font-bold text-primary hover:underline"><?= h($product['brand']) ?></a></p>

    <div class="mt-5 flex items-baseline gap-3">
      <div class="text-3xl font-bold text-primary"><?= money_tk($product['discount_price'] ?? $product['price']) ?></div>
      <?php if ($product['discount_price'] !== null && (float)$product['discount_price'] < (float)$product['price']): ?>
        <div class="text-sm text-on-surface-variant line-through"><?= money_tk($product['price']) ?></div>
      <?php endif; ?>
      <div class="text-sm <?= (int)$product['stock'] > 0 ? 'text-emerald-600' : 'text-red-600' ?> font-bold ml-auto">
        <span data-restock-stock><?= (int)$product['stock'] > 0 ? 'In stock: ' . (int)$product['stock'] : 'Out of stock' ?></span>
      </div>
    </div>

    <p class="text-on-surface mt-5 leading-relaxed"><?= nl2br(h($product['description'])) ?></p>

    <div class="mt-6 grid grid-cols-2 gap-3 text-sm">
      <div class="bg-surface-dim rounded-xl p-3">
        <div class="text-xs text-on-surface-variant">Location</div>
        <div class="font-bold"><?= h($product['district']) ?>, <?= h($product['division']) ?><?= $product['upazila'] ? ', ' . h($product['upazila']) : '' ?></div>
      </div>
      <div class="bg-surface-dim rounded-xl p-3">
        <div class="text-xs text-on-surface-variant">Seller</div>
        <div class="font-bold"><?= h($product['brand']) ?></div>
      </div>
      <?php if (!empty($product['material'])): ?>
      <div class="bg-surface-dim rounded-xl p-3">
        <div class="text-xs text-on-surface-variant">Material</div>
        <div class="font-bold"><?= h($product['material']) ?></div>
      </div>
      <?php endif; ?>
      <?php if (!empty($product['color'])): ?>
      <div class="bg-surface-dim rounded-xl p-3">
        <div class="text-xs text-on-surface-variant">Color</div>
        <div class="font-bold"><?= h($product['color']) ?></div>
      </div>
      <?php endif; ?>
    </div>

    <?php if ($can_buy): ?>
      <form method="post" action="cart-action.php" class="mt-7 flex flex-wrap items-center gap-3" data-cart-add-form>
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
        <label class="flex items-center gap-2 bg-surface-dim rounded-full px-3 py-2 border border-outline/40">
          <span class="text-xs text-on-surface-variant">Qty</span>
          <input type="number" name="quantity" value="1" min="1" max="<?= (int)$product['stock'] ?>" class="w-14 bg-transparent text-center outline-none">
        </label>
        <button type="submit" data-cart-add class="hm-product-action hm-product-action-secondary">
          <span class="material-symbols-outlined">shopping_cart</span>
          Add to Cart
        </button>
        <button type="submit" name="go_cart" value="1" class="hm-product-action hm-product-action-primary">
          <span class="material-symbols-outlined">bolt</span>
          Buy Now
        </button>
      </form>
    <?php elseif ($is_own_product): ?>
      <form method="post" action="cart-action.php" class="mt-7 flex flex-wrap items-center gap-3" data-restock-form>
        <input type="hidden" name="action" value="restock">
        <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
        <label class="flex items-center gap-2 bg-surface-dim rounded-full px-3 py-2 border border-outline/40">
          <span class="text-xs text-on-surface-variant">Add stock</span>
          <input type="number" name="quantity" value="1" min="1" max="10000" class="w-16 bg-transparent text-center outline-none" aria-label="Quantity to add to stock">
        </label>
        <button type="submit" class="hm-product-action hm-product-action-secondary">
          <span class="material-symbols-outlined">add_box</span>
          Add stock
        </button>
      </form>
    <?php else: ?>
      <div class="mt-7 rounded-2xl bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
        This product is currently unavailable.
      </div>
    <?php endif; ?>

    <a href="custom-order.php?seller=<?= (int)$product['seller_id'] ?>" class="hm-btn hm-btn-light mt-3">
      <span class="material-symbols-outlined text-base">auto_awesome</span>
      Request Customization
    </a>

    <?php if ($product['seller_phone']): ?>
      <div class="mt-5 p-4 bg-primary-light rounded-2xl flex items-center gap-3">
        <span class="material-symbols-outlined text-primary">call</span>
        <div class="text-sm">
          <div class="font-bold text-primary">Contact Seller</div>
          <a href="tel:<?= h($product['seller_phone']) ?>" class="text-on-surface hover:underline"><?= h($product['seller_phone']) ?></a>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- Reviews -->
<section class="max-w-7xl mx-auto px-4 md:px-6 py-8">
  <div class="flex items-end justify-between flex-wrap gap-3 mb-4">
    <h2 class="font-serif text-2xl font-bold">Reviews</h2>
    <p class="text-sm text-on-surface-variant"><?= count($reviews) ?> review(s)</p>
  </div>

  <?php if ($can_review && !$has_reviewed): ?>
    <div class="bg-white rounded-2xl border border-outline/40 p-5 mb-6">
      <h3 class="font-bold mb-3">Write a review</h3>
      <form method="post" action="review-action.php" class="space-y-3" data-validate>
        <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
        <input type="hidden" name="seller_id" value="<?= (int)$product['seller_id'] ?>">
        <div>
          <label class="hm-label">Rating</label>
          <select name="rating" class="hm-select" required>
            <option value="5">★★★★★ — Excellent</option>
            <option value="4">★★★★ — Good</option>
            <option value="3">★★★ — Average</option>
            <option value="2">★★ — Below average</option>
            <option value="1">★ — Poor</option>
          </select>
        </div>
        <div>
          <label class="hm-label">Your review</label>
          <textarea name="review_text" class="hm-textarea" rows="3" required placeholder="Share your experience with this product..."></textarea>
        </div>
        <button class="hm-btn hm-btn-primary" type="submit">
          <span class="material-symbols-outlined text-base">rate_review</span>
          Submit Review
        </button>
      </form>
    </div>
  <?php endif; ?>

  <?php if (empty($reviews)): ?>
    <p class="text-sm text-on-surface-variant">No reviews yet for this product.</p>
  <?php else: ?>
    <div class="grid md:grid-cols-2 gap-4">
      <?php foreach ($reviews as $r): ?>
        <div class="bg-white rounded-2xl border border-outline/40 p-5">
          <div class="flex items-center justify-between mb-2">
            <strong class="text-sm"><?= h($r['customer_name'] ?? 'Customer') ?></strong>
            <span class="text-amber-400 text-xs">
              <?php for ($i = 0; $i < (int)$r['rating']; $i++): ?><span class="material-symbols-outlined text-sm">star</span><?php endfor; ?>
            </span>
          </div>
          <p class="text-sm text-on-surface"><?= h($r['review_text']) ?></p>
          <p class="text-xs text-on-surface-variant mt-2"><?= h(date('M j, Y', strtotime($r['created_at']))) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<!-- Related -->
<?php if (!empty($related)): ?>
<section class="max-w-7xl mx-auto px-4 md:px-6 py-8">
  <h2 class="font-serif text-2xl font-bold mb-4">You may also like</h2>
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
    <?php foreach ($related as $r): ?>
      <?php render_product_card($r, cart_action_url()); ?>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
