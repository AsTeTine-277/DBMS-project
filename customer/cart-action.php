<?php
/**
 * HerMart — Cart and inventory actions (restock / add / update / remove / clear)
 *
 * POST endpoint used by product listings, product details,
 * and the cart page. Returns to the referring page on success.
 */
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/customer-auth.php';

customer_only();

$user = current_user();
$action = $_POST['action'] ?? '';

function cart_action_finish(bool $success, string $message, int $cart_id = 0, string $fallback = 'customer/products.php', array $extra = []): void {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $is_ajax = strpos($accept, 'application/json') !== false
        || ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

    if ($is_ajax) {
        $item_count = 0;
        if ($success && $cart_id > 0) {
            $row = db_select_one("SELECT COALESCE(SUM(quantity), 0) AS item_count FROM cart_items WHERE cart_id = ?", 'i', [$cart_id]);
            $item_count = (int)($row['item_count'] ?? 0);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_merge(['success' => $success, 'message' => $message, 'cart_count' => $item_count], $extra));
        exit;
    }

    flash_set($success ? 'success' : 'error', $message);
    redirect($_SERVER['HTTP_REFERER'] ?? $fallback);
}

if ($action === 'restock') {
    if (($user['role'] ?? '') !== 'seller') {
        cart_action_finish(false, 'Only sellers can add stock to their products.');
    }

    $product_id = safe_int($_POST['product_id'] ?? 0);
    $quantity = safe_int($_POST['quantity'] ?? 0);
    if ($product_id <= 0 || $quantity < 1 || $quantity > 10000) {
        cart_action_finish(false, 'Enter a valid stock quantity.');
    }

    $business = current_business($user['id']);
    $product = $business ? db_select_one(
        "SELECT id, name, stock FROM products WHERE id = ? AND seller_id = ?",
        'ii', [$product_id, $business['id']]
    ) : null;
    if (!$product) {
        cart_action_finish(false, 'You can only add stock to your own products.');
    }

    $ok = db_execute(
        "UPDATE products
         SET stock = LEAST(2147483647, stock + ?),
             status = CASE WHEN status = 'out_of_stock' THEN 'active' ELSE status END
         WHERE id = ? AND seller_id = ?",
        'iii', [$quantity, $product_id, $business['id']]
    );
    if (!$ok) {
        cart_action_finish(false, 'Could not update stock. Please try again.');
    }
    $updated_product = db_select_one("SELECT stock FROM products WHERE id = ? AND seller_id = ?", 'ii', [$product_id, $business['id']]);
    cart_action_finish(
        true,
        'Added ' . $quantity . ' to stock for "' . $product['name'] . '".',
        0,
        'customer/products.php',
        ['stock' => (int)($updated_product['stock'] ?? ((int)$product['stock'] + $quantity))]
    );
}

// Ensure we have a cart row for this customer; try to create if missing.
$cart = current_cart($user['id']);
if (!$cart || !isset($cart['id'])) {
    // Attempt to create again and re-fetch.
    $created = db_execute("INSERT INTO cart (customer_id) VALUES (?)", 'i', [$user['id']]);
    if (!$created) {
        flash_set('error', 'Unable to access cart: ' . db_error());
        redirect($_SERVER['HTTP_REFERER'] ?? 'customer/products.php');
    }
    $cart = current_cart($user['id']);
    if (!$cart || !isset($cart['id'])) {
        flash_set('error', 'Unable to create cart. Please try again later.');
        redirect($_SERVER['HTTP_REFERER'] ?? 'customer/products.php');
    }
}

