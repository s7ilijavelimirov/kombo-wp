(function ($) {
  'use strict';
  $(function () {
    $('input[name="billing_pak"]').on('input', function () {
      this.value = this.value.replace(/\D/g, '');
      if (this.value.length > 6) {
        this.value = this.value.slice(0, 6);
      }
    });
  });
})(jQuery);
