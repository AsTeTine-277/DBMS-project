<?php

$page_title = 'Delete Product — HerMart';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/seller-auth.php';

seller_only();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('seller/products.php');
}

$user = current_user();
$biz  = current_business($user['id']);
$biz_id = (int)$biz['id'];

$product_id = safe_int($_POST['product_id'] ?? 0);

if ($product_id <= 0) {
    flash_set('error', 'Invalid product id.');
    redirect('seller/products.php');
}


$product = db_select_one(
    "SELECT id, seller_id, image FROM products WHERE id = ?",
    'i', [$product_id]
);

if (!$product || (int)$product['seller_id'] !== $biz_id) {
    flash_set('error', 'You are not allowed to delete this product.');
    redirect('seller/products.php');
}


$images = db_select(
    "SELECT image_path FROM product_images WHERE product_id = ?",
    'i', [$product_id]
);

$ok = db_execute("DELETE FROM products WHERE id = ?", 'i', [$product_id]);

if ($ok) {
    
    $projectRoot = realpath(__DIR__ . '/../');
    foreach ($images as $img) {
        $rel = $img['image_path'] ?? '';
        if ($rel === '') continue;
        $abs = $projectRoot . '/' . ltrim($rel, '/');
        if (is_file($abs)) {
            @unlink($abs);
        }
    }
    
    if (!empty($product['image'])) {
        $abs = $projectRoot . '/' . ltrim($product['image'], '/');
        if (is_file($abs)) {
            @unlink($abs);
        }
    }
    flash_set('success', 'Product removed.');
} else {
    flash_set('error', 'Could not delete the product. Please try again.');
}

redirect('seller/products.php');
