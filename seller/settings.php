<?php

$page_title = 'Settings — HerMart';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/seller-auth.php';

seller_only();

$user  = current_user();
$userRow = user_by_id($user['id']);
if (!$userRow) {
    flash_set('error', 'Account not found.');
    redirect('seller/dashboard.php');
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $name    = safe_str($_POST['name']    ?? '');
        $phone   = safe_str($_POST['phone']   ?? '');
        $address = safe_str($_POST['address'] ?? '');

        if ($name === '' || $phone === '') {
            $error = 'Name and phone are required.';
        } elseif (!preg_match('/^[0-9+\-\s]{6,20}$/', $phone)) {
            $error = 'Please enter a valid phone number.';
        } else {
            
            $existing = db_select_one("SELECT id FROM users WHERE phone = ? AND id <> ?", 'si', [$phone, $user['id']]);
            if ($existing) {
                $error = 'Another account already uses that phone number.';
            } else {
                db_execute(
                    "UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?",
                    'sssi',
                    [$name, $phone, $address, $user['id']]
                );
                
                $_SESSION['user']['name']  = $name;
                $_SESSION['user']['phone'] = $phone;
                flash_set('success', 'Profile updated.');
                redirect('seller/settings.php');
            }
        }
    } elseif ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $fresh = user_by_id($user['id']);
        if ($current === '' || !password_verify($current, $fresh['password_hash'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new) < 6) {
            $error = 'New password must be at least 6 characters.';
        } elseif ($new !== $confirm) {
            $error = 'New password and confirmation do not match.';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            db_execute("UPDATE users SET password_hash = ? WHERE id = ?", 'si', [$hash, $user['id']]);
            flash_set('success', 'Password changed. Please use the new password next time you sign in.');
            redirect('seller/settings.php');
        }
    } else {
        $error = 'Unknown action.';
    }

    
    $userRow = user_by_id($user['id']);
}

$biz = current_business($user['id']);

$_SESSION['user']['name']  = $userRow['name'];
$_SESSION['user']['phone'] = $userRow['phone'];

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/_layout_top.php';
?>

<section class="hm-container py-6">
  <div class="grid lg:grid-cols-3 gap-6">
    <!-- LEFT — profile form -->
    <div class="lg:col-span-2 bg-white rounded-3xl border border-outline/40 p-6">
      <h2 class="font-bold text-lg mb-4">Account</h2>

      <?php if ($error): ?>
        <div class="mb-4 rounded-xl px-4 py-3 text-sm font-medium border bg-red-50 text-red-700 border-red-200"><?= h($error) ?></div>
      <?php endif; ?>

      <form method="post" class="space-y-4" data-validate>
        <input type="hidden" name="action" value="update_profile">

        <div>
          <label class="hm-label" for="name">Full name</label>
          <input id="name" name="name" type="text" required minlength="2" class="hm-input" value="<?= h($userRow['name']) ?>">
        </div>

        <div>
          <label class="hm-label" for="email">Email (read-only)</label>
          <input id="email" name="email" type="email" disabled class="hm-input bg-surface-dim" value="<?= h($userRow['email']) ?>">
          <p class="text-[11px] text-on-surface-variant mt-1">Email changes are handled by support to keep your account secure.</p>
        </div>

        <div>
          <label class="hm-label" for="phone">Phone</label>
          <input id="phone" name="phone" type="text" required pattern="[0-9+\-\s]{6,20}" class="hm-input" value="<?= h($userRow['phone']) ?>">
        </div>

        <div>
          <label class="hm-label" for="address">Address</label>
          <input id="address" name="address" type="text" class="hm-input" value="<?= h($userRow['address']) ?>" placeholder="House, road, area, district">
        </div>

        <div class="flex gap-2">
          <button type="submit" class="hm-btn hm-btn-primary">Save changes</button>
          <a href="dashboard.php" class="hm-btn hm-btn-outline">Cancel</a>
        </div>
      </form>
    </div>

    <!-- RIGHT — business card -->
    <div class="bg-white rounded-3xl border border-outline/40 p-6">
      <h2 class="font-bold text-lg mb-2">Your business</h2>
      <p class="text-sm text-on-surface-variant mb-3">Public-facing business information is managed in My Business.</p>
      <div class="rounded-2xl bg-surface-dim p-4 text-sm">
        <div class="text-xs uppercase tracking-wider text-on-surface-variant">Shop</div>
        <div class="font-bold text-base mt-0.5"><?= h($biz['shop_name']) ?></div>
        <div class="text-xs mt-2 text-on-surface-variant"><?= h($biz['district'] ?? '') ?><?= !empty($biz['division']) ? ', ' . h($biz['division']) : '' ?></div>
        <div class="text-xs mt-1">Status: <span class="font-semibold"><?= h(format_status_label($biz['verified'] ? 'verified' : 'pending')) ?></span></div>
      </div>
      <a href="profile.php" class="hm-btn hm-btn-outline w-full justify-center mt-3">
        <span class="material-symbols-outlined text-base">storefront</span>
        Manage business profile
      </a>
    </div>
  </div>

  <!-- Password change -->
  <div class="bg-white rounded-3xl border border-outline/40 p-6 mt-6">
    <h2 class="font-bold text-lg mb-3">Change password</h2>
    <form method="post" class="grid sm:grid-cols-3 gap-4" data-validate>
      <input type="hidden" name="action" value="change_password">
      <div>
        <label class="hm-label" for="current_password">Current password</label>
        <input id="current_password" name="current_password" type="password" required minlength="6" class="hm-input">
      </div>
      <div>
        <label class="hm-label" for="new_password">New password</label>
        <input id="new_password" name="new_password" type="password" required minlength="6" class="hm-input">
      </div>
      <div>
        <label class="hm-label" for="confirm_password">Confirm new password</label>
        <input id="confirm_password" name="confirm_password" type="password" required minlength="6" class="hm-input">
      </div>
      <div class="sm:col-span-3">
        <button type="submit" class="hm-btn hm-btn-primary">Update password</button>
      </div>
    </form>
  </div>
</section>

<?php
include __DIR__ . '/_layout_bottom.php';
include __DIR__ . '/../includes/footer.php';
?>
