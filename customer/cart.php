<?php
$page_title = 'My Cart — HerMart';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/customer-auth.php';

customer_only();

$user = current_user();
$cart = current_cart($user['id']);
$items = cart_items($user['id']);

$subtotal = 0;
foreach ($items as $it) {
    $subtotal += (float)$it['unit_price'] * (int)$it['quantity'];
}
$delivery = $subtotal > 0 ? 80 : 0;
$total = $subtotal + $delivery;

include __DIR__ . '/../includes/header.php';
?>

<section class="bg-gradient-to-b from-primary-light/40 to-transparent">
  <div class="max-w-7xl mx-auto px-4 md:px-6 py-10">
    <p class="text-xs uppercase tracking-widest font-bold text-primary">Your Cart</p>
    <h1 class="font-serif text-3xl md:text-4xl font-bold mt-2">Shopping Cart</h1>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 md:px-6 pb-12 grid lg:grid-cols-3 gap-6">

  <div class="lg:col-span-2">
    <?php if (empty($items)): ?>
      <div class="bg-white rounded-3xl border border-outline/40 p-10 text-center">
        <span class="material-symbols-outlined text-primary text-5xl">shopping_cart</span>
        <h2 class="font-bold text-xl mt-3">Your cart is empty</h2>
        <p class="text-on-surface-variant mt-1">Browse products and add your favorites.</p>
        <a href="products.php" class="hm-btn hm-btn-primary mt-4 inline-flex">
          <span class="material-symbols-outlined text-base">shopping_bag</span>
          Start Shopping
        </a>
      </div>
    <?php else: ?>
      <div class="bg-white rounded-3xl border border-outline/40 p-6">
        <div class="flex items-center justify-between mb-3">
          <h2 class="font-bold text-lg">Items (<?= count($items) ?>)</h2>
          <div class="flex items-center gap-4">
            <label class="flex items-center gap-2 text-xs text-on-surface-variant">
              <input id="select-all-cart-items" type="checkbox" checked class="rounded border-outline text-primary focus:ring-primary">
              Select all
            </label>
            <form method="post" action="cart-action.php" class="inline">
              <input type="hidden" name="action" value="clear">
              <button type="submit" class="text-xs text-red-500 hover:underline" onclick="return confirm('Clear cart?');">Clear cart</button>
            </form>
          </div>
        </div>
        <div class="divide-y divide-outline/40">
          <?php foreach ($items as $item): ?>
            <div class="py-4 flex items-center gap-4" data-line>
              <input type="checkbox" name="selected_item_ids[]" value="<?= (int)$item['id'] ?>" form="checkout-selection-form" checked data-checkout-item data-line-total="<?= h(number_format((float)$item['unit_price'] * (int)$item['quantity'], 2, '.', '')) ?>" aria-label="Select <?= h($item['name']) ?> for checkout" class="shrink-0 rounded border-outline text-primary focus:ring-primary">
              <a href="product-details.php?id=<?= (int)$item['product_id'] ?>" class="shrink-0">
                <img <?= product_image_attrs($item['image']) ?> class="w-16 h-16 rounded-xl object-cover border border-outline/40" alt="<?= h($item['name']) ?>">
              </a>
              <div class="flex-1 min-w-0">
                <h3 class="font-bold text-sm truncate"><?= h($item['name']) ?></h3>
                <p class="text-xs text-on-surface-variant">
                  <?= h($item['shop_name'] ?? 'HerMart Seller') ?> · <?= money_tk($item['unit_price']) ?> each
                </p>
                <?php if ((int)$item['stock'] < 1 || $item['status'] !== 'active'): ?>
                  <p class="text-xs text-red-600 font-semibold mt-1">Currently unavailable</p>
                <?php endif; ?>
              </div>
              <form method="post" action="cart-action.php" class="flex items-center gap-2">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>">
                <button type="button" class="hm-qty-dec w-8 h-8 rounded-full border border-outline text-on-surface-variant" onclick="var i=this.nextElementSibling;var m=parseInt(i.min||1,10);if(parseInt(i.value,10)>m){i.value=parseInt(i.value,10)-1;i.form.requestSubmit();}">-</button>
                <input type="number" name="quantity" value="<?= (int)$item['quantity'] ?>" min="1" max="<?= max(1,(int)$item['stock']) ?>" class="w-14 text-center border border-outline rounded-md py-1">
                <button type="button" class="hm-qty-inc w-8 h-8 rounded-full border border-outline text-on-surface-variant" onclick="var i=this.previousElementSibling;var m=parseInt(i.max||9999,10);if(parseInt(i.value,10)<m){i.value=parseInt(i.value,10)+1;i.form.requestSubmit();}">+</button>
                <button class="ml-2 text-primary font-bold text-sm" type="submit">Update</button>
              </form>
              <div class="text-right shrink-0">
                <div class="font-bold text-primary"><?= money_tk((float)$item['unit_price'] * (int)$item['quantity']) ?></div>
                <form method="post" action="cart-action.php" class="inline">
                  <input type="hidden" name="action" value="remove">
                  <input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>">
                  <button type="submit" class="text-xs text-red-500 hover:underline">Remove</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <aside class="lg:col-span-1">
    <div class="bg-white rounded-3xl border border-outline/40 p-6 sticky top-24">
      <h2 class="font-bold text-lg mb-3">Order Summary</h2>
      <div class="space-y-2 text-sm">
        <div class="flex justify-between"><span class="text-on-surface-variant">Selected items</span><span data-selected-count><?= count($items) ?></span></div>
        <div class="flex justify-between"><span class="text-on-surface-variant">Subtotal</span><span data-selected-subtotal><?= money_tk($subtotal) ?></span></div>
        <div class="flex justify-between"><span class="text-on-surface-variant">Delivery</span><span data-selected-delivery><?= money_tk($delivery) ?></span></div>
        <div class="flex justify-between font-bold text-lg border-t border-outline/40 pt-3 mt-3">
          <span>Total</span><span class="text-primary" data-selected-total><?= money_tk($total) ?></span>
        </div>
      </div>

      <form method="post" action="checkout.php" id="checkout-selection-form">
        <input type="hidden" name="start_checkout" value="1">
        <button type="submit" class="hm-btn hm-btn-accent w-full justify-center mt-5" data-checkout-button>
          <span class="material-symbols-outlined text-base">lock</span>
          Checkout selected items
        </button>
      </form>
      <a href="products.php" class="hm-btn hm-btn-outline w-full justify-center mt-2">
        Continue Shopping
      </a>
    </div>
  </aside>
