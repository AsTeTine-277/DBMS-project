<?php

$page_title = 'Sign In — HerMart';
$page_desc  = 'Sign in to your HerMart account.';
require_once __DIR__ . '/includes/init.php';


if (is_logged_in()) {
    $u = current_user();
    if ($u['role'] === 'seller') redirect('seller/dashboard.php');
    if ($u['role'] === 'admin')  redirect('admin/dashboard.php');
    redirect('customer/home.php');
}

$error    = '';
$success  = '';
$email    = '';
$redirect = $_GET['redirect'] ?? ($_POST['redirect'] ?? '');


function hm_safe_redirect($path) {
    $path = (string)$path;
    if ($path === '') return '';
    if ($path[0] === '/') $path = ltrim($path, '/');
    if (preg_match('#^[a-z][a-z0-9+.\-]*://#i', $path)) return '';
    if (strpos($path, '//') === 0) return '';
    if (preg_match('#^[a-z]:[\\\\/]#i', $path)) return '';
    $allowed_prefixes = ['customer/', 'seller/', 'admin/', 'index.php'];
    foreach ($allowed_prefixes as $prefix) {
        if ($path === $prefix || strpos($path, $prefix) === 0) {
            return $path;
        }
    }
    return '';
}
$redirect = hm_safe_redirect($redirect);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!db_ready() && db_error() !== '') {
        $error = 'Database connection failed. Please try again in a moment.';
        error_log('[HerMart] login blocked: ' . db_error());
    } else {
        
        $user = db_select_one(
            "SELECT id, name, email, phone, password_hash, role, status
             FROM users
             WHERE LOWER(email) = LOWER(?)
             LIMIT 1",
            's',
            [$email]
        );

        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['status'] !== 'active') {
                $error = 'Your account is not active. Please contact support.';
            } else {
                
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id'    => (int)$user['id'],
                    'name'  => $user['name'],
                    'email' => $user['email'],
                    'phone' => $user['phone'],
                    'role'  => $user['role'],
                ];
                flash_set('success', 'Welcome back, ' . $user['name'] . '!');

                
                if ($redirect !== '') {
                    redirect($redirect);
                }
                if ($user['role'] === 'admin')  redirect('admin/dashboard.php');
                if ($user['role'] === 'seller') redirect('seller/dashboard.php');
                redirect('customer/home.php');
            }
        } else {
            
            $error = 'Invalid email or password.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="max-w-7xl mx-auto px-4 md:px-6 py-10 md:py-16">
  <div class="grid lg:grid-cols-2 gap-10 items-center">

    <!-- LEFT — brand panel -->
    <div class="bg-gradient-to-br from-[#8e1242] via-[#b01e56] to-[#6d133b] rounded-3xl p-8 md:p-12 text-white relative overflow-hidden shadow-xl">
      <div class="absolute inset-0 opacity-10 pointer-events-none bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:16px_16px]"></div>
      <div class="absolute -bottom-16 -right-16 w-56 h-56 rounded-full bg-pink-400/20 blur-2xl pointer-events-none"></div>

      <a href="index.php" class="inline-flex items-center gap-2 text-pink-100 hover:text-white text-sm font-semibold mb-8 transition">
        <span class="material-symbols-outlined text-lg">arrow_back</span>
        Back to Home
      </a>

      <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold tracking-wide bg-white/15 text-pink-100 border border-white/20 mb-6">
        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
        Women-Led Artisan Marketplace
      </span>

      <h1 class="font-serif text-3xl md:text-4xl font-bold leading-tight mb-4">
        Welcome back to HerMart
      </h1>
      <p class="text-pink-100/90 text-sm leading-relaxed max-w-md">
        Sign in to manage your shop, place orders, request custom creations, or explore handmade crafts from women artisans across all 64 districts of Bangladesh.
      </p>

      <div class="mt-8 space-y-3">
        <div class="flex items-start gap-3 bg-white/10 backdrop-blur-sm rounded-2xl p-3.5 border border-white/10">
          <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-white text-lg">verified_user</span>
          </div>
          <div>
            <h4 class="text-xs font-bold text-white uppercase tracking-wider">Verified Artisans</h4>
            <p class="text-[12px] text-pink-100/80 mt-0.5">Every seller is verified with NID or Birth Certificate.</p>
          </div>
        </div>
        <div class="flex items-start gap-3 bg-white/10 backdrop-blur-sm rounded-2xl p-3.5 border border-white/10">
          <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-white text-lg">local_shipping</span>
          </div>
          <div>
            <h4 class="text-xs font-bold text-white uppercase tracking-wider">Direct &amp; Fast Delivery</h4>
            <p class="text-[12px] text-pink-100/80 mt-0.5">Instant same-district delivery &amp; custom pre-order tracking.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- RIGHT — login form -->
    <div class="bg-white rounded-3xl shadow-sm-custom border border-outline/40 p-6 md:p-10">

      <div class="mb-6">
        <h2 class="font-serif text-2xl md:text-3xl font-bold text-on-surface tracking-tight mb-1">
          Sign in to your account
        </h2>
        <p class="text-sm text-on-surface-variant">
          Enter your email and password to continue.
        </p>
      </div>

      <?php if ($error): ?>
        <div class="mb-4 rounded-xl px-4 py-3 text-sm font-medium border bg-red-50 text-red-700 border-red-200 flex items-start gap-2">
          <span class="material-symbols-outlined text-base shrink-0 mt-0.5">error</span>
          <span><?= h($error) ?></span>
        </div>
      <?php endif; ?>

      <form method="post" action="login.php" class="space-y-4" novalidate>
        <input type="hidden" name="redirect" value="<?= h($redirect) ?>">

        <div>
          <label class="hm-label" for="email">Email Address</label>
          <div class="relative">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px] pointer-events-none">mail</span>
            <input id="email"
                   name="email"
                   type="email"
                   required
                   autocomplete="email"
                   autofocus
                   class="hm-input pl-11"
                   placeholder="you@example.com"
                   value="<?= h($email) ?>">
          </div>
        </div>

        <div>
          <label class="hm-label" for="password">Password</label>
          <div class="relative">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px] pointer-events-none">lock</span>
            <input id="password"
                   name="password"
                   type="password"
                   required
                   minlength="6"
                   autocomplete="current-password"
                   class="hm-input pl-11 pr-11"
                   placeholder="Enter your password">
            <button type="button"
                    id="togglePassword"
                    class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 rounded-lg text-on-surface-variant hover:text-primary hover:bg-primary-light/50 transition"
                    aria-label="Show password">
              <span class="material-symbols-outlined text-[20px]">visibility</span>
            </button>
          </div>
        </div>

        <div class="flex items-center justify-between text-xs">
          <label class="inline-flex items-center gap-2 text-on-surface-variant cursor-pointer select-none">
            <input type="checkbox" name="remember" class="rounded border-outline text-primary focus:ring-primary">
            <span>Remember me</span>
          </label>
          <a href="verification.php" class="text-primary font-semibold hover:underline" title="Contact support to reset your password">Forgot password?</a>
        </div>

        <button type="submit" class="hm-btn hm-btn-primary w-full justify-center">
          <span class="material-symbols-outlined text-base">login</span>
          Sign In
        </button>

        <div class="relative my-5 flex items-center gap-3 text-xs text-on-surface-variant">
          <span class="flex-1 h-px bg-outline"></span>
          <span>OR</span>
          <span class="flex-1 h-px bg-outline"></span>
        </div>

        <a href="register.php" class="hm-btn hm-btn-outline w-full justify-center">
          <span class="material-symbols-outlined text-base">person_add</span>
          Create a new account
        </a>
      </form>

      <p class="mt-6 text-center text-sm text-on-surface-variant">
        Don't have an account?
        <a href="register.php" class="text-primary font-bold hover:underline">Register here</a>
      </p>

      <div class="mt-4 text-center">
        <a href="index.php" class="hm-btn-back">
          <span class="material-symbols-outlined">arrow_back</span>
          Back to Home
        </a>
      </div>
    </div>
  </div>
</section>

<script>
  
  (function () {
    var btn = document.getElementById('togglePassword');
    var input = document.getElementById('password');
    if (!btn || !input) return;
    btn.addEventListener('click', function () {
      if (input.type === 'password') {
        input.type = 'text';
        btn.querySelector('span').textContent = 'visibility_off';
      } else {
        input.type = 'password';
        btn.querySelector('span').textContent = 'visibility';
      }
    });
  })();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
