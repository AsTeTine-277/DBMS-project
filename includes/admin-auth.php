<?php


require_once __DIR__ . '/auth.php';

function admin_only() {
    require_login();
    if (($_SESSION['user']['role'] ?? '') !== 'admin') {
        flash_set('error', 'Admins only.');
        redirect('customer/home.php');
    }
}
