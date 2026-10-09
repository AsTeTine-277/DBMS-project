<?php
$page_title = 'Sellers — HerMart';
require_once __DIR__ . '/../includes/init.php';

$q    = trim($_GET['q'] ?? '');
$cat  = safe_int($_GET['cat'] ?? 0);
$div  = $_GET['div'] ?? '';

$where = [];
$args  = [];
$types = '';

$sql = "SELECT b.*, c.name AS category_name,
               (SELECT COUNT(*) FROM products p WHERE p.seller_id = b.id AND p.status = 'active') AS product_count
        FROM businesses b
        LEFT JOIN categories c ON c.id = b.category_id
        WHERE b.status = 'active'";

if ($q !== '') {
    $where[] = "(b.shop_name LIKE ? OR b.description LIKE ? OR b.address LIKE ?)";
    array_push($args, "%$q%", "%$q%", "%$q%"); $types .= 'sss';
}
if ($cat) {
    $where[] = "b.category_id = ?";
    $args[] = $cat; $types .= 'i';
}
if ($div !== '') {
    $where[] = "b.division = ?";
    $args[] = $div; $types .= 's';
}
if ($where) $sql .= ' AND ' . implode(' AND ', $where);
$sql .= ' ORDER BY b.verified DESC, b.id ASC LIMIT 60';

$sellers = db_select($sql, $types, $args);
$cats = categories_all();
$divisions = regions_by_type('division');

include __DIR__ . '/../includes/header.php';
?>

<section class="bg-gradient-to-b from-primary-light/40 to-transparent">
  <div class="max-w-7xl mx-auto px-4 md:px-6 py-10">
    <p class="text-xs uppercase tracking-widest font-bold text-primary">Our Sellers</p>
    <h1 class="font-serif text-3xl md:text-4xl font-bold mt-2">Meet women entrepreneurs</h1>
    <p class="text-on-surface-variant mt-2 max-w-2xl">Browse businesses owned by women artisans across Bangladesh.</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 md:px-6 pb-12 grid lg:grid-cols-4 gap-6">
  <aside class="lg:col-span-1">
    <form method="get" class="bg-white rounded-3xl border border-outline/40 p-5 sticky top-24">
      <h3 class="font-bold mb-3">Filter Sellers</h3>
      <label class="hm-label">Search</label>
      <input type="text" name="q" value="<?= h($q) ?>" placeholder="Brand or product..." class="hm-input mb-3">
      <label class="hm-label">Category</label>
      <select name="cat" class="hm-select mb-3">
        <option value="0">All</option>
        <?php foreach ($cats as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= $cat === (int)$c['id'] ? 'selected' : '' ?>><?= h($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <label class="hm-label">Division</label>
      <select name="div" class="hm-select mb-3">
        <option value="">All</option>
        <?php foreach ($divisions as $d): ?>
          <option value="<?= h($d['name']) ?>" <?= $div === $d['name'] ? 'selected' : '' ?>><?= h($d['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="hm-btn hm-btn-primary w-full justify-center">
        <span class="material-symbols-outlined text-base">search</span> Apply
      </button>
    </form>
  </aside>

  <div class="lg:col-span-3">
    <p class="text-sm text-on-surface-variant mb-4"><?= count($sellers) ?> sellers</p>
    <div class="grid sm:grid-cols-2 gap-5">
      <?php foreach ($sellers as $s): ?>
        <a href="seller-details.php?id=<?= (int)$s['id'] ?>" class="hm-card p-5 flex gap-4 items-start">
          <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-primary to-pink-300 text-white flex items-center justify-center text-2xl font-bold shrink-0">
            <?= strtoupper(substr($s['shop_name'], 0, 1)) ?>
          </div>
          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2 flex-wrap">
              <h3 class="font-bold truncate"><?= h($s['shop_name']) ?></h3>
              <?php if ($s['verified']): ?>
                <span class="text-emerald-600 text-xs flex items-center gap-0.5"><span class="material-symbols-outlined text-sm">verified</span> Verified</span>
              <?php endif; ?>
            </div>
            <p class="text-xs text-on-surface-variant mt-1"><?= h($s['category_name'] ?? '') ?></p>
            <p class="text-xs text-on-surface-variant"><?= h($s['district']) ?>, <?= h($s['division']) ?></p>
            <p class="text-sm text-on-surface mt-2 line-clamp-2"><?= h($s['description']) ?></p>
            <p class="text-xs text-primary font-bold mt-2"><?= (int)$s['product_count'] ?> products</p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>