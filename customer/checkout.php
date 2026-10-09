<?php
$page_title = 'Checkout - HerMart';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/customer-auth.php';
require_once __DIR__ . '/../includes/checkout-service.php';

customer_only();
$user = current_user();
$checkout_key = 'checkout_selection_' . (int)$user['id'];
$error = '';
$step = 'delivery';
$shipping = $_SESSION[$checkout_key]['shipping'] ?? null;
$payment_method = $_SESSION[$checkout_key]['payment_method'] ?? 'cod';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_checkout'])) {
    $selected_ids = array_values(array_unique(array_filter(array_map('safe_int', $_POST['selected_item_ids'] ?? []), static fn($id) => $id > 0)));
    if (!$selected_ids) {
        flash_set('error', 'Select at least one item to check out.');
        redirect('customer/cart.php');
    }
    $_SESSION[$checkout_key] = ['item_ids' => $selected_ids];
    $shipping = null;
    $payment_method = 'cod';
}

$checkout = $_SESSION[$checkout_key] ?? null;
if (!$checkout || empty($checkout['item_ids'])) {
    flash_set('error', 'Choose the cart items you want to check out.');
    redirect('customer/cart.php');
}
$item_ids = $checkout['item_ids'];
$placeholders = implode(',', array_fill(0, count($item_ids), '?'));
$item_types = 'i' . str_repeat('i', count($item_ids));
$items = db_select(
    "SELECT ci.id, ci.product_id, ci.quantity, ci.unit_price, p.name, p.image, p.stock, p.status,
            p.seller_id, b.shop_name
     FROM cart c
     JOIN cart_items ci ON ci.cart_id = c.id
     JOIN products p ON p.id = ci.product_id
     LEFT JOIN businesses b ON b.id = p.seller_id
     WHERE c.customer_id = ? AND ci.id IN ($placeholders)
     ORDER BY ci.id",
    $item_types,
    array_merge([(int)$user['id']], $item_ids)
);
if (count($items) !== count($item_ids)) {
    unset($_SESSION[$checkout_key]);
    flash_set('error', 'Some selected cart items are no longer available. Please select your items again.');
    redirect('customer/cart.php');
}

if (($user['role'] ?? '') === 'seller') {
    $business = current_business($user['id']);
    foreach ($items as $item) {
        if ($business && (int)$item['seller_id'] === (int)$business['id']) {
            unset($_SESSION[$checkout_key]);
            flash_set('error', 'Remove products from your own store before checkout.');
            redirect('customer/cart.php');
        }
    }
}

