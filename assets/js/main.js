/* =====================================================
   HerMart — Main JS
   Mobile menu, password eye toggle, cart helpers.
   ===================================================== */

(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
  }

  /**
   * Wrap bare <input type="password"> elements with a positioning wrap
   * and append an eye toggle button.
   */
  function initPasswordToggles() {
    var inputs = document.querySelectorAll('input[type="password"]:not([data-pw-skip])');
    inputs.forEach(function (input) {
      if (input.parentElement && input.parentElement.classList.contains('hm-pw-wrap')) return;

      var wrap = document.createElement('div');
      wrap.className = 'hm-pw-wrap';

      // Insert wrap before the input, then move input into it.
      input.parentNode.insertBefore(wrap, input);
      wrap.appendChild(input);

      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'hm-pw-toggle';
      btn.setAttribute('aria-label', 'Toggle password visibility');
      btn.innerHTML = '<span class="material-symbols-outlined">visibility</span>';

      btn.addEventListener('click', function () {
        if (input.type === 'password') {
          input.type = 'text';
          btn.innerHTML = '<span class="material-symbols-outlined">visibility_off</span>';
        } else {
          input.type = 'password';
          btn.innerHTML = '<span class="material-symbols-outlined">visibility</span>';
        }
        input.focus();
      });

      wrap.appendChild(btn);
    });
  }

  ready(function () {
    // Mobile nav toggle
    var btn = document.getElementById('hmMobileBtn');
    var menu = document.getElementById('hmMobileMenu');
    if (btn && menu) {
      btn.addEventListener('click', function () {
        menu.classList.toggle('hidden');
      });
    }

    // Password eye toggles
    initPasswordToggles();

    // Add-to-cart form submission (POST form to cart-action.php)
    document.querySelectorAll('[data-add-to-cart]').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        var productId = btn.getAttribute('data-product-id');
        var form = btn.closest('form');
        if (form) {
          form.submit();
        } else if (productId) {
          // Build a form on the fly and submit
          var qty = btn.getAttribute('data-qty') || '1';
          var f = document.createElement('form');
          f.method = 'POST';
          f.action = 'cart-action.php';
          f.innerHTML =
            '<input type="hidden" name="action" value="add">' +
            '<input type="hidden" name="product_id" value="' + productId + '">' +
            '<input type="hidden" name="quantity" value="' + qty + '">';
          document.body.appendChild(f);
          f.submit();
        }
      });
    });

    function showCartToast(message, success) {
      var toast = document.getElementById('hmCartToast');
      if (!toast) {
        toast = document.createElement('div');
        toast.id = 'hmCartToast';
        toast.setAttribute('role', 'status');
        toast.setAttribute('aria-live', 'polite');
        toast.className = 'fixed bottom-4 right-4 z-[100] max-w-sm rounded-xl px-4 py-3 text-sm font-semibold text-white shadow-lg';
        document.body.appendChild(toast);
      }
      toast.textContent = message;
      toast.classList.toggle('bg-emerald-700', success);
      toast.classList.toggle('bg-red-700', !success);
      toast.classList.remove('hidden');
      window.clearTimeout(toast.hideTimer);
      toast.hideTimer = window.setTimeout(function () {
        toast.classList.add('hidden');
      }, 3500);
    }

    document.querySelectorAll('[data-cart-add-form], [data-restock-form]').forEach(function (form) {
      form.addEventListener('submit', async function (event) {
        var submitter = event.submitter;
        if (submitter && (submitter.name === 'go_cart' || submitter.hasAttribute('data-go-cart'))) return;

        event.preventDefault();
        var button = submitter || form.querySelector('[data-cart-add]');
        if (button) {
          button.disabled = true;
          button.setAttribute('aria-busy', 'true');
        }

        try {
          var response = await fetch(form.getAttribute('action'), {
            method: 'POST',
            body: new FormData(form),
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
          });
          var contentType = response.headers.get('content-type') || '';
          if (!contentType.includes('application/json')) {
            var message = response.redirected && /login\.php/i.test(response.url)
              ? 'Please sign in to add products to your cart.'
              : 'Could not add this product. Please try again.';
            showCartToast(message, false);
            return;
          }

          var result = await response.json();
          showCartToast(result.message || 'Cart updated.', Boolean(result.success));
          if (result.success && result.stock !== undefined) {
            var card = form.closest('.product-card');
            var stockDisplay = card ? card.querySelector('[data-restock-stock]') : document.querySelector('[data-restock-stock]');
            if (stockDisplay) stockDisplay.textContent = 'In stock: ' + result.stock;
          }
          if (result.success) {
            document.querySelectorAll('[data-cart-count]').forEach(function (count) {
              count.textContent = result.cart_count;
              count.classList.toggle('hidden', Number(result.cart_count) < 1);
            });
          }
        } catch (error) {
          showCartToast('Could not connect to the shop. Please try again.', false);
        } finally {
          if (button) {
            button.disabled = false;
            button.removeAttribute('aria-busy');
          }
        }
      });
    });

    // Quantity controls inside cart line items
    document.querySelectorAll('.hm-qty-dec').forEach(function (b) {
      b.addEventListener('click', function () {
        var i = b.closest('[data-line]');
        var input = i && i.querySelector('input[name="quantity"]');
        if (!input) return;
        input.value = Math.max(1, (parseInt(input.value) || 1) - 1);
      });
    });
    document.querySelectorAll('.hm-qty-inc').forEach(function (b) {
      b.addEventListener('click', function () {
        var i = b.closest('[data-line]');
        var input = i && i.querySelector('input[name="quantity"]');
        if (!input) return;
        input.value = Math.max(1, (parseInt(input.value) || 1) + 1);
      });
    });

    // Simple form required-field inline error
    document.querySelectorAll('form[data-validate]').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        var ok = true;
        form.querySelectorAll('[required]').forEach(function (el) {
          if (!el.value || (el.type === 'email' && !/^\S+@\S+\.\S+$/.test(el.value))) {
            el.classList.add('border-red-500');
            ok = false;
          } else {
            el.classList.remove('border-red-500');
          }
        });
        if (!ok) {
          e.preventDefault();
          var first = form.querySelector('.border-red-500');
          if (first) first.focus();
        }
      });
    });

    // Role tab cards highlight (login/register pages)
    document.querySelectorAll('[data-role-card]').forEach(function (card) {
      card.addEventListener('click', function () {
        document.querySelectorAll('[data-role-card]').forEach(function (c) {
          c.classList.remove('hm-role-card-active');
        });
        card.classList.add('hm-role-card-active');
      });
    });
  });
})();
