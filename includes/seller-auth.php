<?php
/**
 * HerMart - Seller-area auth gate.
 *
 * seller_only() makes sure the current user is logged in AND has role=seller.
 * It also calls current_business() so each seller page can rely on a
 * business row existing (lazily creates one for new sellers).
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

function seller_only() {
    require_login();
    if (($_SESSION['user']['role'] ?? '') !== 'seller') {
        flash_set('error', 'Please sign in with a seller account to continue.');
        redirect('login.php?role=seller');
    }
    // Ensure a `businesses` row exists for this seller so every page can
    // safely reference $biz['shop_name'], etc.
    current_business($_SESSION['user']['id']);
}