</section>

<script>
  const checkoutItems = Array.from(document.querySelectorAll('[data-checkout-item]'));
  const selectAllItems = document.getElementById('select-all-cart-items');
  const checkoutButton = document.querySelector('[data-checkout-button]');
  const formatTk = amount => '৳' + Math.round(amount).toLocaleString('en-US');

  function updateCheckoutSelection() {
    const selectedItems = checkoutItems.filter(item => item.checked);
    const subtotal = selectedItems.reduce((sum, item) => sum + Number(item.dataset.lineTotal), 0);
    const delivery = selectedItems.length ? 80 : 0;
    document.querySelector('[data-selected-count]').textContent = selectedItems.length;
    document.querySelector('[data-selected-subtotal]').textContent = formatTk(subtotal);
    document.querySelector('[data-selected-delivery]').textContent = formatTk(delivery);
    document.querySelector('[data-selected-total]').textContent = formatTk(subtotal + delivery);
    checkoutButton.disabled = selectedItems.length === 0;
    selectAllItems.checked = selectedItems.length === checkoutItems.length;
    selectAllItems.indeterminate = selectedItems.length > 0 && selectedItems.length < checkoutItems.length;
  }

  checkoutItems.forEach(item => item.addEventListener('change', updateCheckoutSelection));
  selectAllItems.addEventListener('change', () => {
    checkoutItems.forEach(item => { item.checked = selectAllItems.checked; });
    updateCheckoutSelection();
  });
  updateCheckoutSelection();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
