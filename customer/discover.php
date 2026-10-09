<?php
$page_title = 'Discover Bangladesh — HerMart';
require_once __DIR__ . '/../includes/init.php';

$divisions = regions_by_type('division');

$selectedDiv = $_GET['div'] ?? '';
$districts = [];
$biz = [];
if ($selectedDiv !== '') {
    $divRow = db_select_one("SELECT id FROM locations WHERE name = ? AND type = 'division'", 's', [$selectedDiv]);
    if ($divRow) {
        $districts = db_select("SELECT * FROM locations WHERE parent_id = ? AND type = 'district' ORDER BY name", 'i', [$divRow['id']]);
        $biz = db_select(
            "SELECT b.*, c.name AS category_name FROM businesses b
             LEFT JOIN categories c ON c.id = b.category_id
             WHERE b.division = ? AND b.status = 'active' ORDER BY b.verified DESC LIMIT 30",
            's', [$selectedDiv]
        );
    }
}

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
    <p class="text-xs uppercase tracking-widest font-bold text-primary">Discover</p>
    <h1 class="font-serif text-3xl md:text-4xl font-bold mt-2">Discover Bangladesh</h1>
    <p class="text-on-surface-variant mt-2 max-w-2xl">Explore women-led businesses across every division and district.</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 md:px-6 pb-12">
  <h2 class="font-bold mb-4">Browse by Division</h2>
  <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 mb-10">
    <?php foreach ($divisions as $d): ?>
      <a href="?div=<?= urlencode($d['name']) ?>" class="hm-card p-4 text-center <?= $selectedDiv === $d['name'] ? 'ring-2 ring-primary' : '' ?>">
        <span class="material-symbols-outlined text-primary">map</span>
        <div class="text-xs font-bold mt-1"><?= h($d['name']) ?></div>
      </a>
    <?php endforeach; ?>
  </div>

  <?php if ($selectedDiv): ?>
    <h2 class="font-bold mb-4">Districts in <?= h($selectedDiv) ?></h2>
    <?php if (empty($districts)): ?>
      <p class="text-on-surface-variant mb-8">No districts listed yet.</p>
    <?php else: ?>
      <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3 mb-10">
        <?php foreach ($districts as $ds): ?>
          <div class="bg-white border border-outline/40 rounded-2xl p-3 text-sm font-semibold text-on-surface text-center">
            <?= h($ds['name']) ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <h2 class="font-bold mb-4">Businesses in <?= h($selectedDiv) ?></h2>
    <?php if (empty($biz)): ?>
      <p class="text-on-surface-variant">No businesses listed yet in this division.</p>
    <?php else: ?>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($biz as $b): ?>
          <a href="seller-details.php?id=<?= (int)$b['id'] ?>" class="hm-card p-5">
            <div class="flex gap-3 items-center mb-3">
              <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-primary to-pink-300 text-white flex items-center justify-center text-xl font-bold">
                <?= strtoupper(substr($b['shop_name'], 0, 1)) ?>
              </div>
              <div>
                <h3 class="font-bold"><?= h($b['shop_name']) ?></h3>
                <p class="text-xs text-on-surface-variant"><?= h($b['district']) ?><?= $b['upazila'] ? ', ' . h($b['upazila']) : '' ?></p>
              </div>
            </div>
            <p class="text-sm text-on-surface line-clamp-2"><?= h($b['description']) ?></p>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>