<?php
$page_title = 'Vendor Booking — HerMart';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/customer-auth.php';

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    customer_only();

    $service_id   = safe_int($_POST['service_id'] ?? 0);
    $vendor_id    = safe_int($_POST['vendor_id'] ?? 0);
    $event_type   = trim($_POST['event_type'] ?? '');
    $event_date   = $_POST['event_date'] ?? '';
    $event_time   = $_POST['event_time'] ?? '';
    $location     = trim($_POST['location'] ?? '');
    $notes        = trim($_POST['notes'] ?? '');

    if ($service_id && $vendor_id && $event_type !== '' && $event_date !== '' && $location !== '') {
        // Resolve service name for denormalization
        $svc = db_select_one("SELECT name FROM services WHERE id = ?", 'i', [$service_id]);
        $service_name = $svc['name'] ?? null;
        $ok = db_execute(
            "INSERT INTO bookings (customer_id, vendor_id, service_id, service_name, event_type, booking_date, booking_time, location, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            'iiissssss',
            [current_user()['id'], $vendor_id, $service_id, $service_name, $event_type, $event_date, $event_time, $location, $notes]
        );
        if ($ok) {
            $bookingId = db_insert_id();
            // Notify the vendor
            $biz = business_by_id($vendor_id);
            if ($biz && $biz['owner_id']) {
                db_execute(
                    "INSERT INTO notifications (user_id, type, title, body, link) VALUES (?, 'booking', ?, ?, ?)",
                    'isss',
                    [$biz['owner_id'], 'New booking request', 'You have a new booking for ' . $event_type, 'seller/bookings.php']
                );
            }
            flash_set('success', 'Booking submitted! The vendor will contact you shortly.');
            redirect('customer/profile.php');
        } else {
            $error = 'Could not save your booking. Please try again.';
        }
    } else {
        $error = 'Please complete all required fields.';
    }
}

$selectedService = safe_int($_GET['service'] ?? 0);
$services = services_all();
$vendors  = db_select("SELECT id, shop_name AS brand, district FROM businesses WHERE status = 'active' ORDER BY verified DESC LIMIT 20");

include __DIR__ . '/../includes/header.php';
?>

<section class="bg-gradient-to-b from-primary-light/40 to-transparent">
  <div class="max-w-7xl mx-auto px-4 md:px-6 py-10">
    <div class="mb-3">
      <button type="button" onclick="if(history.length>1){history.back();}else{location.href='services.php';}" class="hm-btn-back" aria-label="Go back">
        <span class="material-symbols-outlined">arrow_back</span>
        Back
      </button>
    </div>
    <p class="text-xs uppercase tracking-widest font-bold text-primary">Vendor Booking</p>
    <h1 class="font-serif text-3xl md:text-4xl font-bold mt-2">Book a women vendor</h1>
    <p class="text-on-surface-variant mt-2 max-w-2xl">Reserve a verified women vendor for events, catering, tailoring, mehendi and more.</p>
  </div>
</section>

<?php if ($error): ?>
<div class="max-w-7xl mx-auto px-4">
  <div class="rounded-xl px-4 py-3 text-sm font-medium border bg-red-50 text-red-700 border-red-200 mb-4"><?= h($error) ?></div>
</div>
<?php endif; ?>

<section class="max-w-7xl mx-auto px-4 md:px-6 pb-12 grid lg:grid-cols-3 gap-6">

  <div class="lg:col-span-2 bg-white rounded-3xl border border-outline/40 p-6 md:p-8">
    <h2 class="font-bold text-xl mb-4">Booking details</h2>

    <?php if (!is_logged_in()): ?>
      <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-4 text-amber-800 text-sm">
        Please <a href="../login.php?redirect=customer/vendor-booking.php" class="font-bold underline">sign in</a> to submit a booking. You can still browse vendors below.
      </div>
    <?php endif; ?>

    <form method="post" class="space-y-4" data-validate>
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="hm-label">Service *</label>
          <select name="service_id" required class="hm-select">
            <option value="">Select service</option>
            <?php foreach ($services as $s): ?>
              <option value="<?= (int)$s['id'] ?>" <?= $selectedService === (int)$s['id'] ? 'selected' : '' ?>><?= h($s['name']) ?> — <?= money_tk($s['base_price']) ?>+</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="hm-label">Vendor *</label>
          <select name="vendor_id" required class="hm-select">
            <option value="">Select vendor</option>
            <?php foreach ($vendors as $v): ?>
              <option value="<?= (int)$v['id'] ?>"><?= h($v['brand']) ?> (<?= h($v['district']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div>
        <label class="hm-label">Event Type *</label>
        <input name="event_type" type="text" required class="hm-input" placeholder="e.g. Wedding Mehendi, Birthday Catering">
      </div>

      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="hm-label">Date *</label>
          <input name="event_date" type="date" required min="<?= date('Y-m-d') ?>" class="hm-input">
        </div>
        <div>
          <label class="hm-label">Time</label>
          <input name="event_time" type="time" class="hm-input">
        </div>
      </div>

      <div>
        <label class="hm-label">Location *</label>
        <input name="location" type="text" required class="hm-input" placeholder="Venue name, address">
      </div>

      <div>
        <label class="hm-label">Notes / Special Requests</label>
        <textarea name="notes" class="hm-textarea" rows="4" placeholder="How many guests, vegetarian menu, theme, etc."></textarea>
      </div>

      <button type="submit" class="hm-btn hm-btn-primary" <?= !is_logged_in() ? 'disabled' : '' ?>>
        <span class="material-symbols-outlined text-base">send</span>
        Submit Booking
      </button>
    </form>
  </div>

  <aside class="space-y-4">
    <div class="bg-white rounded-3xl border border-outline/40 p-6">
      <h3 class="font-bold mb-3 flex items-center gap-2">
        <span class="material-symbols-outlined text-primary">workspace_premium</span>
        Verified Vendors
      </h3>
      <p class="text-sm text-on-surface-variant">All vendors on HerMart are verified women entrepreneurs.</p>
    </div>
    <div class="bg-primary text-white rounded-3xl p-6">
      <h3 class="font-bold mb-2">How booking works</h3>
      <ol class="space-y-3 text-sm text-pink-100/90">
        <li class="flex gap-3"><span class="font-bold text-white">1.</span> Submit your booking request</li>
        <li class="flex gap-3"><span class="font-bold text-white">2.</span> Vendor confirms availability</li>
        <li class="flex gap-3"><span class="font-bold text-white">3.</span> Pay advance or cash on event</li>
        <li class="flex gap-3"><span class="font-bold text-white">4.</span> Enjoy your event</li>
      </ol>
    </div>
  </aside>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
