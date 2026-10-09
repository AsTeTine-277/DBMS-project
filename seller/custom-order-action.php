<?php

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/seller-auth.php';

seller_only();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('seller/custom-orders.php');
}

$user = current_user();
$biz  = current_business($user['id']);
$biz_id = (int)$biz['id'];

$id     = safe_int($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

$co = db_select_one(
    "SELECT * FROM custom_orders WHERE id = ? AND seller_id = ?",
    'ii', [$id, $biz_id]
);

if (!$co) {
    flash_set('error', 'Custom order not found.');
    redirect('seller/custom-orders.php');
}

switch ($action) {
    case 'review':
        db_execute(
            "UPDATE custom_orders SET status = 'reviewed' WHERE id = ?",
            'i', [$id]
        );
        $msg = 'Marked as reviewed.';
        break;

    case 'quote':
        $price = (float)($_POST['quoted_price'] ?? 0);
        $eta   = trim($_POST['estimated_completion'] ?? '');
        if ($price <= 0) {
            flash_set('error', 'Quote price must be greater than zero.');
            redirect('seller/custom-orders.php');
        }
        db_execute(
            "UPDATE custom_orders
             SET quoted_price = ?, estimated_completion = ?, status = 'quoted'
             WHERE id = ?",
            'dsi', [$price, $eta !== '' ? $eta : null, $id]
        );
        $msg = 'Quote sent.';
        break;

    case 'accept':
        if ($co['status'] !== 'quoted') {
            flash_set('error', 'You can only accept a quoted request.');
            redirect('seller/custom-orders.php');
        }
        db_execute(
            "UPDATE custom_orders SET status = 'accepted' WHERE id = ?",
            'i', [$id]
        );
        $msg = 'Request accepted.';
        break;

    case 'start':
        if ($co['status'] !== 'accepted') {
            flash_set('error', 'You can only start an accepted request.');
            redirect('seller/custom-orders.php');
        }
        db_execute(
            "UPDATE custom_orders SET status = 'in_progress' WHERE id = ?",
            'i', [$id]
        );
        $msg = 'Marked as in progress.';
        break;

    case 'complete':
        if (!in_array($co['status'], ['accepted','in_progress'], true)) {
            flash_set('error', 'Cannot complete from current status.');
            redirect('seller/custom-orders.php');
        }
        db_execute(
            "UPDATE custom_orders SET status = 'completed' WHERE id = ?",
            'i', [$id]
        );
        $msg = 'Marked as completed.';
        break;

    case 'reject':
        if (in_array($co['status'], ['completed','cancelled'], true)) {
            flash_set('error', 'Cannot reject from current status.');
            redirect('seller/custom-orders.php');
        }
        db_execute(
            "UPDATE custom_orders SET status = 'rejected' WHERE id = ?",
            'i', [$id]
        );
        $msg = 'Request rejected.';
        break;

    case 'reopen':
        db_execute(
            "UPDATE custom_orders SET status = 'pending' WHERE id = ?",
            'i', [$id]
        );
        $msg = 'Re-opened as pending.';
        break;

    default:
        flash_set('error', 'Unknown action.');
        redirect('seller/custom-orders.php');
}


if (!empty($co['customer_id'])) {
    db_execute(
        "INSERT INTO notifications (user_id, type, title, body, link)
         VALUES (?, 'custom_order', ?, ?, ?)",
        'ssss',
        [
            (int)$co['customer_id'],
            'Custom order update',
            'Your custom order "' . $co['title'] . '" was updated: ' . $msg,
            'customer/home.php'
        ]
    );
}

flash_set('success', $msg . ' Customer has been notified.');
redirect('seller/custom-orders.php');
