<?php

$adminLinks = [
    ['href' => 'dashboard.php',     'icon' => 'space_dashboard',  'label' => 'Dashboard'],
    ['href' => 'users.php',         'icon' => 'group',            'label' => 'Users'],
    ['href' => 'products.php',       'icon' => 'inventory_2',      'label' => 'Products'],
    ['href' => 'categories.php',     'icon' => 'category',         'label' => 'Categories'],
    ['href' => 'orders.php',        'icon' => 'receipt_long',     'label' => 'Orders'],
    ['href' => 'payments.php',      'icon' => 'payments',         'label' => 'Payments'],
    ['href' => 'deliveries.php',    'icon' => 'local_shipping',   'label' => 'Deliveries'],
    ['href' => 'reviews.php',       'icon' => 'rate_review',      'label' => 'Reviews'],
    ['href' => 'bookings.php',      'icon' => 'event',            'label' => 'Bookings'],
    ['href' => 'custom-orders.php', 'icon' => 'auto_awesome',     'label' => 'Custom Orders'],
    ['href' => 'reports.php',       'icon' => 'analytics',        'label' => 'Reports'],
];
$currentAdmin = basename($_SERVER['PHP_SELF']);
?>
<section class="hm-container py-6">
  <style>
    /* Admin: stack action buttons vertically for better usability */
    .admin-actions { display:flex; flex-direction:column; gap:0.5rem; align-items:flex-end; }
    .admin-actions form { display:block; margin:0; }
    .admin-actions .hm-btn, .admin-actions button, .admin-actions .hm-select { width:auto; }
    @media (min-width:1024px) {
      .admin-actions { align-items:flex-end; }
    }
  </style>
  <div class="grid lg:grid-cols-[260px_1fr] gap-6">
    <aside class="bg-white rounded-3xl border border-outline/40 p-4 h-fit lg:sticky lg:top-4">
      <div class="flex items-center gap-3 mb-4 px-2">
        <span class="w-10 h-10 rounded-xl bg-primary-light text-primary flex items-center justify-center">
          <span class="material-symbols-outlined">admin_panel_settings</span>
        </span>
        <div>
          <div class="font-bold text-sm">Admin Panel</div>
          <div class="text-[11px] text-on-surface-variant"><?= h(($_SESSION['user']['name'] ?? 'Admin')) ?></div>
        </div>
      </div>
      <nav class="space-y-1">
        <?php foreach ($adminLinks as $l): ?>
          <a href="<?= h($l['href']) ?>"
             class="hm-side-link <?= $currentAdmin === $l['href'] ? 'hm-side-link-active' : '' ?>">
            <span class="material-symbols-outlined text-base"><?= h($l['icon']) ?></span>
            <?= h($l['label']) ?>
          </a>
        <?php endforeach; ?>
        <a href="../customer/home.php" class="hm-side-link mt-2">
          <span class="material-symbols-outlined text-base">arrow_back</span>
          Back to site
        </a>
        <a href="../logout.php" class="hm-side-link text-red-600">
          <span class="material-symbols-outlined text-base">logout</span>
          Log out
        </a>
      </nav>
    </aside>
    <div class="space-y-4">