$subtotal = 0;
foreach ($items as $item) {
    $subtotal += (float)$item['unit_price'] * (int)$item['quantity'];
}
$delivery = 80;
$total = $subtotal + $delivery;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_delivery'])) {
    $shipping = [
        'name' => trim($_POST['name'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'district' => trim($_POST['district'] ?? ''),
        'upazila' => trim($_POST['upazila'] ?? ''),
        'notes' => trim($_POST['notes'] ?? ''),
    ];
    $payment_method = $_POST['payment_method'] ?? 'cod';
    if (!in_array($payment_method, ['cod', 'bkash', 'nagad', 'card'], true)) {
        $payment_method = 'cod';
    }

    if ($shipping['name'] === '' || $shipping['phone'] === '' || $shipping['address'] === '' || $shipping['district'] === '') {
        $error = 'Please fill all required delivery details.';
    } elseif (!preg_match('/^[0-9+\-\s]{6,20}$/', $shipping['phone'])) {
        $error = 'Please enter a valid phone number.';
    } else {
        $_SESSION[$checkout_key]['shipping'] = $shipping;
        $_SESSION[$checkout_key]['payment_method'] = $payment_method;
        if ($payment_method === 'cod') {
            $result = finalize_selected_cart_order((int)$user['id'], $item_ids, $shipping, 'cod');
            if ($result['ok']) {
                unset($_SESSION[$checkout_key]);
                flash_set('success', 'Order #' . $result['order_number'] . ' placed successfully!');
                redirect('customer/order-details.php?id=' . (int)$result['order_id']);
            }
            $error = $result['error'];
        } else {
            $step = 'payment';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    $shipping = $_SESSION[$checkout_key]['shipping'] ?? null;
    $payment_method = $_SESSION[$checkout_key]['payment_method'] ?? '';
    if (!$shipping || !in_array($payment_method, ['bkash', 'nagad', 'card'], true)) {
        $error = 'Your checkout session expired. Please enter delivery details again.';
        $step = 'delivery';
    } else {
    $step = 'payment';
        $payment_reference = '';
      $payment_valid = true;
        if ($payment_method === 'bkash' || $payment_method === 'nagad') {
            $wallet_number = trim($_POST['wallet_number'] ?? '');
            $wallet_pin = trim($_POST['wallet_pin'] ?? '');
            if (!preg_match('/^[0-9+\-\s]{8,20}$/', $wallet_number) || !preg_match('/^[0-9]{4,6}$/', $wallet_pin)) {
                $error = 'Enter a valid mobile wallet number and 4 to 6 digit PIN to complete demo payment.';
                $payment_valid = false;
            } else {
                $payment_reference = strtoupper(substr($payment_method, 0, 3)) . '-' . preg_replace('/\D/', '', $wallet_number) . '-' . strtoupper(bin2hex(random_bytes(3)));
            }
        } elseif ($payment_method === 'card') {
            $card_number = preg_replace('/\D/', '', $_POST['card_number'] ?? '');
            $cardholder = trim($_POST['cardholder'] ?? '');
            $expiry = trim($_POST['expiry'] ?? '');
            $cvv = trim($_POST['cvv'] ?? '');
            if (!preg_match('/^[0-9]{12,19}$/', $card_number)
                || $cardholder === ''
                || !preg_match('/^(0[1-9]|1[0-2])\/[0-9]{2}$/', $expiry)
                || !preg_match('/^[0-9]{3,4}$/', $cvv)) {
                $error = 'Enter valid card details to complete demo payment.';
                $payment_valid = false;
            } else {
                $payment_reference = 'CARD-' . substr($card_number, -4) . '-' . strtoupper(bin2hex(random_bytes(3)));
            }
        }

        if ($payment_valid) {
            $result = finalize_selected_cart_order((int)$user['id'], $item_ids, $shipping, $payment_method, $payment_reference);
            if ($result['ok']) {
                unset($_SESSION[$checkout_key]);
                flash_set('success', 'Payment completed and order #' . $result['order_number'] . ' confirmed.');
                redirect('customer/order-details.php?id=' . (int)$result['order_id']);
            }
            $error = $result['error'];
        }
    }
}

$userRow = user_by_id($user['id']);
include __DIR__ . '/../includes/header.php';
?>

<section class="bg-gradient-to-b from-primary-light/40 to-transparent">
  <div class="max-w-7xl mx-auto px-4 md:px-6 py-10">
    <div class="mb-3">
      <a href="cart.php" class="hm-btn-back" aria-label="Back to cart"><span class="material-symbols-outlined">arrow_back</span>Back to cart</a>
    </div>
    <p class="text-xs uppercase tracking-widest font-bold text-primary"><?= $step === 'payment' ? 'Payment' : 'Checkout' ?></p>
    <h1 class="font-serif text-3xl md:text-4xl font-bold mt-2"><?= $step === 'payment' ? 'Complete your payment' : 'Delivery details' ?></h1>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 md:px-6 pb-12 grid lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2 bg-white rounded-3xl border border-outline/40 p-6 md:p-8">
    <?php if ($error): ?>
      <div class="rounded-xl px-4 py-3 text-sm font-medium border bg-red-50 text-red-700 border-red-200 mb-4"><?= h($error) ?></div>
    <?php endif; ?>

    <?php if ($step === 'delivery'): ?>
      <form method="post" class="space-y-4" data-validate>
        <h2 class="font-bold text-lg">Delivery details</h2>
        <div class="grid sm:grid-cols-2 gap-4">
          <div><label class="hm-label">Full Name *</label><input class="hm-input" name="name" required value="<?= h($shipping['name'] ?? $userRow['name']) ?>"></div>
          <div><label class="hm-label">Phone *</label><input class="hm-input" name="phone" required value="<?= h($shipping['phone'] ?? $userRow['phone']) ?>"></div>
        </div>
        <div><label class="hm-label">Delivery Address *</label><textarea class="hm-textarea" name="address" rows="2" required placeholder="House, road, area"><?= h($shipping['address'] ?? $userRow['address']) ?></textarea></div>
        <div class="grid sm:grid-cols-2 gap-4">
          <div><label class="hm-label">District *</label><input class="hm-input" name="district" required value="<?= h($shipping['district'] ?? '') ?>" placeholder="e.g. Dhaka"></div>
          <div><label class="hm-label">Upazila</label><input class="hm-input" name="upazila" value="<?= h($shipping['upazila'] ?? '') ?>" placeholder="e.g. Banani"></div>
        </div>
        <div><label class="hm-label">Notes (optional)</label><textarea class="hm-textarea" name="notes" rows="2" placeholder="Delivery instructions..."><?= h($shipping['notes'] ?? '') ?></textarea></div>
        <h2 class="font-bold text-lg pt-3">Payment method</h2>
        <div class="space-y-2">
          <?php foreach ([
              'cod' => ['Cash on Delivery', 'Pay when your order arrives.'],
              'bkash' => ['bKash', 'Continue to mobile wallet demo payment.'],
              'nagad' => ['Nagad', 'Continue to mobile wallet demo payment.'],
              'card' => ['Credit / Debit Card', 'Continue to secure card demo payment.'],
          ] as $method => $details): ?>
            <label class="flex items-center gap-3 p-3 border border-outline/40 rounded-xl cursor-pointer hover:border-primary">
              <input type="radio" name="payment_method" value="<?= h($method) ?>" <?= ($payment_method === $method) ? 'checked' : '' ?>>
              <span><span class="block font-semibold"><?= h($details[0]) ?></span><span class="block text-xs text-on-surface-variant"><?= h($details[1]) ?></span></span>
            </label>
          <?php endforeach; ?>
        </div>
        <button type="submit" name="save_delivery" value="1" class="hm-btn hm-btn-primary w-full justify-center mt-3">
          <span class="material-symbols-outlined text-base">lock</span>
          Continue · <?= money_tk($total) ?>
        </button>
      </form>
    <?php else: ?>
      <form method="post" class="space-y-4" data-validate>
        <h2 class="font-bold text-lg"><?= $payment_method === 'card' ? 'Card payment' : h(ucfirst($payment_method)) . ' payment' ?></h2>
        <p class="text-sm text-on-surface-variant">Demo checkout: no actual charge is made. Card or wallet credentials are not saved.</p>
        <?php if ($payment_method === 'bkash' || $payment_method === 'nagad'): ?>
          <div><label class="hm-label">Mobile wallet number *</label><input class="hm-input" name="wallet_number" type="tel" inputmode="tel" autocomplete="off" required placeholder="01XXXXXXXXX"></div>
          <div><label class="hm-label">Demo PIN *</label><input class="hm-input" name="wallet_pin" type="password" inputmode="numeric" autocomplete="new-password" required minlength="4" maxlength="6" placeholder="4 to 6 digits"></div>
        <?php else: ?>
          <div><label class="hm-label">Name on card *</label><input class="hm-input" name="cardholder" autocomplete="cc-name" required></div>
          <div><label class="hm-label">Card number *</label><input class="hm-input" name="card_number" inputmode="numeric" autocomplete="cc-number" required minlength="12" maxlength="23" placeholder="0000 0000 0000 0000"></div>
          <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="hm-label">Expiry (MM/YY) *</label><input class="hm-input" name="expiry" autocomplete="cc-exp" required placeholder="MM/YY"></div>
            <div><label class="hm-label">CVV *</label><input class="hm-input" name="cvv" type="password" inputmode="numeric" autocomplete="cc-csc" required minlength="3" maxlength="4"></div>
          </div>
        <?php endif; ?>
        <div class="flex flex-wrap gap-3 pt-2">
          <a href="checkout.php" class="hm-btn hm-btn-outline">Back</a>
          <button type="submit" name="confirm_payment" value="1" class="hm-btn hm-btn-primary flex-1 justify-center">
            <span class="material-symbols-outlined text-base">verified</span>
            Pay <?= money_tk($total) ?>
          </button>
        </div>
      </form>
    <?php endif; ?>
  </div>

  <aside class="bg-white rounded-3xl border border-outline/40 p-6 sticky top-24 h-fit">
    <h2 class="font-bold text-lg mb-3">Selected items</h2>
    <div class="divide-y divide-outline/40 mb-3">
      <?php foreach ($items as $item): ?>
        <div class="py-2 flex items-center gap-3">
          <img <?= product_image_attrs($item['image']) ?> class="w-10 h-10 rounded-lg object-cover" alt="">
          <div class="flex-1 min-w-0"><div class="text-sm font-semibold truncate"><?= h($item['name']) ?></div><div class="text-xs text-on-surface-variant">Qty <?= (int)$item['quantity'] ?></div></div>
          <div class="text-sm font-bold text-primary"><?= money_tk((float)$item['unit_price'] * (int)$item['quantity']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="space-y-2 text-sm">
      <div class="flex justify-between"><span class="text-on-surface-variant">Subtotal</span><span><?= money_tk($subtotal) ?></span></div>
      <div class="flex justify-between"><span class="text-on-surface-variant">Delivery</span><span><?= money_tk($delivery) ?></span></div>
      <div class="flex justify-between font-bold text-lg border-t border-outline/40 pt-3 mt-3"><span>Total</span><span class="text-primary"><?= money_tk($total) ?></span></div>
    </div>
  </aside>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
