<?php
/**
 * HerMart - Helper functions (non-auth).
 *
 * Auth-related helpers (current_user, is_logged_in, require_login,
 * require_role, flash_set, flash_get, redirect) live in `auth.php`,
 * which is loaded by every page through `init.php`.
 *
 * The asset / pagination / status helpers below are the page-level
 * utility belt.
 */

require_once __DIR__ . '/db.php';

function url($path) {
    // Pass-through URL helper. Kept so call sites can wrap URLs in one
    // place if we ever add a base-prefix or CDN host.
    return $path;
}

function user_by_id($id) {
    return db_select_one(
        "SELECT id, name, email, phone, role, status, address, created_at FROM users WHERE id = ? LIMIT 1",
        'i',
        [$id]
    );
}

/**
 * Resolve the business row owned by the currently logged-in user.
 * Creates one if it doesn't exist yet (helps the seller portal).
 */
function current_business($user_id) {
    $biz = db_select_one(
        "SELECT b.*, c.name AS category_name
         FROM businesses b
         LEFT JOIN categories c ON c.id = b.category_id
         WHERE b.owner_id = ? LIMIT 1",
        'i',
        [$user_id]
    );
    if (!$biz) {
        $u = user_by_id($user_id);
        if (!$u) return null;
        db_execute(
            "INSERT INTO businesses (owner_id, shop_name, phone, address) VALUES (?, ?, ?, ?)",
            'isss',
            [$user_id, $u['name'] . "'s Shop", $u['phone'], $u['address']]
        );
        $biz = db_select_one(
            "SELECT b.*, c.name AS category_name
             FROM businesses b LEFT JOIN categories c ON c.id = b.category_id
             WHERE b.owner_id = ? LIMIT 1",
            'i', [$user_id]
        );
    }
    return $biz;
}

/**
 * Get or create the cart row for the logged-in customer.
 */
function current_cart($customer_id) {
    $cart = db_select_one("SELECT * FROM cart WHERE customer_id = ?", 'i', [$customer_id]);
    if (!$cart) {
        db_execute("INSERT INTO cart (customer_id) VALUES (?)", 'i', [$customer_id]);
        $cart = db_select_one("SELECT * FROM cart WHERE customer_id = ?", 'i', [$customer_id]);
    }
    return $cart;
}

/**
 * Return all cart items joined with product info for the given customer.
 */
function cart_items($customer_id) {
    return db_select(
        "SELECT ci.id, ci.product_id, ci.quantity, ci.unit_price,
                p.name, p.image, p.stock, p.status,
                b.id AS seller_id, b.shop_name, p.price AS current_price, p.discount_price
         FROM cart c
         JOIN cart_items ci ON ci.cart_id = c.id
         JOIN products p     ON p.id = ci.product_id
         LEFT JOIN businesses b ON b.id = p.seller_id
         WHERE c.customer_id = ?
         ORDER BY ci.created_at DESC",
        'i',
        [$customer_id]
    );
}

/**
 * Generate a unique tracking number for deliveries.
 */
function generate_tracking_number() {
    return 'TRK-' . strtoupper(bin2hex(random_bytes(3))) . '-' . date('Ymd');
}

/**
 * Generate a unique order number like HM-000001.
 */
function generate_order_number() {
    // Race-safe: use the current Unix timestamp + a small random suffix so
    // concurrent checkouts cannot collide on the orders.order_number unique key.
    return 'HM-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
}

/**
 * Friendly status pill color helper.
 */
