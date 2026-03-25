(function ($) {
  'use strict';
  $(function () {
    if ($('.button-proceed').length) {
      $('.button-proceed').val('Nastavi plaćanje / Continue payment');
    }
    var translations = {
      'Broj narudžbine:': 'Broj narudžbine / Order number:',
      'Datum:': 'Datum / Date:',
      'Ukupno:': 'Ukupno / Total:',
      'Način plaćanja:': 'Način plaćanja / Payment method:',
      'Plaćanje platnom karticom': 'Plaćanje platnom karticom / Card payment'
    };
    $('.order_details li').each(function () {
      var $li = $(this);
      var firstTextNode = Array.from($li[0].childNodes).find(function (node) {
        return node.nodeType === 3 && node.textContent.trim();
      });
      if (firstTextNode && translations[firstTextNode.textContent.trim()]) {
        firstTextNode.textContent = translations[firstTextNode.textContent.trim()];
      }
    });
    $('.order_details li.method strong').text('Plaćanje platnom karticom / Card payment');
  });
})(jQuery);
