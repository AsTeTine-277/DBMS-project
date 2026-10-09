<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/customer-auth.php';

customer_only();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('customer/products.php');
}

$user = current_user();
$product_id = safe_int($_POST['product_id'] ?? 0);
$seller_id  = safe_int($_POST['seller_id'] ?? 0);
$rating     = max(1, min(5, safe_int($_POST['rating'] ?? 5)));
$review     = trim($_POST['review_text'] ?? '');

if ($product_id <= 0 || $review === '') {
    flash_set('error', 'Please fill all required fields.');
    redirect('customer/product-details.php?id=' . $product_id);
}

// Has the user actually purchased this product and the order delivered?
$order = db_select_one(
    "SELECT oi.order_id FROM order_items oi
     JOIN orders o ON o.id = oi.order_id
     WHERE oi.product_id = ? AND o.customer_id = ? AND o.order_status = 'delivered'
     LIMIT 1",
    'ii', [$product_id, $user['id']]
);

if (!$order) {
    flash_set('error', 'You can only review products from delivered orders.');
    redirect('customer/product-details.php?id=' . $product_id);
}

// Already reviewed?
$existing = db_select_one(
    "SELECT id FROM reviews WHERE customer_id = ? AND product_id = ?",
    'ii', [$user['id'], $product_id]
);
if ($existing) {
    flash_set('error', 'You already reviewed this product.');
    redirect('customer/product-details.php?id=' . $product_id);
}

$ok = db_execute(
    "INSERT INTO reviews (customer_id, product_id, seller_id, order_id, rating, review_text)
     VALUES (?, ?, ?, ?, ?, ?)",
    'iiiiis',
    [$user['id'], $product_id, $seller_id, $order['order_id'], $rating, $review]
);

if ($ok) {
    flash_set('success', 'Thanks for your review!');
} else {
    flash_set('error', 'Could not save your review.');
}
redirect('customer/product-details.php?id=' . $product_id);
