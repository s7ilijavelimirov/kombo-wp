(function ($) {
  'use strict';
  var urls = typeof komboCartUrls === 'undefined' ? {} : komboCartUrls;

  $(function () {
    var langAttr = $('html').attr('lang');
    var currentLang = langAttr ? langAttr.split('-')[0] : 'sr';

    function updateCartUrls() {
      var u = urls[currentLang];
      if (!u) {
        return;
      }
      var cartButton = $('.xoo-wsc-ft-btn-cart');
      var checkoutButton = $('.xoo-wsc-ft-btn-checkout');
      if (cartButton.length) {
        cartButton.attr('href', u.cart);
      }
      if (checkoutButton.length) {
        checkoutButton.attr('href', u.checkout);
      }
    }

    var observer = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        if (mutation.addedNodes.length) {
          if ($('.xoo-wsc-ft-btn-cart').length) {
            updateCartUrls();
          }
        }
      });
    });
    observer.observe(document.body, { childList: true, subtree: true });
    $(document.body).on('xoo_wsc_cart_opened updated_cart_totals updated_checkout', function () {
      setTimeout(updateCartUrls, 100);
    });
    updateCartUrls();
    setTimeout(updateCartUrls, 1000);
  });
})(jQuery);
