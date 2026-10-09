<?php
require_once __DIR__ . '/init.php';

$page_title = $page_title ?? 'HerMart - Bangladesh Women Entrepreneur Marketplace';
$page_desc  = $page_desc  ?? 'HerMart — a women-led artisan marketplace celebrating handmade crafts, traditional products, and women entrepreneurs from all 64 districts of Bangladesh.';

// Determine the active file for nav highlighting.
$self = basename($_SERVER['PHP_SELF']);
$nav_role = $_SESSION['user']['role'] ?? 'guest';
$logged_in = is_logged_in();
$header_search_query = trim($_GET['q'] ?? '');
$show_shop_search = $nav_role !== 'admin';

function navActive($self, $target) {
    return $self === $target ? ' hm-nav-link-active' : '';
}

function navActivePrefix($self, $prefix) {
    return strpos($self, $prefix) === 0 ? ' hm-nav-link-active' : '';
}

// Detect whether the current request lives inside one of the new
// role-based sub-folders so relative links to the project root work.
$self_path = $_SERVER['PHP_SELF'] ?? '';
$in_sub = (strpos($self_path, '/customer/') !== false)
       || (strpos($self_path, '/seller/')   !== false)
       || (strpos($self_path, '/admin/')    !== false);
$base = $in_sub ? '../' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($page_title) ?></title>
<meta name="description" content="<?= h($page_desc) ?>">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,500&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">

<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script>
  tailwind.config = {
    darkMode: 'class',
    theme: {
      extend: {
        colors: {
          primary: '#b01e56',
          'primary-dark': '#8e1242',
          'primary-light': '#fdf2f6',
          'primary-container': '#ffd9e2',
          'on-primary': '#ffffff',
          surface: '#fef7ff',
          'surface-card': '#ffffff',
          'surface-dim': '#f5ecf7',
          outline: '#e2d3df',
          'on-surface': '#201a1e',
          'on-surface-variant': '#514349',
        },
        fontFamily: {
          sans: ['Manrope', 'sans-serif'],
          serif: ['Playfair Display', 'serif'],
        }
      }
    }
  }
</script>

<link rel="stylesheet" href="<?= h(url($base . 'assets/css/style.css')) ?>">
</head>
<body class="bg-surface text-on-surface font-sans min-h-screen flex flex-col antialiased selection:bg-primary selection:text-white">

