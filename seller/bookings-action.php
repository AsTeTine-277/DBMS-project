<?php

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/seller-auth.php';

seller_only();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('seller/bookings.php');
}

$user = current_user();
$biz  = current_business($user['id']);
$biz_id = (int)$biz['id'];

$booking_id = safe_int($_POST['booking_id'] ?? 0);
$action     = $_POST['action'] ?? '';
$redirect   = $_POST['redirect_to'] ?? 'seller/bookings.php';

$booking = db_select_one(
    "SELECT id, vendor_id, customer_id, event_type, status FROM bookings WHERE id = ?",
    'i', [$booking_id]
);

if (!$booking || (int)$booking['vendor_id'] !== $biz_id) {
    flash_set('error', 'Booking not found.');
    redirect($redirect);
}

$map = [
    'accept'   => 'accepted',
    'reject'   => 'rejected',
    'complete' => 'completed',
    'cancel'   => 'cancelled',
    'reopen'   => 'pending',
];

if (!isset($map[$action])) {
    flash_set('error', 'Unknown action.');
    redirect($redirect);
}

$newStatus = $map[$action];
$ok = db_execute(
    "UPDATE bookings SET status = ? WHERE id = ?",
    'si', [$newStatus, $booking_id]
);

if ($ok && !empty($booking['customer_id'])) {
    $titles = [
        'accept'   => 'Booking accepted',
        'reject'   => 'Booking rejected',
        'complete' => 'Booking completed',
        'cancel'   => 'Booking cancelled',
        'reopen'   => 'Booking reopened',
    ];
    db_execute(
        "INSERT INTO notifications (user_id, type, title, body, link)
         VALUES (?, 'booking', ?, ?, ?)",
        'ssss',
        [
            (int)$booking['customer_id'],
            ($titles[$action] ?? 'Booking update') . ': ' . ($booking['event_type'] ?? ''),
            'Your booking is now "' . format_status_label($newStatus) . '".',
            'customer/home.php'
        ]
    );
    flash_set('success', 'Booking ' . $newStatus . '.');
} else {
    flash_set('error', 'Could not update booking.');
}

redirect($redirect);
