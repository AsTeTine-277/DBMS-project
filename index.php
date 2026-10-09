<?php

$page_title = 'HerMart — Women Entrepreneur Marketplace of Bangladesh';
$page_desc  = 'Discover authentic handmade, traditional and Pahari products from women artisans across all 64 districts of Bangladesh.';
require_once __DIR__ . '/includes/init.php';

$cats = categories_all();
$featured = db_select("SELECT p.*, b.shop_name AS brand, b.district, c.name AS category_name
                       FROM products p
                       LEFT JOIN businesses b ON b.id = p.seller_id
                       LEFT JOIN categories c ON c.id = p.category_id
                       WHERE p.status = 'active'
                       ORDER BY p.created_at DESC LIMIT 8");
$biz = db_select("SELECT b.*, b.shop_name AS brand, c.name AS category_name
                  FROM businesses b
                  LEFT JOIN categories c ON c.id = b.category_id
                  WHERE b.status = 'active'
                  ORDER BY b.verified DESC, b.id ASC LIMIT 6");
$reviews = db_select("SELECT r.*, u.name AS customer_name, b.shop_name AS brand, p.name AS product_name
                      FROM reviews r
                      LEFT JOIN users u      ON u.id = r.customer_id
                      LEFT JOIN businesses b ON b.id = r.seller_id
                      LEFT JOIN products p   ON p.id = r.product_id
                      WHERE r.status = 'active'
                      ORDER BY r.created_at DESC LIMIT 4");
$divisions = regions_by_type('division');

include __DIR__ . '/includes/header.php';
?>

<!-- HERO -->
<section class="relative overflow-hidden">
  <div class="absolute inset-0 bg-grid opacity-60 pointer-events-none"></div>
  <div class="absolute -top-24 -right-20 w-[420px] h-[420px] rounded-full bg-pink-200/40 blur-3xl pointer-events-none"></div>
  <div class="absolute -bottom-24 -left-20 w-[420px] h-[420px] rounded-full bg-rose-200/30 blur-3xl pointer-events-none"></div>

  <div class="relative max-w-7xl mx-auto px-4 md:px-6 py-14 md:py-20 grid lg:grid-cols-12 gap-10 items-center">
    <div class="lg:col-span-7">
      <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-white/70 border border-primary-container text-primary">
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
        Women-Led · 64 Districts · Verified Artisans
      </span>
      <h1 class="font-serif text-4xl md:text-6xl font-bold text-on-surface leading-[1.05] mt-5">
        Handmade with love by
        <span class="text-primary">Bangladesh's women artisans</span>
      </h1>
      <p class="mt-5 text-lg text-on-surface-variant max-w-2xl">
        HerMart is the trusted marketplace for authentic Nakshi Kantha, Jamdani, jewelry,
        rural foods and traditional crafts — direct from women entrepreneurs in every district.
      </p>
      <div class="mt-7 flex flex-wrap gap-3">
        <a href="customer/home.php" class="hm-btn hm-btn-primary">
          <span class="material-symbols-outlined text-base">shopping_bag</span>
          Start Shopping
        </a>
        <a href="customer/sellers.php" class="hm-btn hm-btn-outline">
          <span class="material-symbols-outlined text-base">storefront</span>
          Meet the Artisans
        </a>
        <a href="register.php?role=seller" class="hm-btn hm-btn-light">
          Become a Seller
        </a>
      </div>

      <div class="mt-10 grid grid-cols-3 max-w-md gap-4">
        <div><div class="font-serif text-3xl font-bold text-primary">2,400+</div><div class="text-xs text-on-surface-variant">Women Artisans</div></div>
        <div><div class="font-serif text-3xl font-bold text-primary">64</div><div class="text-xs text-on-surface-variant">Districts</div></div>
        <div><div class="font-serif text-3xl font-bold text-primary">12k+</div><div class="text-xs text-on-surface-variant">Happy Buyers</div></div>
      </div>
    </div>

    <div class="lg:col-span-5">
      <div class="relative">
        <div class="absolute -inset-4 bg-gradient-to-tr from-primary-light to-pink-100 rounded-3xl blur-2xl opacity-70"></div>
        <div class="relative grid grid-cols-2 gap-4">
          <?php foreach (array_slice($featured, 0, 4) as $i => $p): ?>
            <div class="rounded-2xl overflow-hidden shadow-lg <?= $i % 2 === 0 ? 'translate-y-6' : '' ?>">
              <img <?= product_image_attrs($p['image']) ?> alt="<?= h($p['name']) ?>" class="w-full h-44 object-cover">
              <div class="bg-white p-3">
                <div class="text-xs font-semibold text-primary"><?= h($p['category_name'] ?? 'Featured') ?></div>
                <div class="text-sm font-bold truncate"><?= h($p['name']) ?></div>
                <div class="text-xs text-on-surface-variant"><?= h($p['district']) ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CATEGORIES -->
<section class="max-w-7xl mx-auto px-4 md:px-6 py-14">
  <div class="flex items-end justify-between flex-wrap gap-3 mb-6">
    <div>
      <p class="text-xs uppercase tracking-widest font-bold text-primary">Browse</p>
      <h2 class="font-serif text-3xl md:text-4xl font-bold">Shop by category</h2>
    </div>
    <a href="customer/categories.php" class="text-sm font-bold text-primary hover:underline">All categories →</a>
  </div>
  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
    <?php foreach ($cats as $c): ?>
      <a href="customer/categories.php?cat=<?= h($c['slug']) ?>" class="hm-card p-5 flex flex-col items-center text-center group">
        <div class="w-14 h-14 rounded-full bg-primary-light flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
          <span class="material-symbols-outlined text-primary text-3xl"><?= h($c['icon']) ?></span>
        </div>
        <h3 class="font-bold text-on-surface"><?= h($c['name']) ?></h3>
        <p class="text-xs text-on-surface-variant mt-1">Explore →</p>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- FEATURED PRODUCTS -->
<section class="bg-white border-y border-outline/40">
  <div class="max-w-7xl mx-auto px-4 md:px-6 py-14">
    <div class="flex items-end justify-between flex-wrap gap-3 mb-6">
      <div>
        <p class="text-xs uppercase tracking-widest font-bold text-primary">Featured</p>
        <h2 class="font-serif text-3xl md:text-4xl font-bold">Hand-picked products</h2>
      </div>
      <a href="customer/products.php" class="text-sm font-bold text-primary hover:underline">See all →</a>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
      <?php foreach ($featured as $p): ?>
        <?php render_product_card($p, cart_action_url(), 'customer/product-details.php'); ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- SELLERS -->
<section class="max-w-7xl mx-auto px-4 md:px-6 py-14">
  <div class="flex items-end justify-between flex-wrap gap-3 mb-6">
    <div>
      <p class="text-xs uppercase tracking-widest font-bold text-primary">Our Community</p>
      <h2 class="font-serif text-3xl md:text-4xl font-bold">Meet women entrepreneurs</h2>
    </div>
    <a href="customer/sellers.php" class="text-sm font-bold text-primary hover:underline">All sellers →</a>
  </div>
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
    <?php foreach ($biz as $b): ?>
      <a href="customer/seller-details.php?id=<?= (int)$b['id'] ?>" class="hm-card p-5 flex gap-4 items-center">
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-primary to-pink-300 text-white flex items-center justify-center text-2xl font-bold shrink-0">
          <?= strtoupper(substr($b['brand'], 0, 1)) ?>
        </div>
        <div class="min-w-0">
          <h3 class="font-bold truncate"><?= h($b['brand']) ?></h3>
          <p class="text-xs text-on-surface-variant truncate"><?= h($b['category_name'] ?? '') ?> · <?= h($b['district']) ?>, <?= h($b['division']) ?></p>
          <p class="text-xs text-on-surface-variant line-clamp-2 mt-1"><?= h($b['description']) ?></p>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- REGIONS -->
<section class="bg-gradient-to-br from-primary to-primary-dark text-white">
  <div class="max-w-7xl mx-auto px-4 md:px-6 py-14">
    <p class="text-xs uppercase tracking-widest font-bold text-pink-200">Discover Bangladesh</p>
    <h2 class="font-serif text-3xl md:text-4xl font-bold mt-2">Shop by region</h2>
    <p class="text-pink-100/80 mt-2 max-w-2xl">Each division has its own craft tradition. Explore regional specialties.</p>
    <div class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-3">
      <?php foreach ($divisions as $d): ?>
        <a href="customer/discover.php?div=<?= urlencode($d['name']) ?>" class="bg-white/10 hover:bg-white/20 backdrop-blur-sm border border-white/20 rounded-2xl p-4 flex items-center gap-3 transition">
          <span class="material-symbols-outlined">map</span>
          <span class="font-bold"><?= h($d['name']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- REVIEWS -->
<section class="max-w-7xl mx-auto px-4 md:px-6 py-14">
  <div class="text-center mb-8">
    <p class="text-xs uppercase tracking-widest font-bold text-primary">Loved by buyers</p>
    <h2 class="font-serif text-3xl md:text-4xl font-bold">What customers say</h2>
  </div>
  <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-5">
    <?php foreach ($reviews as $r): ?>
      <div class="hm-card p-5">
        <div class="flex gap-1 text-amber-400 mb-3">
          <?php for ($i = 0; $i < (int)$r['rating']; $i++): ?>
            <span class="material-symbols-outlined text-base">star</span>
          <?php endfor; ?>
        </div>
        <p class="text-sm text-on-surface line-clamp-4">"<?= h($r['review_text'] ?? '') ?>"</p>
        <div class="mt-3 text-xs text-on-surface-variant">— <?= h($r['customer_name']) ?> on <strong class="text-on-surface"><?= h($r['product_name'] ?? $r['brand']) ?></strong></div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- CTA -->
<section class="max-w-7xl mx-auto px-4 md:px-6 pb-14">
  <div class="bg-white rounded-3xl border border-outline/40 p-8 md:p-12 flex flex-col md:flex-row items-center justify-between gap-6 shadow-sm-custom">
    <div>
      <h3 class="font-serif text-2xl md:text-3xl font-bold">Are you a woman entrepreneur?</h3>
      <p class="text-on-surface-variant mt-2 max-w-2xl">Join HerMart to reach thousands of buyers across Bangladesh. We verify every seller.</p>
    </div>
    <div class="flex gap-3">
      <a href="register.php?role=seller" class="hm-btn hm-btn-primary">Register as Seller</a>
      <a href="verification.php" class="hm-btn hm-btn-outline">Learn More</a>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>