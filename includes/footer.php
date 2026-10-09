<?php
$self_path = $_SERVER['PHP_SELF'] ?? '';
$in_sub = (strpos($self_path, '/customer/') !== false)
       || (strpos($self_path, '/seller/')   !== false)
       || (strpos($self_path, '/admin/')    !== false);
$base = $in_sub ? '../' : '';
$logged_in_footer = is_logged_in();
?>
</main>

<footer class="bg-[#1a1014] text-white mt-16">
  <div class="max-w-7xl mx-auto px-4 md:px-6 py-12 grid grid-cols-2 md:grid-cols-4 gap-8">
    <div class="col-span-2 md:col-span-1">
      <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-primary to-pink-400 flex items-center justify-center text-white">
          <span class="material-symbols-outlined text-2xl">local_florist</span>
        </div>
        <span class="font-serif text-2xl font-bold text-white">HerMart</span>
      </div>
      <p class="text-sm text-pink-100/70 leading-relaxed">
        A women-led artisan marketplace celebrating handmade crafts, traditional products, and homegrown businesses from all 64 districts of Bangladesh.
      </p>
    </div>
    <div>
      <h4 class="text-xs uppercase tracking-wider font-bold text-pink-200 mb-4">Explore</h4>
      <ul class="space-y-2 text-sm">
        <li><a class="hover:text-pink-300" href="<?= h($base) ?>index.php">Home</a></li>
        <?php if ($logged_in_footer): ?>
          <li><a class="hover:text-pink-300" href="<?= h($base) ?>customer/home.php">Shop Home</a></li>
          <li><a class="hover:text-pink-300" href="<?= h($base) ?>customer/dashboard.php">My Dashboard</a></li>
          <li><a class="hover:text-pink-300" href="<?= h($base) ?>customer/products.php">All Products</a></li>
          <li><a class="hover:text-pink-300" href="<?= h($base) ?>customer/categories.php">Categories</a></li>
          <li><a class="hover:text-pink-300" href="<?= h($base) ?>customer/sellers.php">Sellers</a></li>
          <li><a class="hover:text-pink-300" href="<?= h($base) ?>customer/discover.php">Discover Bangladesh</a></li>
        <?php else: ?>
          <li><a class="hover:text-pink-300" href="<?= h($base) ?>login.php">Sign In</a></li>
          <li><a class="hover:text-pink-300" href="<?= h($base) ?>register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
    <div>
      <h4 class="text-xs uppercase tracking-wider font-bold text-pink-200 mb-4">For Buyers</h4>
      <ul class="space-y-2 text-sm">
        <?php if ($logged_in_footer): ?>
          <li><a class="hover:text-pink-300" href="<?= h($base) ?>customer/custom-order.php">Custom Order</a></li>
          <li><a class="hover:text-pink-300" href="<?= h($base) ?>customer/services.php">Services</a></li>
          <li><a class="hover:text-pink-300" href="<?= h($base) ?>customer/vendor-booking.php">Vendor Booking</a></li>
          <li><a class="hover:text-pink-300" href="<?= h($base) ?>customer/reviews.php">Reviews</a></li>
          <li><a class="hover:text-pink-300" href="<?= h($base) ?>customer/cart.php">Cart</a></li>
        <?php else: ?>
          <li><a class="hover:text-pink-300" href="<?= h($base) ?>login.php">Sign In</a></li>
        <?php endif; ?>
      </ul>
    </div>
    <div>
      <h4 class="text-xs uppercase tracking-wider font-bold text-pink-200 mb-4">For Sellers</h4>
      <ul class="space-y-2 text-sm">
        <?php if ($logged_in_footer): ?>
          <li><a class="hover:text-pink-300" href="<?= h($base) ?>seller/profile.php">My Business</a></li>
          <li><a class="hover:text-pink-300" href="<?= h($base) ?>seller/dashboard.php">Dashboard</a></li>
          <li><a class="hover:text-pink-300" href="<?= h($base) ?>seller/products.php">My Products</a></li>
          <li><a class="hover:text-pink-300" href="<?= h($base) ?>seller/add-product.php">Add Product</a></li>
          <li><a class="hover:text-pink-300" href="<?= h($base) ?>seller/settings.php">Settings</a></li>
          <li><a class="hover:text-pink-300" href="<?= h($base) ?>verification.php">Verification</a></li>
        <?php else: ?>
          <li><a class="hover:text-pink-300" href="<?= h($base) ?>register.php?role=seller">Become a Seller</a></li>
          <li><a class="hover:text-pink-300" href="<?= h($base) ?>verification.php">Verification</a></li>
          <li><a class="hover:text-pink-300" href="<?= h($base) ?>login.php">Sign In</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
  <div class="border-t border-white/10">
    <div class="max-w-7xl mx-auto px-4 md:px-6 py-5 flex flex-col md:flex-row items-center justify-between gap-3 text-xs text-pink-100/60">
      <span>© <?= date('Y') ?> HerMart. Empowering women entrepreneurs across Bangladesh.</span>
      <span>Dhaka • Chattogram • Rajshahi • Sylhet • Rangpur</span>
    </div>
  </div>
</footer>

<script src="<?= h($base) ?>assets/js/main.js?v=<?= (int)filemtime(__DIR__ . '/../assets/js/main.js') ?>"></script>
</body>
</html>
