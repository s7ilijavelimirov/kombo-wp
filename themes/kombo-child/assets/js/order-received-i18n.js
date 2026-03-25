(function ($) {
  'use strict';
  var pack = typeof komboOrderReceivedI18n === 'undefined' ? {} : komboOrderReceivedI18n;
  var variations = pack.variations || {};

  function translateText(text) {
    Object.keys(variations).forEach(function (key) {
      if (text.trim() === key) {
        text = variations[key];
      }
    });
    Object.keys(variations).forEach(function (key) {
      if (text.indexOf(key) !== -1) {
        text = text.replace(key, variations[key]);
      }
    });
    return text;
  }

  function translateOrderDetails() {
    $('.woocommerce-table__product-name').contents().filter(function () {
      return this.nodeType === 3;
    }).each(function () {
      this.nodeValue = translateText(this.nodeValue);
    });
    $('.woocommerce-table__product-name .wc-item-meta li').each(function () {
      var $item = $(this);
      $item.find('strong').contents().filter(function () {
        return this.nodeType === 3;
      }).each(function () {
        this.nodeValue = translateText(this.nodeValue);
      });
      $item.find('p').contents().filter(function () {
        return this.nodeType === 3;
      }).each(function () {
        this.nodeValue = translateText(this.nodeValue);
      });
    });
    $('.woocommerce-table th').each(function () {
      var $th = $(this);
      $th.text(translateText($th.text()));
    });
  }

  $(function () {
    translateOrderDetails();
  });
})(jQuery);
