<?php
$page_title = 'Shop Home — HerMart';
require_once __DIR__ . '/../includes/init.php';

$featured = db_select("SELECT p.*, b.shop_name AS brand, b.district, c.name AS category_name FROM products p
                      LEFT JOIN businesses b ON b.id = p.seller_id
                      LEFT JOIN categories c ON c.id = p.category_id
                      WHERE p.status = 'active'
                      ORDER BY p.created_at DESC");

$cats = categories_all();

include __DIR__ . '/../includes/header.php';
?>

<section class="bg-gradient-to-b from-primary-light/40 to-transparent">
  <div class="max-w-7xl mx-auto px-4 md:px-6 py-10">
    <p class="text-xs uppercase tracking-widest font-bold text-primary">Shop</p>
    <h1 class="font-serif text-3xl md:text-4xl font-bold mt-2">Welcome to HerMart Shop</h1>
    <p class="text-on-surface-variant mt-2 max-w-2xl">Discover authentic handmade products from women artisans across Bangladesh.</p>
  </div>
</section>

<!-- Quick category tiles -->
<section class="max-w-7xl mx-auto px-4 md:px-6 py-6">
  <div class="grid grid-cols-3 sm:grid-cols-5 lg:grid-cols-9 gap-3">
    <?php foreach ($cats as $c): ?>
      <a href="categories.php?cat=<?= h($c['slug']) ?>" class="hm-card p-3 flex flex-col items-center text-center">
        <div class="w-10 h-10 rounded-full bg-primary-light flex items-center justify-center mb-2">
          <span class="material-symbols-outlined text-primary text-2xl"><?= h($c['icon']) ?></span>
        </div>
        <div class="text-[11px] font-bold leading-tight"><?= h($c['name']) ?></div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 md:px-6 py-10">
  <div class="flex items-end justify-between flex-wrap gap-3 mb-6">
    <h2 class="font-serif text-3xl font-bold">Trending now</h2>
    <a href="products.php" class="text-sm font-bold text-primary hover:underline">View all →</a>
  </div>
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
    <?php foreach ($featured as $p): ?>
      <?php render_product_card($p, cart_action_url()); ?>
    <?php endforeach; ?>
  </div>
</section>

<!-- Service banner -->
<section class="max-w-7xl mx-auto px-4 md:px-6 py-6 grid md:grid-cols-3 gap-4">
  <a href="services.php" class="hm-card p-6 flex items-center gap-4">
    <div class="w-12 h-12 rounded-full bg-primary-light flex items-center justify-center"><span class="material-symbols-outlined text-primary">design_services</span></div>
    <div>
      <h3 class="font-bold">Services</h3>
      <p class="text-xs text-on-surface-variant">Tailoring, catering, mehendi & more</p>
    </div>
  </a>
  <a href="vendor-booking.php" class="hm-card p-6 flex items-center gap-4">
    <div class="w-12 h-12 rounded-full bg-primary-light flex items-center justify-center"><span class="material-symbols-outlined text-primary">event</span></div>
    <div>
      <h3 class="font-bold">Vendor Booking</h3>
      <p class="text-xs text-on-surface-variant">Hire women vendors for events</p>
    </div>
  </a>
  <a href="custom-order.php" class="hm-card p-6 flex items-center gap-4">
    <div class="w-12 h-12 rounded-full bg-primary-light flex items-center justify-center"><span class="material-symbols-outlined text-primary">auto_awesome</span></div>
    <div>
      <h3 class="font-bold">Custom Order</h3>
      <p class="text-xs text-on-surface-variant">Made-to-order from artisans</p>
    </div>
  </a>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>