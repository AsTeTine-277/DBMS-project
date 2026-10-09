<?php
/**
 * HerMart — Admin: Deliveries
 *
 * Lists every delivery row and lets admins update tracking status.
 */
$page_title = 'Deliveries — Admin — HerMart';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/admin-auth.php';

admin_only();

$status = $_GET['status'] ?? '';
if (!in_array($status, ['pending','processing','shipped','out_for_delivery','delivered','cancelled'], true)) {
    $status = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $did = safe_int($_POST['delivery_id'] ?? 0);
    $new = $_POST['status'] ?? '';
    if ($did > 0 && in_array($new, ['pending','processing','shipped','out_for_delivery','delivered','cancelled'], true)) {
        $deliveredAt = ($new === 'delivered') ? date('Y-m-d H:i:s') : null;
        db_execute(
            "UPDATE deliveries SET status = ?, delivered_at = ? WHERE id = ?",
            'ssi', [$new, $deliveredAt, $did]
        );
        // Mirror onto the order.
        db_execute("UPDATE orders SET order_status = ? WHERE id = (SELECT order_id FROM deliveries WHERE id = ?)", 'si', [$new, $did]);
        flash_set('success', 'Delivery updated.');
    }
    redirect('admin/deliveries.php' . ($status !== '' ? '?status=' . urlencode($status) : ''));
}

$sql = "SELECT d.*, o.order_number, u.name AS customer_name
        FROM deliveries d
        LEFT JOIN orders o ON o.id = d.order_id
        LEFT JOIN users  u ON u.id = d.customer_id";
$args = []; $types = '';
if ($status !== '') {
    $sql .= ' WHERE d.status = ?';
    $args[] = $status; $types .= 's';
}
$sql .= ' ORDER BY d.id DESC LIMIT 50';
$rows = $args ? db_select($sql, $types, $args) : db_select($sql);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/_layout_top.php';
?>

<section class="hm-container py-6">
  <div class="bg-white rounded-3xl border border-outline/40 p-6">
    <div class="flex items-end justify-between gap-3 mb-4">
      <h2 class="font-bold text-xl">Deliveries</h2>
      <form method="get" class="text-xs">
        <label class="hm-label">Status</label>
        <select name="status" onchange="this.form.submit()" class="hm-input">
          <option value="">All</option>
          <?php foreach (['pending','processing','shipped','out_for_delivery','delivered','cancelled'] as $s): ?>
            <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>

    <?php if (empty($rows)): ?>
      <p class="text-sm text-on-surface-variant py-8 text-center">No deliveries match.</p>
    <?php else: ?>
      <table class="w-full text-sm">
        <thead class="text-left text-xs uppercase text-on-surface-variant border-b border-outline/40">
          <tr>
            <th class="py-2">Order</th>
            <th>Customer</th>
            <th>Tracking</th>
            <th>Address</th>
            <th>ETA</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr class="border-b border-outline/20">
              <td class="py-2"><a href="order-details.php?id=<?= (int)$r['order_id'] ?>" class="text-primary font-semibold hover:underline"><?= h($r['order_number']) ?></a></td>
              <td><?= h($r['customer_name']) ?></td>
              <td class="text-xs font-mono"><?= h($r['tracking_number']) ?></td>
              <td class="text-xs"><?= h($r['address']) ?></td>
              <td class="text-xs"><?= h($r['estimated_delivery'] ?: '—') ?></td>
              <td><span class="px-2 py-1 rounded-full text-[11px] font-semibold <?= status_pill_class($r['status'], 'order') ?>"><?= format_status_label($r['status']) ?></span></td>
              <td>
                <div class="admin-actions">
                  <form method="post">
                    <input type="hidden" name="delivery_id" value="<?= (int)$r['id'] ?>">
                    <select name="status" class="hm-input text-xs py-1">
                      <?php foreach (['pending','processing','shipped','out_for_delivery','delivered','cancelled'] as $s): ?>
                        <option value="<?= $s ?>" <?= $r['status'] === $s ? 'selected' : '' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option>
                      <?php endforeach; ?>
                    </select>
                    <button class="hm-btn hm-btn-outline py-1 px-2 text-xs mt-1">Save</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</section>

<?php
include __DIR__ . '/_layout_bottom.php';
include __DIR__ . '/../includes/footer.php';
?>