function status_pill_class($status, $type = 'order') {
    if ($type === 'order') {
        return match($status) {
            'pending'          => 'bg-amber-100 text-amber-700',
            'confirmed'        => 'bg-sky-100 text-sky-700',
            'processing'       => 'bg-indigo-100 text-indigo-700',
            'shipped'          => 'bg-blue-100 text-blue-700',
            'out_for_delivery' => 'bg-purple-100 text-purple-700',
            'delivered'        => 'bg-emerald-100 text-emerald-700',
            'cancelled'        => 'bg-red-100 text-red-700',
            default            => 'bg-gray-100 text-gray-700',
        };
    }
    if ($type === 'custom') {
        return match($status) {
            'pending'     => 'bg-amber-100 text-amber-700',
            'reviewed'    => 'bg-sky-100 text-sky-700',
            'quoted'      => 'bg-blue-100 text-blue-700',
            'accepted'    => 'bg-indigo-100 text-indigo-700',
            'in_progress' => 'bg-purple-100 text-purple-700',
            'completed'   => 'bg-emerald-100 text-emerald-700',
            'rejected'    => 'bg-red-100 text-red-700',
            'cancelled'   => 'bg-red-100 text-red-700',
            default       => 'bg-gray-100 text-gray-700',
        };
    }
    if ($type === 'booking') {
        return match($status) {
            'pending'   => 'bg-amber-100 text-amber-700',
            'accepted'  => 'bg-indigo-100 text-indigo-700',
            'rejected'  => 'bg-red-100 text-red-700',
            'completed' => 'bg-emerald-100 text-emerald-700',
            'cancelled' => 'bg-red-100 text-red-700',
            default     => 'bg-gray-100 text-gray-700',
        };
    }
    if ($type === 'payment') {
        return match($status) {
            'paid'     => 'bg-emerald-100 text-emerald-700',
            'pending'  => 'bg-amber-100 text-amber-700',
            'failed'   => 'bg-red-100 text-red-700',
            'refunded' => 'bg-purple-100 text-purple-700',
            default    => 'bg-gray-100 text-gray-700',
        };
    }
    return 'bg-gray-100 text-gray-700';
}

function format_status_label($status) {
    return h(ucwords(str_replace('_', ' ', (string)$status)));
}

function business_by_id($id) {
    return db_select_one(
        "SELECT b.*, c.name AS category_name
         FROM businesses b
         LEFT JOIN categories c ON c.id = b.category_id
         WHERE b.id = ? LIMIT 1",
        'i',
        [$id]
    );
}

function site_asset_url($path) {
    $script_path = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $script_dir = dirname($script_path);
    if (preg_match('#/(customer|seller|admin)$#', $script_dir)) {
        $script_dir = dirname($script_dir);
    }
    $base = rtrim($script_dir, '/.');
    return ($base === '' ? '' : $base) . '/' . ltrim($path, '/');
}

function product_image_fallback() {
    return site_asset_url('assets/images/hermart-placeholder.svg');
}

function site_image_url($name, $fallback = null) {
    $img = trim((string)$name);
    $fallback = $fallback ?? product_image_fallback();
    if ($img === '') return $fallback;
    if (preg_match('#^https?://#i', $img)) return $img;

    $relative_path = ltrim(str_replace('\\', '/', $img), '/');
    if (strpos($relative_path, '/') === false) {
        $relative_path = 'assets/images/' . $relative_path;
    }

    $root = realpath(dirname(__DIR__));
    $file = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative_path);
    $resolved = realpath($file);
    if (!$resolved || !is_file($resolved) || strpos($resolved, $root . DIRECTORY_SEPARATOR) !== 0) {
        return $fallback;
    }

    return site_asset_url($relative_path);
}

function product_image($name) {
    return site_image_url($name, product_image_fallback());
}

function site_image_attrs($name) {
    return 'src="' . h(site_image_url($name)) . '" data-image-fallback="' . h(product_image_fallback()) . '" onerror="this.onerror=null;this.src=this.dataset.imageFallback"';
}

function product_image_attrs($name) {
    return site_image_attrs($name);
}

function business_logo($name) {
    return site_image_url($name, product_image_fallback());
}

function money_tk($amount) {
    return '৳' . number_format((float)$amount, 0);
}

/**
 * Is this product row purchasable? Combines stock and status so call sites
 * don't have to repeat the (int)$p['stock'] > 0 && $p['status'] === 'active'
 * dance in every template.
 */
function is_in_stock(array $p): bool {
    return (int)($p['stock'] ?? 0) > 0
        && ($p['status'] ?? 'active') === 'active';
}

/**
 * Relative URL to the cart-action endpoint from the currently executing
 * script. Resolves correctly whether the page lives at the site root
 * (e.g. /index.php) or inside /customer/ (e.g. /customer/products.php).
 */
function cart_action_url(): string {
    $script = strtok($_SERVER['SCRIPT_NAME'] ?? '', '?') ?: '';
    // The form's action is interpreted relative to the page URL. Pages
    // inside /customer/ use the sibling path; pages at the site root
    // descend one folder.
    return substr(dirname($script), -9) === '/customer'
        ? 'cart-action.php'
        : 'customer/cart-action.php';
}

/**
 * Render a single product card (image + info + inline Add/Buy actions)
 * using the shared .product-card markup. Accepts rows from any of the
 * product listing queries — alias columns (.brand vs .brand_name) are
 * normalized here so callers don't have to.
 */
