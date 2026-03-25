jQuery(document).ready(function ($) {
    function updateCartCount() {
        $.ajax({
            url: cartCountAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'get_cart_count',
                nonce: cartCountAjax.nonce,
            },
            success: function (response) {
                if (response.success) {
                    const cartCount = response.data;
                    if (cartCount > 0) {
                        $('.cart-count').text(cartCount).show();
                    } else {
                        $('.cart-count').hide();
                    }
                }
            },
        });
    }

    // Trigger on WooCommerce events
    $(document.body).on('added_to_cart removed_from_cart updated_cart_totals', function () {
        updateCartCount();
    });

    // Initial update on page load
    updateCartCount();
});