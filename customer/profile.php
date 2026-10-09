<?php
/**
 * HerMart — Customer Profile (FIRST 4 PAGES — FULL BACKEND)
 *
 * Read/update profile, view order history (read).
 * Demonstrates SELECT, UPDATE on `users`, SELECT on `orders`.
 */
$page_title = 'My Profile — HerMart';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/customer-auth.php';

customer_only();

$user = current_user();
$error   = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_profile') {
    $name    = trim($_POST['name'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($name === '' || $phone === '') {
        $error = 'Name and phone are required.';
    } elseif (!preg_match('/^[0-9+\-\s]{6,20}$/', $phone)) {
        $error = 'Please enter a valid phone number.';
    } else {
        // Check phone uniqueness for other users
        $conflict = db_select_one(
            "SELECT id FROM users WHERE phone = ? AND id <> ? LIMIT 1",
            'si',
            [$phone, $user['id']]
        );
        if ($conflict) {
            $error = 'That phone number is already in use.';
        } else {
            db_execute(
                "UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?",
                'sssi',
                [$name, $phone, $address, $user['id']]
            );
            $_SESSION['user']['name']    = $name;
            $_SESSION['user']['phone']   = $phone;
            $_SESSION['user']['address'] = $address;
            $success = 'Profile updated successfully.';
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'change_password') {
    $cur = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $con = $_POST['confirm_password'] ?? '';
    $row = db_select_one("SELECT password_hash FROM users WHERE id = ?", 'i', [$user['id']]);
    if (!$row || !password_verify($cur, $row['password_hash'])) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($new) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($new !== $con) {
        $error = 'New passwords do not match.';
    } else {
        db_execute(
            "UPDATE users SET password_hash = ? WHERE id = ?",
            'si',
            [password_hash($new, PASSWORD_DEFAULT), $user['id']]
        );
        $success = 'Password changed successfully.';
    }
}

$user = current_user();
$full = user_by_id($user['id']);
$orders = db_select(
    "SELECT o.id, o.order_number, o.total_amount, o.order_status, o.shipping_address, o.created_at,
            COUNT(oi.id) AS item_count
     FROM orders o
     LEFT JOIN order_items oi ON oi.order_id = o.id
     WHERE o.customer_id = ?
     GROUP BY o.id
     ORDER BY o.created_at DESC
     LIMIT 10",
    'i',
    [$user['id']]
);

$custom_orders = db_select(
    "SELECT * FROM custom_orders WHERE customer_id = ? ORDER BY created_at DESC LIMIT 5",
    'i',
    [$user['id']]
);

include __DIR__ . '/../includes/header.php';
?>

<section class="bg-gradient-to-b from-primary-light/40 to-transparent">
  <div class="max-w-7xl mx-auto px-4 md:px-6 py-10 flex items-center gap-4">
    <div class="w-16 h-16 rounded-full bg-gradient-to-tr from-primary to-pink-400 flex items-center justify-center text-white text-2xl font-bold">
      <?= strtoupper(substr($user['name'], 0, 1)) ?>
    </div>
    <div>
      <p class="text-xs uppercase tracking-widest font-bold text-primary">Customer Account</p>
      <h1 class="font-serif text-3xl font-bold text-on-surface"><?= h($user['name']) ?></h1>
      <p class="text-sm text-on-surface-variant"><?= h($user['email']) ?> · Member since <?= h(date('M Y', strtotime($full['created_at']))) ?></p>
    </div>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 md:px-6 py-10 grid lg:grid-cols-3 gap-6">

  <!-- Sidebar -->
  <aside class="lg:col-span-1 space-y-4">
    <div class="bg-white rounded-3xl border border-outline/40 p-5">
      <nav class="space-y-1">
        <a href="#profile" class="block px-3 py-2 rounded-lg bg-primary-light text-primary font-semibold">Profile Information</a>
        <a href="#orders" class="block px-3 py-2 rounded-lg hover:bg-primary-light text-on-surface">My Orders</a>
        <a href="#custom" class="block px-3 py-2 rounded-lg hover:bg-primary-light text-on-surface">Custom Orders</a>
        <a href="#password" class="block px-3 py-2 rounded-lg hover:bg-primary-light text-on-surface">Change Password</a>
        <a href="../logout.php" class="block px-3 py-2 rounded-lg hover:bg-red-50 text-red-600 font-semibold">Logout</a>
      </nav>
    </div>

    <div class="bg-primary text-white rounded-3xl p-5">
      <h3 class="font-bold mb-3">Quick Links</h3>
      <ul class="space-y-2 text-sm">
        <li><a class="hover:underline flex items-center gap-2" href="home.php"><span class="material-symbols-outlined text-base">storefront</span> Browse Shop</a></li>
        <li><a class="hover:underline flex items-center gap-2" href="custom-order.php"><span class="material-symbols-outlined text-base">auto_awesome</span> Custom Order</a></li>
        <li><a class="hover:underline flex items-center gap-2" href="cart.php"><span class="material-symbols-outlined text-base">shopping_cart</span> My Cart</a></li>
      </ul>
    </div>
  </aside>

  <!-- Main -->
  <div class="lg:col-span-2 space-y-6">

    <?php if ($error): ?>
      <div class="rounded-xl px-4 py-3 text-sm font-medium border bg-red-50 text-red-700 border-red-200"><?= h($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="rounded-xl px-4 py-3 text-sm font-medium border bg-emerald-50 text-emerald-700 border-emerald-200"><?= h($success) ?></div>
    <?php endif; ?>

    <div id="profile" class="bg-white rounded-3xl border border-outline/40 p-6 md:p-8">
      <h2 class="text-xl font-bold mb-4">Profile Information</h2>
      <form method="post" class="grid sm:grid-cols-2 gap-4" data-validate>
        <input type="hidden" name="action" value="update_profile">
        <div>
          <label class="hm-label">Full Name *</label>
          <input name="name" type="text" required class="hm-input" value="<?= h($full['name']) ?>">
        </div>
        <div>
          <label class="hm-label">Phone *</label>
          <input name="phone" type="text" required class="hm-input" value="<?= h($full['phone']) ?>">
        </div>
        <div class="sm:col-span-2">
          <label class="hm-label">Email (read-only)</label>
          <input type="email" class="hm-input bg-surface-dim" value="<?= h($full['email']) ?>" readonly>
        </div>
        <div class="sm:col-span-2">
          <label class="hm-label">Address</label>
          <input name="address" type="text" class="hm-input" value="<?= h($full['address']) ?>" placeholder="Area, District">
        </div>
        <div class="sm:col-span-2 flex justify-end">
          <button class="hm-btn hm-btn-primary" type="submit">
            <span class="material-symbols-outlined text-base">save</span> Save Changes
          </button>
        </div>
      </form>
    </div>

    <div id="password" class="bg-white rounded-3xl border border-outline/40 p-6 md:p-8">
      <h2 class="text-xl font-bold mb-4">Change Password</h2>
      <form method="post" class="grid sm:grid-cols-3 gap-4" data-validate>
        <input type="hidden" name="action" value="change_password">
        <div>
          <label class="hm-label">Current *</label>
          <input name="current_password" type="password" required class="hm-input">
        </div>
        <div>
          <label class="hm-label">New *</label>
          <input name="new_password" type="password" required minlength="6" class="hm-input">
        </div>
        <div>
          <label class="hm-label">Confirm *</label>
          <input name="confirm_password" type="password" required minlength="6" class="hm-input">
        </div>
        <div class="sm:col-span-3 flex justify-end">
          <button class="hm-btn hm-btn-primary" type="submit">
            <span class="material-symbols-outlined text-base">lock_reset</span> Update Password
          </button>
        </div>
      </form>
    </div>

    <div id="orders" class="bg-white rounded-3xl border border-outline/40 p-6 md:p-8">
      <h2 class="text-xl font-bold mb-4">Order History</h2>
      <?php if (empty($orders)): ?>
        <p class="text-sm text-on-surface-variant py-6 text-center">No orders yet. <a href="home.php" class="text-primary font-semibold hover:underline">Start shopping →</a></p>
      <?php else: ?>
        <div class="divide-y divide-outline/40">
          <?php foreach ($orders as $o): ?>
            <a href="order-details.php?id=<?= (int)$o['id'] ?>" class="py-4 flex items-center gap-4 hover:bg-surface-dim rounded-xl px-2 -mx-2 transition-colors">
              <div class="w-10 h-10 rounded-xl bg-primary-light flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-primary">receipt_long</span>
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="font-bold">Order #<?= h($o['order_number']) ?></h3>
                <p class="text-xs text-on-surface-variant">
                  <?= (int)$o['item_count'] ?> item(s) · <?= h(date('M j, Y', strtotime($o['created_at']))) ?> · <?= h($o['shipping_address'] ?? '') ?>
                </p>
              </div>
              <div class="text-right shrink-0">
                <div class="font-bold text-primary"><?= money_tk($o['total_amount']) ?></div>
                <span class="hm-pill <?= status_pill_class($o['order_status'], 'order') ?> mt-1">
                  <?= format_status_label($o['order_status']) ?>
                </span>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
        <div class="mt-3 text-right">
          <a href="orders.php" class="text-sm font-semibold text-primary hover:underline">View all orders →</a>
        </div>
      <?php endif; ?>
    </div>

    <div id="custom" class="bg-white rounded-3xl border border-outline/40 p-6 md:p-8">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold">Recent Custom Orders</h2>
        <a href="custom-order.php" class="text-sm font-semibold text-primary hover:underline">New request →</a>
      </div>
      <?php if (empty($custom_orders)): ?>
        <p class="text-sm text-on-surface-variant py-6 text-center">No custom orders yet.</p>
      <?php else: ?>
        <div class="divide-y divide-outline/40">
          <?php foreach ($custom_orders as $co): ?>
            <div class="py-3 flex items-center gap-3">
              <span class="material-symbols-outlined text-primary">auto_awesome</span>
              <div class="flex-1 min-w-0">
                <h3 class="font-semibold truncate"><?= h($co['title']) ?></h3>
                <p class="text-xs text-on-surface-variant"><?= h(date('M j, Y', strtotime($co['created_at']))) ?> · Qty <?= (int)$co['quantity'] ?> · <?= money_tk($co['budget'] ?? 0) ?></p>
              </div>
              <span class="text-[11px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full bg-surface-dim text-on-surface-variant">
                <?= h(str_replace('_',' ',$co['status'])) ?>
              </span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>