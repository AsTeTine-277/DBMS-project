<?php
/**
 * HerMart - Customer-area auth gate.
 *
 * customer_only() protects the customer shopping surfaces. Sellers can use
 * these pages as buyers, while admin accounts remain restricted.
 */

require_once __DIR__ . '/auth.php';

function customer_only() {
    require_login();
    if (!in_array(($_SESSION['user']['role'] ?? ''), ['customer', 'seller'], true)) {
        flash_set('error', 'That area is for customer and seller accounts.');
        redirect('customer/home.php');
    }
}
