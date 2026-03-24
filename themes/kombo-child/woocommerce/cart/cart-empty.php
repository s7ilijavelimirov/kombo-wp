<?php

/**
 * Empty cart page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart-empty.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined('ABSPATH') || exit;

/*
 * @hooked wc_empty_cart_message - 10
 */
do_action('woocommerce_cart_is_empty');

$current_lang = pll_current_language();

$home_url = pll_home_url($current_lang);

$home_button_text = '';
switch ($current_lang) {
    case 'sr':
        $home_button_text = 'Nazad na početnu stranu';
        break;
    case 'en':
        $home_button_text = 'Back to Home';
        break;
    case 'ru':
        $home_button_text = 'Вернуться домой';
        break;
    default:
        $home_button_text = 'Nazad na početnu stranu';
}
?>


<div class="empty-cart-buttons">
    <div class="empty-card-title-wrapper">
        <h1><?php echo pll__('Vaša korpa je prazna') ?></h1>
    </div>
    <div class="empty-cart-buttons-wrapper">
        <div class="button-wrapper">
            <a class="button-main" href="<?php echo esc_url($home_url); ?>">
                <span><?php echo pll__('Nazad na početnu') ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="32px" height="32px" viewBox="0 0 16 16" fill="#fff" class="bi bi-arrow-right-short">
                        <path fill-rule="evenodd" d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8z" />
                    </svg>
                </span>
            </a>
        </div>
        <div class="button-wrapper">
            <a class="button-main" href="<?php
                                            $current_language = pll_current_language();
                                            if ($current_language === 'sr') {
                                                echo get_site_url() . '/porucivanje';
                                            } elseif ($current_language === 'en') {
                                                echo get_site_url() . '/en/ordering';
                                            } elseif ($current_language === 'ru') {
                                                echo get_site_url() . '/ru/заказ';
                                            }
                                            ?>">
                <span><?php echo pll__('Naruči Kombo paket') ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="32px" height="32px" viewBox="0 0 16 16" fill="#fff" class="bi bi-arrow-right-short">
                        <path fill-rule="evenodd" d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8z" />
                    </svg>
                </span>
            </a>
        </div>
    </div>
</div>