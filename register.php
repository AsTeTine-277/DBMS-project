<?php

$page_title = 'Create Account — HerMart';
$page_desc  = 'Join HerMart as a customer or artisan seller.';
require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) {
    $u = current_user();
    if ($u['role'] === 'seller') redirect('seller/dashboard.php');
    if ($u['role'] === 'admin')  redirect('admin/dashboard.php');
    redirect('customer/home.php');
}

$error   = '';
$success = '';
$role    = $_GET['role'] ?? 'customer';
if (!in_array($role, ['customer', 'seller'], true)) {
    $role = 'customer';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $phone    = preg_replace('/[^0-9+]/', '', trim($_POST['phone'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';
    $address  = trim($_POST['address'] ?? '');
    $role     = $_POST['role'] ?? 'customer';

    if (!in_array($role, ['customer', 'seller'], true)) {
        $role = 'customer';
    }

    if ($name === '' || $email === '' || $phone === '' || $password === '') {
        $error = 'Please fill all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!preg_match('/^\+?[0-9]{6,20}$/', $phone)) {
        $error = 'Please enter a valid phone number.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        
        $exists = db_select_one(
            "SELECT id FROM users WHERE LOWER(email) = LOWER(?) OR phone = ? LIMIT 1",
            'ss',
            [$email, $phone]
        );
        if ($exists) {
            $error = 'An account with that email or phone already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
          $registrationStarted = db_transaction_start();
            $ok = db_execute(
                "INSERT INTO users (name, email, phone, password_hash, role, address) VALUES (?, ?, ?, ?, ?, ?)",
                'ssssss',
                [$name, $email, $phone, $hash, $role, $address]
            );
            if ($ok) {
                $newUserId = db_insert_id();
                
                if ($role === 'seller') {
                $businessOk = db_execute(
                        "INSERT INTO businesses (owner_id, shop_name, phone, address) VALUES (?, ?, ?, ?)",
                        'isss',
                        [$newUserId, $name . "'s Shop", $phone, $address]
                    );
                if (!$businessOk) {
                  $error = 'Could not create your seller profile. Please try again.';
                }
              }
              if ($error === '') {
                if ($registrationStarted) db_transaction_commit();
                flash_set('success', 'Account created! Please sign in to continue.');
                redirect('login.php?role=' . $role);
                }
            } else {
                $error = 'Could not create your account. Please try again.';
            }
            if ($error !== '' && $registrationStarted) db_transaction_rollback();
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="max-w-3xl mx-auto px-4 md:px-6 py-10 md:py-16">
  <div class="bg-white rounded-3xl shadow-sm-custom border border-outline/40 p-6 md:p-10">

    <h1 class="font-serif text-3xl font-bold text-on-surface mb-2">Create your HerMart account</h1>
    <p class="text-sm text-on-surface-variant mb-6">
      Join thousands of customers and women entrepreneurs across Bangladesh.
    </p>

    <!-- Role cards -->
    <div class="mb-6">
      <p class="text-xs font-bold tracking-wider text-primary uppercase mb-2">I want to register as</p>
      <div class="grid grid-cols-2 gap-3">
        <form method="get" action="register.php" class="m-0">
          <input type="hidden" name="role" value="customer">
          <button type="submit" data-role-card class="hm-role-card w-full <?= $role === 'customer' ? 'hm-role-card-active' : '' ?>">
            <span class="material-symbols-outlined">shopping_bag</span>
            <div class="text-sm">Customer</div>
            <div class="text-[11px] font-medium opacity-80">I want to buy</div>
          </button>
        </form>
        <form method="get" action="register.php" class="m-0">
          <input type="hidden" name="role" value="seller">
          <button type="submit" data-role-card class="hm-role-card w-full <?= $role === 'seller' ? 'hm-role-card-active' : '' ?>">
            <span class="material-symbols-outlined">storefront</span>
            <div class="text-sm">Artisan / Seller</div>
            <div class="text-[11px] font-medium opacity-80">I want to sell</div>
          </button>
        </form>
      </div>
    </div>

    <h2 class="text-xl font-bold text-on-surface mb-1">
      Register as <?= $role === 'seller' ? 'Artisan / Seller' : 'Customer' ?>
    </h2>
    <p class="text-sm text-on-surface-variant mb-5">
      <?= $role === 'seller'
          ? 'Set up your shop and start selling your handmade creations.'
          : 'Create an account to shop, request custom orders and book services.' ?>
    </p>

    <?php if ($error): ?>
      <div class="mb-4 rounded-xl px-4 py-3 text-sm font-medium border bg-red-50 text-red-700 border-red-200">
        <?= h($error) ?>
      </div>
    <?php endif; ?>

    <form method="post" class="space-y-4" data-validate>
      <input type="hidden" name="role" value="<?= h($role) ?>">

      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="hm-label" for="name">Full Name *</label>
          <input id="name" name="name" type="text" required class="hm-input" value="<?= h($_POST['name'] ?? '') ?>">
        </div>
        <div>
          <label class="hm-label" for="phone">Phone *</label>
          <input id="phone" name="phone" type="text" required class="hm-input" placeholder="017xxxxxxxx" value="<?= h($_POST['phone'] ?? '') ?>">
        </div>
      </div>

      <div>
        <label class="hm-label" for="email">Email *</label>
        <input id="email" name="email" type="email" required class="hm-input" value="<?= h($_POST['email'] ?? '') ?>">
      </div>

      <div>
        <label class="hm-label" for="address">Address</label>
        <input id="address" name="address" type="text" class="hm-input" placeholder="Area, District" value="<?= h($_POST['address'] ?? '') ?>">
      </div>

      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="hm-label" for="password">Password *</label>
          <input id="password" name="password" type="password" required minlength="6" class="hm-input" placeholder="At least 6 characters">
        </div>
        <div>
          <label class="hm-label" for="confirm">Confirm Password *</label>
          <input id="confirm" name="confirm" type="password" required minlength="6" class="hm-input" placeholder="Re-enter password">
        </div>
      </div>

      <button type="submit" class="hm-btn hm-btn-primary w-full justify-center">
        <span class="material-symbols-outlined text-base">person_add</span>
        Create <?= $role === 'seller' ? 'Seller' : 'Customer' ?> Account
      </button>

      <p class="text-xs text-center text-on-surface-variant mt-3">
        Already have an account?
        <a href="login.php?role=<?= h($role) ?>" class="text-primary font-bold hover:underline">Sign in</a>
      </p>
    </form>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
