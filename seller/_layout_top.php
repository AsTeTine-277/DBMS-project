<?php

$self = basename($_SERVER['PHP_SELF']);
$biz  = $biz ?? current_business(current_user()['id']);

$navLinks = [
    ['dashboard.php',     'dashboard',   'Dashboard'],
    ['profile.php',       'storefront',  'My Business'],
    ['products.php',      'inventory_2', 'My Products'],
    ['add-product.php',   'add_box',     'Add Product'],
    ['orders.php',        'receipt_long','Orders'],
    ['bookings.php',      'event',       'Bookings'],
    ['custom-orders.php', 'auto_awesome','Custom Orders'],
    ['settings.php',      'settings',    'Settings'],
];
?>
<section class="bg-gradient-to-b from-primary-light/40 to-transparent">
  <div class="max-w-7xl mx-auto px-4 md:px-6 py-8">
    <div class="flex items-center justify-between gap-3 flex-wrap">
      <div>
        <p class="text-xs uppercase tracking-widest font-bold text-primary">Seller Portal</p>
        <h1 class="font-serif text-2xl md:text-3xl font-bold mt-1"><?= h($biz['shop_name']) ?></h1>
      </div>
      <a href="../customer/home.php" class="hm-btn hm-btn-outline">
        <span class="material-symbols-outlined text-base">storefront</span>
        View Public Shop
      </a>
    </div>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 md:px-6 pb-12 grid lg:grid-cols-4 gap-6">
  <aside class="lg:col-span-1 min-w-0 space-y-4">
    <div class="bg-white rounded-3xl border border-outline/40 p-4">
      <nav class="space-y-1 text-sm">
        <?php foreach ($navLinks as $link):
            $file = $link[0];
            $icon = $link[1];
            $label = $link[2];
            $active = $self === $file ? ' is-active' : '';
        ?>
          <a href="<?= h($file) ?>" class="hm-side-link<?= $active ?>">
            <span class="material-symbols-outlined"><?= h($icon) ?></span>
            <?= h($label) ?>
          </a>
        <?php endforeach; ?>
        <a href="../logout.php" class="hm-side-link text-red-600 hover:bg-red-50">
          <span class="material-symbols-outlined">logout</span>
          Logout
        </a>
      </nav>
    </div>
  </aside>
  <div class="lg:col-span-3 min-w-0 space-y-6">
