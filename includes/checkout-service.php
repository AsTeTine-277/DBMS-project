<?php

function finalize_selected_cart_order(int $customer_id, array $item_ids, array $shipping, string $payment_method, string $payment_reference = ''): array {
    $item_ids = array_values(array_unique(array_filter(array_map('safe_int', $item_ids), static fn($id) => $id > 0)));
    if (!$item_ids) {
        return ['ok' => false, 'error' => 'Select at least one cart item.'];
    }
    if (!in_array($payment_method, ['cod', 'bkash', 'nagad', 'card'], true)) {
        return ['ok' => false, 'error' => 'Choose a valid payment method.'];
    }
    if (!db_transaction_start()) {
        return ['ok' => false, 'error' => 'The order could not be started. Please try again.'];
    }

    $placeholders = implode(',', array_fill(0, count($item_ids), '?'));
    $types = 'i' . str_repeat('i', count($item_ids));
    $params = array_merge([$customer_id], $item_ids);
    $items = db_select(
        "SELECT ci.id AS cart_item_id, ci.product_id, ci.quantity, ci.unit_price,
                p.name, p.seller_id, p.stock, p.status
         FROM cart c
         JOIN cart_items ci ON ci.cart_id = c.id
         JOIN products p ON p.id = ci.product_id
         WHERE c.customer_id = ? AND ci.id IN ($placeholders)
         ORDER BY ci.id FOR UPDATE",
        $types,
        $params
    );

    if (count($items) !== count($item_ids)) {
        db_transaction_rollback();
        return ['ok' => false, 'error' => 'Some selected cart items are no longer available. Please review your cart.'];
    }

    $user = current_user();
    if (($user['role'] ?? '') === 'seller') {
        $business = current_business($customer_id);
        foreach ($items as $item) {
            if ($business && (int)$item['seller_id'] === (int)$business['id']) {
                db_transaction_rollback();
                return ['ok' => false, 'error' => 'You cannot order products from your own store.'];
            }
        }
    }

    $subtotal = 0;
    foreach ($items as $item) {
        if ($item['status'] !== 'active' || (int)$item['stock'] < (int)$item['quantity']) {
            db_transaction_rollback();
            return ['ok' => false, 'error' => 'A selected product is unavailable or has insufficient stock. Please review your cart.'];
        }
        $subtotal += (float)$item['unit_price'] * (int)$item['quantity'];
    }

    $total = $subtotal + 80;
    $order_number = generate_order_number();
    $payment_status = $payment_method === 'cod' ? 'pending' : 'paid';
    $order_status = $payment_method === 'cod' ? 'pending' : 'confirmed';
    $paid_at = $payment_method === 'cod' ? null : date('Y-m-d H:i:s');

    $ok = db_execute(
        "INSERT INTO orders (order_number, customer_id, total_amount, shipping_address, district, upazila, phone, notes, payment_method, payment_status, order_status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        'sidssssssss',
        [
            $order_number, $customer_id, $total, $shipping['address'], $shipping['district'],
            $shipping['upazila'], $shipping['phone'], $shipping['notes'], $payment_method,
            $payment_status, $order_status
        ]
    );
    if (!$ok) {
        db_transaction_rollback();
        return ['ok' => false, 'error' => 'Could not place your order. Please try again.'];
    }

    $order_id = db_insert_id();
    foreach ($items as $item) {
        $quantity = (int)$item['quantity'];
        $unit_price = (float)$item['unit_price'];
        $ok = db_execute(
            "INSERT INTO order_items (order_id, product_id, seller_id, product_name, quantity, unit_price, subtotal)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            'iiisidd',
            [$order_id, $item['product_id'], $item['seller_id'], $item['name'], $quantity, $unit_price, $unit_price * $quantity]
        );
        if (!$ok) {
            db_transaction_rollback();
            return ['ok' => false, 'error' => 'Could not save the order items. Please try again.'];
        }

        $ok = db_execute(
            "UPDATE products
             SET status = CASE WHEN stock <= ? THEN 'out_of_stock' ELSE status END,
                 stock = stock - ?
             WHERE id = ? AND status = 'active' AND stock >= ?",
            'iiii', [$quantity, $quantity, $item['product_id'], $quantity]
        );
        if (!$ok) {
            db_transaction_rollback();
            return ['ok' => false, 'error' => 'Could not update product stock. Please try again.'];
        }
    }

    if ($payment_method !== 'cod' && $payment_reference === '') {
        $payment_reference = strtoupper(substr($payment_method, 0, 3)) . '-' . strtoupper(bin2hex(random_bytes(6)));
    }
    $ok = db_execute(
        "INSERT INTO payments (order_id, payment_method, amount, transaction_reference, payment_status, paid_at)
         VALUES (?, ?, ?, ?, ?, ?)",
        'isdsss', [$order_id, $payment_method, $total, $payment_reference ?: null, $payment_status, $paid_at]
    );
    if (!$ok) {
        db_transaction_rollback();
        return ['ok' => false, 'error' => 'Could not save the payment. Please try again.'];
    }

    $shipping_address = $shipping['address'] . ($shipping['upazila'] ? ', ' . $shipping['upazila'] : '') . ', ' . $shipping['district'];
    $tracking = generate_tracking_number();
    $estimated_delivery = date('Y-m-d', strtotime('+5 days'));
    $ok = db_execute(
        "INSERT INTO deliveries (order_id, customer_id, address, district, upazila, tracking_number, status, estimated_delivery)
         VALUES (?, ?, ?, ?, ?, ?, 'confirmed', ?)",
        'iisssss', [$order_id, $customer_id, $shipping_address, $shipping['district'], $shipping['upazila'], $tracking, $estimated_delivery]
    );
    if (!$ok) {
        db_transaction_rollback();
        return ['ok' => false, 'error' => 'Could not schedule delivery. Please try again.'];
    }

    $ok = db_execute(
        "INSERT INTO notifications (user_id, type, title, body, link) VALUES (?, 'order', ?, ?, ?)",
        'isss', [$customer_id, 'Order ' . $order_number . ' placed', 'Your order has been placed successfully.', 'customer/orders.php']
    );
    if (!$ok) {
        db_transaction_rollback();
        return ['ok' => false, 'error' => 'Could not finish the order. Please try again.'];
    }

    foreach ($items as $item) {
        if (!db_execute("DELETE FROM cart_items WHERE id = ?", 'i', [$item['cart_item_id']])) {
            db_transaction_rollback();
            return ['ok' => false, 'error' => 'Could not update your cart. Please try again.'];
        }
    }

    if (!db_transaction_commit()) {
        db_transaction_rollback();
        return ['ok' => false, 'error' => 'Could not finish the order. Please try again.'];
    }

    return ['ok' => true, 'order_id' => $order_id, 'order_number' => $order_number];
}