<!-- Top Navbar -->
<header class="w-full border-b border-outline/50 bg-white/85 backdrop-blur-md sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-4 pr-2 md:px-6 md:pr-0 h-20 flex items-center justify-between gap-3">

    <a href="<?= h($base) ?>index.php" class="flex items-center gap-3 group shrink-0">
      <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-primary to-pink-400 flex items-center justify-center text-white shadow-md group-hover:scale-105 transition-transform">
        <span class="material-symbols-outlined text-2xl">local_florist</span>
      </div>
      <div class="hidden sm:block">
        <span class="font-serif text-2xl font-bold tracking-tight text-primary">HerMart</span>
        <span class="block text-[10px] tracking-wider uppercase font-semibold text-on-surface-variant -mt-1">Bangladesh</span>
      </div>
    </a>

    <nav class="hm-primary-nav<?= $nav_role === 'customer' ? ' hm-primary-nav-customer' : '' ?> hidden lg:flex items-center gap-1 text-sm font-medium text-on-surface-variant" aria-label="Main navigation" tabindex="0">
      <a class="hm-nav-link<?= navActive($self, 'index.php') ?>" href="<?= h($base) ?>index.php">Home</a>
      <?php if ($logged_in): ?>
        <a class="hm-nav-link<?= navActive($self, 'home.php') ?>" href="<?= h($base) ?>customer/home.php">Shop</a>
        <?php if ($nav_role === 'seller'): ?>
          <a class="hm-nav-link<?= navActive($self, 'dashboard.php') ?>" href="<?= h($base) ?>seller/dashboard.php">My Dashboard</a>
          <a class="hm-nav-link<?= navActive($self, 'products.php') ?>" href="<?= h($base) ?>seller/products.php">My Products</a>
        <?php else: ?>
          <a class="hm-nav-link<?= navActive($self, 'dashboard.php') ?>" href="<?= h($base) ?>customer/dashboard.php">My Dashboard</a>
          <a class="hm-nav-link<?= navActive($self, 'products.php') ?>" href="<?= h($base) ?>customer/products.php">Products</a>
        <?php endif; ?>
        <a class="hm-nav-link<?= navActive($self, 'categories.php') ?>" href="<?= h($base) ?>customer/categories.php">Categories</a>
        <a class="hm-nav-link<?= navActive($self, 'sellers.php') ?>" href="<?= h($base) ?>customer/sellers.php">Sellers</a>
        <a class="hm-nav-link<?= navActive($self, 'discover.php') ?>" href="<?= h($base) ?>customer/discover.php">Discover</a>
        <a class="hm-nav-link<?= navActive($self, 'services.php') ?>" href="<?= h($base) ?>customer/services.php">Services</a>
        <a class="hm-nav-link<?= navActive($self, 'vendor-booking.php') ?>" href="<?= h($base) ?>customer/vendor-booking.php">Book Vendor</a>
        <a class="hm-nav-link<?= navActive($self, 'custom-order.php') ?>" href="<?= h($base) ?>customer/custom-order.php">Custom Order</a>
      <?php endif; ?>
    </nav>

    <div class="hm-header-actions flex items-center gap-2 shrink-0">
      <?php if ($logged_in): ?>
        <a href="<?= h($base) ?>customer/cart.php" class="hm-nav-icon<?= navActive($self, 'cart.php') ?>" title="Cart">
          <span class="material-symbols-outlined text-[20px]">shopping_cart</span>
        </a>
      <?php endif; ?>

      <?php if ($logged_in): ?>
        <?php if ($nav_role === 'seller'): ?>
          <a href="<?= h($base) ?>seller/dashboard.php" class="hm-nav-icon<?= navActivePrefix($self, 'dashboard') ?>" title="Dashboard">
            <span class="material-symbols-outlined text-[20px]">dashboard</span>
          </a>
          <a href="<?= h($base) ?>seller/products.php" class="hm-nav-icon<?= navActivePrefix($self, 'product') ?>" title="My Products">
            <span class="material-symbols-outlined text-[20px]">inventory_2</span>
          </a>
          <a href="<?= h($base) ?>seller/add-product.php" class="hm-nav-icon<?= navActivePrefix($self, 'add-product') ?>" title="Add Product">
            <span class="material-symbols-outlined text-[20px]">add_box</span>
          </a>
          <a href="<?= h($base) ?>seller/settings.php" class="hm-nav-icon<?= navActive($self, 'settings.php') ?>" title="Settings">
            <span class="material-symbols-outlined text-[20px]">settings</span>
          </a>
        <?php elseif ($nav_role === 'admin'): ?>
          <a href="<?= h($base) ?>admin/dashboard.php" class="hm-nav-icon" title="Admin">
            <span class="material-symbols-outlined text-[20px]">admin_panel_settings</span>
          </a>
        <?php else: ?>
          <a href="<?= h($base) ?>customer/profile.php" class="hm-nav-icon<?= navActive($self, 'profile.php') ?>" title="My Profile">
            <span class="material-symbols-outlined text-[20px]">person</span>
          </a>
          <a href="<?= h($base) ?>customer/orders.php" class="hm-nav-icon<?= navActive($self, 'orders.php') ?>" title="My Orders">
            <span class="material-symbols-outlined text-[20px]">receipt_long</span>
          </a>
        <?php endif; ?>
        <a href="<?= h($base) ?>logout.php" class="hm-nav-logout" title="Log out">
          <span class="material-symbols-outlined text-[18px]">logout</span>
          <span class="hidden md:inline">Logout</span>
        </a>
      <?php else: ?>
        <a href="<?= h($base) ?>login.php" class="hm-nav-pill">
          <span class="material-symbols-outlined text-[18px]">login</span>
          <span>Sign In</span>
        </a>
        <a href="<?= h($base) ?>register.php" class="hm-nav-pill bg-primary text-white hover:bg-primary-dark border-primary">
          <span>Register</span>
        </a>
      <?php endif; ?>
      <button id="hmMobileBtn" class="lg:hidden hm-nav-icon" aria-label="Menu">
        <span class="material-symbols-outlined text-[22px]">menu</span>
      </button>
    </div>
  </div>

  <?php if ($show_shop_search): ?>
  <div class="border-t border-outline/30 bg-white/95">
    <form action="<?= h($base) ?>customer/products.php" method="get" role="search" class="max-w-7xl mx-auto px-4 md:px-6 py-2.5">
      <div class="flex items-center gap-2 max-w-2xl mx-auto rounded-full border border-outline/60 bg-surface px-3 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/15">
        <span class="material-symbols-outlined text-primary" aria-hidden="true">search</span>
        <input type="search" name="q" value="<?= h($header_search_query) ?>" placeholder="Search products, brands, or categories" aria-label="Search products" class="min-w-0 flex-1 border-0 bg-transparent py-2 text-sm focus:ring-0">
        <button type="submit" class="hm-btn hm-btn-primary px-4 py-2" aria-label="Search products">Search</button>
      </div>
    </form>
  </div>
  <?php endif; ?>

  <!-- Mobile menu -->
  <div id="hmMobileMenu" class="hidden lg:hidden border-t border-outline/40 bg-white">
    <nav class="max-w-7xl mx-auto px-4 py-3 grid grid-cols-2 gap-2 text-sm">
      <?php if ($logged_in): ?>
        <a class="hm-nav-link<?= navActive($self, 'home.php') ?>" href="<?= h($base) ?>customer/home.php">Shop</a>
        <?php if ($nav_role === 'seller'): ?>
          <a class="hm-nav-link<?= navActive($self, 'dashboard.php') ?>" href="<?= h($base) ?>seller/dashboard.php">My Dashboard</a>
          <a class="hm-nav-link<?= navActive($self, 'products.php') ?>" href="<?= h($base) ?>seller/products.php">My Products</a>
        <?php else: ?>
          <a class="hm-nav-link<?= navActive($self, 'dashboard.php') ?>" href="<?= h($base) ?>customer/dashboard.php">My Dashboard</a>
          <a class="hm-nav-link<?= navActive($self, 'products.php') ?>" href="<?= h($base) ?>customer/products.php">Products</a>
        <?php endif; ?>
        <a class="hm-nav-link<?= navActive($self, 'categories.php') ?>" href="<?= h($base) ?>customer/categories.php">Categories</a>
        <a class="hm-nav-link<?= navActive($self, 'sellers.php') ?>" href="<?= h($base) ?>customer/sellers.php">Sellers</a>
        <a class="hm-nav-link<?= navActive($self, 'discover.php') ?>" href="<?= h($base) ?>customer/discover.php">Discover</a>
        <a class="hm-nav-link<?= navActive($self, 'services.php') ?>" href="<?= h($base) ?>customer/services.php">Services</a>
        <a class="hm-nav-link<?= navActive($self, 'vendor-booking.php') ?>" href="<?= h($base) ?>customer/vendor-booking.php">Book Vendor</a>
        <a class="hm-nav-link<?= navActive($self, 'custom-order.php') ?>" href="<?= h($base) ?>customer/custom-order.php">Custom Order</a>
        <a class="hm-nav-link<?= navActive($self, 'cart.php') ?>" href="<?= h($base) ?>customer/cart.php">Cart</a>
        <?php if ($nav_role === 'seller'): ?>
          <a class="hm-nav-link" href="<?= h($base) ?>seller/dashboard.php">Seller Dashboard</a>
          <a class="hm-nav-link" href="<?= h($base) ?>seller/products.php">My Products</a>
          <a class="hm-nav-link" href="<?= h($base) ?>seller/add-product.php">Add Product</a>
          <a class="hm-nav-link" href="<?= h($base) ?>seller/settings.php">Settings</a>
        <?php else: ?>
          <a class="hm-nav-link<?= navActive($self, 'orders.php') ?>" href="<?= h($base) ?>customer/orders.php">Orders</a>
        <?php endif; ?>
      <?php else: ?>
        <a class="hm-nav-link" href="<?= h($base) ?>login.php">Sign In</a>
        <a class="hm-nav-link" href="<?= h($base) ?>register.php">Register</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<?php $flash = flash_get(); if ($flash): ?>
<div class="max-w-7xl mx-auto px-4 mt-4">
  <div class="rounded-xl px-4 py-3 text-sm font-medium border <?= $flash['type'] === 'error' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' ?>">
    <?= h($flash['message']) ?>
  </div>
</div>
<?php endif; ?>

<main class="flex-1">
