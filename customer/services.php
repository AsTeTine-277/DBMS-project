<?php
$page_title = 'Services — HerMart';
require_once __DIR__ . '/../includes/init.php';

$services = services_all();
$vendors = db_select(
    "SELECT b.id, b.shop_name AS brand, b.district, b.division, b.description, c.name AS category_name
     FROM businesses b LEFT JOIN categories c ON c.id = b.category_id
     WHERE b.status = 'active'
     ORDER BY b.verified DESC LIMIT 8"
);

include __DIR__ . '/../includes/header.php';
?>

<section class="bg-gradient-to-b from-primary-light/40 to-transparent">
  <div class="max-w-7xl mx-auto px-4 md:px-6 py-10">
    <div class="mb-3">
      <button type="button" onclick="if(history.length>1){history.back();}else{location.href='home.php';}" class="hm-btn-back" aria-label="Go back">
        <span class="material-symbols-outlined">arrow_back</span>
        Back
      </button>
    </div>
    <p class="text-xs uppercase tracking-widest font-bold text-primary">Services</p>
    <h1 class="font-serif text-3xl md:text-4xl font-bold mt-2">Women-provided services</h1>
    <p class="text-on-surface-variant mt-2 max-w-2xl">From tailoring to catering — hire skilled women professionals for any occasion.</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 md:px-6 py-10 grid md:grid-cols-2 lg:grid-cols-3 gap-5">
  <?php foreach ($services as $s): ?>
    <div class="hm-card p-6 flex flex-col">
      <span class="material-symbols-outlined text-primary text-4xl">design_services</span>
      <h2 class="font-bold text-lg mt-3"><?= h($s['name']) ?></h2>
      <p class="text-xs text-on-surface-variant"><?= h($s['category']) ?></p>
      <p class="text-sm text-on-surface mt-3 flex-1"><?= h($s['description']) ?></p>
      <div class="mt-4 flex items-center justify-between">
        <span class="font-bold text-primary"><?= money_tk($s['base_price']) ?>+</span>
        <a href="vendor-booking.php?service=<?= (int)$s['id'] ?>" class="hm-btn hm-btn-light text-xs">
          Book Now
        </a>
      </div>
    </div>
  <?php endforeach; ?>
</section>

<section class="bg-white border-y border-outline/40">
  <div class="max-w-7xl mx-auto px-4 md:px-6 py-12">
    <h2 class="font-serif text-2xl font-bold mb-6">Service providers</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <?php foreach ($vendors as $v): ?>
        <a href="seller-details.php?id=<?= (int)$v['id'] ?>" class="hm-card p-5 flex flex-col items-center text-center">
          <div class="w-16 h-16 rounded-full bg-gradient-to-tr from-primary to-pink-300 flex items-center justify-center text-white text-2xl font-bold mb-3">
            <?= strtoupper(substr($v['brand'], 0, 1)) ?>
          </div>
          <h3 class="font-bold"><?= h($v['brand']) ?></h3>
          <p class="text-xs text-on-surface-variant mt-1"><?= h($v['district']) ?>, <?= h($v['division']) ?></p>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>