function render_product_card(array $p, string $cart_action, string $details_href = 'product-details.php'): void {
    $brand    = $p['brand'] ?? ($p['brand_name'] ?? '');
    $district = $p['district'] ?? '';
    $cat_name = $p['category_name'] ?? '';
    $price    = $p['discount_price'] ?? $p['price'];
    $id       = (int)$p['id'];
    $in       = is_in_stock($p);
    $is_own_product = false;
    if (is_logged_in() && (current_user()['role'] ?? '') === 'seller') {
        static $seller_business_id = null;
        static $seller_business_loaded = false;
        if (!$seller_business_loaded) {
            $business = current_business(current_user()['id']);
            $seller_business_id = (int)($business['id'] ?? 0);
            $seller_business_loaded = true;
        }
        $is_own_product = $seller_business_id > 0 && (int)($p['seller_id'] ?? 0) === $seller_business_id;
    }
    ?>
    <div class="product-card">
      <a href="<?= h($details_href) ?>?id=<?= $id ?>" class="product-card-info">
        <img <?= product_image_attrs($p['image']) ?> alt="<?= h($p['name']) ?>">
        <div class="p-3 flex-1 flex flex-col">
          <div class="text-xs font-semibold text-primary mb-1"><?= h($cat_name) ?></div>
          <h3 class="font-bold text-on-surface text-sm leading-snug line-clamp-2"><?= h($p['name']) ?></h3>
          <p class="text-xs text-on-surface-variant mt-1"><?= h($brand) ?><?= $district !== '' ? ' · ' . h($district) : '' ?></p>
          <div class="mt-2 font-bold text-primary"><?= money_tk($price) ?></div>
                    <?php if ($is_own_product): ?>
                        <p class="text-xs text-on-surface-variant mt-1" data-restock-stock>In stock: <?= (int)($p['stock'] ?? 0) ?></p>
                    <?php endif; ?>
        </div>
      </a>
      <div class="product-card-actions">
        <form method="post" action="<?= h($cart_action) ?>" class="contents" data-cart-add-form>
                    <input type="hidden" name="action" value="<?= $is_own_product ? 'restock' : 'add' ?>">
          <input type="hidden" name="product_id" value="<?= $id ?>">
                    <?php if ($is_own_product): ?>
                        <input type="number" name="quantity" value="1" min="1" max="10000" aria-label="Quantity to add to stock" class="w-14 rounded-lg border border-outline/50 px-2 py-1 text-center text-xs">
                        <button type="submit" class="hm-product-action hm-product-action-secondary hm-product-action-compact">
                            <span class="material-symbols-outlined">add_box</span> Add stock
                        </button>
                    <?php elseif ($in): ?>
                        <input type="hidden" name="quantity" value="1">
            <button type="submit" data-cart-add class="hm-product-action hm-product-action-secondary hm-product-action-compact">
              <span class="material-symbols-outlined">shopping_cart</span> Add
            </button>
            <button type="submit" name="go_cart" value="1" class="hm-product-action hm-product-action-primary hm-product-action-compact">
              <span class="material-symbols-outlined">bolt</span> Buy
            </button>
          <?php else: ?>
            <span class="product-card-stock-badge">Out of stock</span>
          <?php endif; ?>
        </form>
      </div>
    </div>
    <?php
}

function categories_all() {
    return db_select("SELECT id, name, slug, icon FROM categories ORDER BY name ASC");
}

/**
 * List of regions (divisions / districts / upazilas) for the given type.
 * Queries the `locations` table — a `regions` table does not exist in the
 * current schema, so callers that hit `regions_by_type('division')` get
 * the Bangladesh division list.
 */
function regions_by_type($type) {
    return db_select(
        "SELECT MIN(id) AS id, name, MIN(parent_id) AS parent_id
         FROM locations
         WHERE type = ?
         GROUP BY name
         ORDER BY name ASC",
        's',
        [$type]
    );
}

function services_all() {
    return db_select("SELECT id, name, category, base_price, description, status FROM services WHERE status <> 'inactive' ORDER BY id ASC");
}

function safe_int($v, $default = 0) {
    return is_numeric($v) ? (int)$v : $default;
}

function safe_str($v, $max = 255) {
    $s = trim((string)$v);
    if (strlen($s) > $max) $s = substr($s, 0, $max);
    return $s;
}
