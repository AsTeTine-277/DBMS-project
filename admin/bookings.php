<?php
/**
 * HerMart — Admin: Bookings
 *
 * Read-mostly view of every vendor booking on the platform. Admins can
 * change status as last resort; primary triage lives in
 * `seller/bookings.php` for the vendor.
 */
$page_title = 'Bookings — Admin — HerMart';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/admin-auth.php';

admin_only();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bid = safe_int($_POST['booking_id'] ?? 0);
    $new = $_POST['status'] ?? '';
    if ($bid > 0 && in_array($new, ['pending','accepted','rejected','completed','cancelled'], true)) {
        db_execute("UPDATE bookings SET status = ? WHERE id = ?", 'si', [$new, $bid]);
        flash_set('success', 'Booking updated.');
    }
    redirect('admin/bookings.php');
}

$rows = db_select(
    "SELECT b.*, sv.name AS service_name, u.name AS customer_name, biz.shop_name AS vendor_name
     FROM bookings b
     LEFT JOIN services   sv  ON sv.id  = b.service_id
     LEFT JOIN users      u   ON u.id   = b.customer_id
     LEFT JOIN businesses biz ON biz.id = b.vendor_id
     ORDER BY b.id DESC LIMIT 100"
);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/_layout_top.php';
?>

<section class="hm-container py-6">
  <div class="bg-white rounded-3xl border border-outline/40 p-6">
    <h2 class="font-bold text-xl mb-4">Bookings</h2>

    <?php if (empty($rows)): ?>
      <p class="text-sm text-on-surface-variant py-8 text-center">No bookings yet.</p>
    <?php else: ?>
      <table class="w-full text-sm">
        <thead class="text-left text-xs uppercase text-on-surface-variant border-b border-outline/40">
          <tr>
            <th class="py-2">Customer</th>
            <th>Service</th>
            <th>Vendor</th>
            <th>Date / Time</th>
            <th>Location</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr class="border-b border-outline/20">
              <td class="py-2"><?= h($r['customer_name']) ?></td>
              <td><?= h($r['service_name'] ?: $r['service_name'] ?? '—') ?><div class="text-xs text-on-surface-variant"><?= h($r['event_type']) ?></div></td>
              <td><?= h($r['vendor_name']) ?></td>
              <td class="text-xs"><?= h($r['booking_date']) ?><div class="text-on-surface-variant"><?= h($r['booking_time']) ?></div></td>
              <td class="text-xs"><?= h($r['location']) ?></td>
              <td><span class="px-2 py-1 rounded-full text-[11px] font-semibold <?= status_pill_class($r['status'], 'booking') ?>"><?= format_status_label($r['status']) ?></span></td>
              <td>
                <div class="admin-actions">
                  <form method="post">
                    <input type="hidden" name="booking_id" value="<?= (int)$r['id'] ?>">
                    <select name="status" class="hm-input text-xs py-1">
                      <?php foreach (['pending','accepted','rejected','completed','cancelled'] as $s): ?>
                        <option value="<?= $s ?>" <?= $r['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
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
