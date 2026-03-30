<?php
// Get current language
$current_lang = pll_current_language();
$home_url = pll_home_url($current_lang);

get_header();
?>

<div class="error-404-container">
    <div class="error-404-content">
        <h1 class="error-404-title"><?php echo pll__('Greška 404') ?></h1>
        <h2 class="error-404-subtitle"><?php echo pll__('Oops! Stranica nije pronađena'); ?></h2>
        <p class="error-404-description"><?php echo pll__('Čini se da stranica koju tražite ne postoji. Možda ste pogrešno uneli adresu ili je stranica uklonjena.'); ?></p>

        <div class="empty-cart-buttons-wrapper">
            <div class="button-wrapper">
                <a class="button-main" href="<?php echo esc_url($home_url); ?>">
                    <span><?php echo pll__('Nazad na početnu stranu'); ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="32px" height="32px" viewBox="0 0 16 16" fill="#fff" class="bi bi-arrow-right-short">
                            <path fill-rule="evenodd" d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8z" />
                        </svg>
                    </span>
                </a>
            </div>
            <div class="button-wrapper">
                <a class="button-main" href="<?php echo esc_url($home_url . 'porucivanje/'); ?>">
                    <span><?php echo pll__('Naruči Kombo paket'); ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="32px" height="32px" viewBox="0 0 16 16" fill="#fff" class="bi bi-arrow-right-short">
                            <path fill-rule="evenodd" d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8z" />
                        </svg>
                    </span>
                </a>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>