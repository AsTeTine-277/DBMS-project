<?php
$page_title = 'Verification — HerMart';
require_once __DIR__ . '/includes/init.php';
include __DIR__ . '/includes/header.php';
?>

<section class="bg-gradient-to-b from-primary-light/40 to-transparent">
  <div class="max-w-7xl mx-auto px-4 md:px-6 py-10">
    <p class="text-xs uppercase tracking-widest font-bold text-primary">Verification</p>
    <h1 class="font-serif text-3xl md:text-4xl font-bold mt-2">Seller Verification</h1>
    <p class="text-on-surface-variant mt-2 max-w-2xl">Verified sellers gain a green checkmark, more visibility and customer trust.</p>
  </div>
</section>

<section class="max-w-5xl mx-auto px-4 md:px-6 pb-12 grid md:grid-cols-2 gap-6">
  <div class="bg-white rounded-3xl border border-outline/40 p-6 md:p-8">
    <h2 class="font-bold text-xl mb-3">How to get verified</h2>
    <ol class="space-y-4 text-sm">
      <li class="flex gap-3"><span class="font-bold text-primary text-lg">1.</span><div><strong>Sign up</strong> as a seller with your real name and phone number.</div></li>
      <li class="flex gap-3"><span class="font-bold text-primary text-lg">2.</span><div>Provide <strong>NID number</strong> and <strong>business details</strong> (brand, address, products).</div></li>
      <li class="flex gap-3"><span class="font-bold text-primary text-lg">3.</span><div>Upload <strong>NID photo</strong> and a <strong>business photo</strong> for review.</div></li>
      <li class="flex gap-3"><span class="font-bold text-primary text-lg">4.</span><div>Our team verifies within <strong>2–3 business days</strong>.</div></li>
    </ol>
    <a href="register.php?role=seller" class="hm-btn hm-btn-primary mt-6 inline-flex">Start Registration</a>
  </div>

  <div class="bg-white rounded-3xl border border-outline/40 p-6 md:p-8">
    <h2 class="font-bold text-xl mb-3">Benefits of verification</h2>
    <ul class="space-y-3 text-sm">
      <li class="flex gap-2"><span class="material-symbols-outlined text-emerald-500">verified</span> Green verified badge on your profile</li>
      <li class="flex gap-2"><span class="material-symbols-outlined text-emerald-500">trending_up</span> Higher search ranking</li>
      <li class="flex gap-2"><span class="material-symbols-outlined text-emerald-500">workspace_premium</span> Featured in "Verified" category</li>
      <li class="flex gap-2"><span class="material-symbols-outlined text-emerald-500">support_agent</span> Priority customer support</li>
      <li class="flex gap-2"><span class="material-symbols-outlined text-emerald-500">payments</span> Faster payouts</li>
    </ul>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>