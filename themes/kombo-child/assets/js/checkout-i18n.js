(function ($) {
  'use strict';
  var L = typeof komboCheckoutI18n === 'undefined' ? {} : komboCheckoutI18n;

  $(function () {
    $(document.body).on('updated_checkout', function () {
      if (L.shipping) {
        $('.woocommerce-shipping-totals th').text(L.shipping);
      }
      if (L.total) {
        $('.order-total th').text(L.total);
      }
      if (L.subtotal) {
        $('.cart-subtotal th').text(L.subtotal);
      }
      if (L.place_order) {
        $('#place_order').text(L.place_order);
      }
    });

    var isTranslating = false;
    function translateCheckoutLabels() {
      if (isTranslating) {
        return;
      }
      isTranslating = true;
      if (L.shipping) {
        $('.woocommerce-shipping-totals th').text(L.shipping);
      }
      if (L.total) {
        $('.order-total th').text(L.total);
      }
      if (L.subtotal) {
        $('.cart-subtotal th').text(L.subtotal);
      }
      if (L.place_order) {
        $('#place_order').text(L.place_order);
      }
      setTimeout(function () {
        isTranslating = false;
      }, 1000);
    }

    $(document.body).on('updated_checkout payment_method_selected update_checkout', function () {
      translateCheckoutLabels();
    });
    translateCheckoutLabels();

    var variationsTranslations = L.variations || {};
    function translateVariationText(text) {
      Object.keys(variationsTranslations).forEach(function (key) {
        if (text.trim() === key) {
          text = variationsTranslations[key];
        }
      });
      Object.keys(variationsTranslations).forEach(function (key) {
        if (text.indexOf(key) !== -1) {
          text = text.replace(key, variationsTranslations[key]);
        }
      });
      return text;
    }

    $(document.body).on('updated_checkout', function () {
      setTimeout(function () {
        $('.product-name').contents().filter(function () {
          return this.nodeType === 3;
        }).each(function () {
          this.nodeValue = translateVariationText(this.nodeValue);
        });
        $('.product-name strong.product-quantity').contents().filter(function () {
          return this.nodeType === 3;
        }).each(function () {
          this.nodeValue = translateVariationText(this.nodeValue);
        });
        $('.variation dt').contents().filter(function () {
          return this.nodeType === 3;
        }).each(function () {
          this.nodeValue = translateVariationText(this.nodeValue);
        });
        $('.variation dd p').contents().filter(function () {
          return this.nodeType === 3;
        }).each(function () {
          this.nodeValue = translateVariationText(this.nodeValue);
        });
      }, 100);
    });

    var checkoutReplacements = L.checkoutTable || {};
    $(document.body).on('updated_checkout', function () {
      Object.keys(checkoutReplacements).forEach(function (key) {
        $('*:contains("' + key + '")').each(function () {
          if ($(this).children().length === 0) {
            $(this).text($(this).text().replace(key, checkoutReplacements[key]));
          }
        });
      });
      if (L.price_notice) {
        $('.price-notice').text(L.price_notice);
      }
      if (L.terms) {
        $('#terms').text(L.terms);
      }
    });
  });
})(jQuery);