switch ($action) {
    case 'add': {
        $product_id = safe_int($_POST['product_id'] ?? 0);
        $quantity   = max(1, safe_int($_POST['quantity'] ?? 1));
        if ($product_id <= 0) {
            cart_action_finish(false, 'Invalid product.', (int)$cart['id']);
        }
        $product = db_select_one("SELECT id, name, seller_id, price, discount_price, stock, status FROM products WHERE id = ?", 'i', [$product_id]);
        if (!$product) {
            cart_action_finish(false, 'Product not found.', (int)$cart['id']);
        }
        if (($user['role'] ?? '') === 'seller') {
            $business = current_business($user['id']);
            if ($business && (int)$product['seller_id'] === (int)$business['id']) {
                cart_action_finish(false, 'You cannot buy products from your own store.', (int)$cart['id']);
            }
        }
        if ($product['status'] !== 'active') {
            cart_action_finish(false, 'This product is not available for purchase.', (int)$cart['id']);
        }
        if ((int)$product['stock'] < 1) {
            cart_action_finish(false, 'Sorry, this product is out of stock.', (int)$cart['id']);
        }
        // Decide unit price: use discount_price if set, else price.
        $unitPrice = $product['discount_price'] !== null ? (float)$product['discount_price'] : (float)$product['price'];

        // Check existing line.
        $existing = db_select_one(
            "SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?",
            'ii', [$cart['id'], $product_id]
        );
        if ($existing) {
            $newQty = min((int)$product['stock'], (int)$existing['quantity'] + $quantity);
            $ok = db_execute(
                "UPDATE cart_items SET quantity = ?, unit_price = ? WHERE id = ?",
                'idi', [$newQty, $unitPrice, $existing['id']]
            );
            if (!$ok) {
                cart_action_finish(false, 'Could not update cart: ' . db_error(), (int)$cart['id']);
            }
        } else {
            $newQty = min((int)$product['stock'], $quantity);
            $ok = db_execute(
                "INSERT INTO cart_items (cart_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)",
                'iiid', [$cart['id'], $product_id, $newQty, $unitPrice]
            );
            if (!$ok) {
                cart_action_finish(false, 'Could not add to cart: ' . db_error(), (int)$cart['id']);
            }
        }
        // Redirect to cart if user clicked "Add to Cart" — otherwise go back.
        $to = $_POST['redirect_to'] ?? '';
        if ($to === 'cart' || isset($_POST['go_cart'])) {
            flash_set('success', 'Added "' . $product['name'] . '" to your cart.');
            redirect('customer/cart.php');
        }
        cart_action_finish(true, 'Added "' . $product['name'] . '" to your cart.', (int)$cart['id']);
        break;
    }

    case 'update': {
        $item_id  = safe_int($_POST['item_id'] ?? 0);
        $quantity = max(1, safe_int($_POST['quantity'] ?? 1));
        $item = db_select_one(
            "SELECT ci.id, ci.cart_id, p.stock, p.status
             FROM cart_items ci
             JOIN cart c ON c.id = ci.cart_id
             JOIN products p ON p.id = ci.product_id
             WHERE ci.id = ? AND c.customer_id = ?",
            'ii', [$item_id, $user['id']]
        );
        if (!$item) {
            flash_set('error', 'Cart item not found.');
            redirect('customer/cart.php');
        }
        if ($item['status'] !== 'active') {
            flash_set('error', 'This product is currently unavailable.');
            redirect('customer/cart.php');
        }
        $newQty = min($quantity, max(1, (int)$item['stock']));
        db_execute(
            "UPDATE cart_items SET quantity = ? WHERE id = ? AND cart_id = ?",
            'iii', [$newQty, $item_id, $cart['id']]
        );
        flash_set('success', 'Cart updated.');
        redirect('customer/cart.php');
        break;
    }

    case 'remove': {
        $item_id = safe_int($_POST['item_id'] ?? 0);
        db_execute(
            "DELETE ci FROM cart_items ci
             JOIN cart c ON c.id = ci.cart_id
             WHERE ci.id = ? AND c.customer_id = ?",
            'ii', [$item_id, $user['id']]
        );
        flash_set('success', 'Item removed from cart.');
        redirect('customer/cart.php');
        break;
    }

    case 'clear': {
        db_execute("DELETE FROM cart_items WHERE cart_id = ?", 'i', [$cart['id']]);
        flash_set('success', 'Cart cleared.');
        redirect('customer/cart.php');
        break;
    }

    default:
        flash_set('error', 'Unknown cart action.');
        redirect('customer/cart.php');
}
