<?php do_action("get_html_start"); ?>
<?php $header_color = carbon_get_theme_option('background_color_header'); ?>



<header id="desktop-menu">
    <nav id="desktop-nav">
        <div class="logo-img">
            <?php
            $custom_logo_id = get_theme_mod('custom_logo');
            $logo = wp_get_attachment_image_src($custom_logo_id, 'full');
            if ($logo): ?>
                <div class="logo-wrapper">
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="logo navlogo" id="logo">
                        <img class="img-fluid" alt="<?php bloginfo('name'); ?>" src="<?php echo esc_url($logo[0]); ?>">
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <div class="menu">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'main-menu-srb',
                'container' => false,
                'menu_class' => 'nav-menu',
                'fallback_cb' => false,
                'depth' => 2
            ));
            ?>
            <div class="language-switcher">
                <?php
                if (function_exists('pll_the_languages')) {
                    $languages = pll_the_languages(array(
                        'show_flags' => 0,
                        'show_names' => 1,
                        'dropdown' => 0,
                        'hide_if_empty' => 0,
                        'hide_current' => 1,
                        'raw' => 1
                    ));

                    if ($languages) {
                        $total = count($languages);
                        $current = 0;

                        foreach ($languages as $language) {
                            $lang_name = $language['name'];
                            $short_name = "";

                            switch ($lang_name) {
                                case "RUSSIAN":
                                    $short_name = "RUS";
                                    break;
                                case "ENGLISH":
                                    $short_name = "ENG";
                                    break;
                                case "SRPSKI":
                                    $short_name = "SRB";
                                    break;
                            }

                            echo '<a href="' . esc_url($language['url']) . '">' . esc_html($short_name) . '</a>';
                            $current++;

                            if ($current < $total) {
                                echo '<div class="separator"></div>';
                            }
                        }
                    }
                }
                ?>
            </div>
            <div class="cart-icon">
                <?php
                $cart_url = wc_get_cart_url();
                $translated_cart_url = pll_get_post_translations(wc_get_page_id('cart'));
                $current_lang = pll_current_language();

                if (isset($translated_cart_url[$current_lang])) {
                    $cart_url = get_permalink($translated_cart_url[$current_lang]);
                }
                ?>
                <a href="<?php echo esc_url($cart_url); ?>" aria-label="<?php echo pll__('View Cart'); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 56 44" fill="none">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M26.3 35C23.935 35 22 36.935 22 39.3C22 41.665 23.935 43.6 26.3 43.6C28.665 43.6 30.6 41.665 30.6 39.3C30.6 36.935 28.665 35 26.3 35ZM47.8 35C45.435 35 43.5 36.935 43.5 39.3C43.5 41.665 45.435 43.6 47.8 43.6C50.165 43.6 52.1 41.665 52.1 39.3C52.1 36.935 50.165 35 47.8 35Z"
                            fill="#0E0E0E" />
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M13 0V4.3H17.3L25.04 20.64L22.03 25.8C21.815 26.445 21.6 27.305 21.6 27.95C21.6 30.315 23.535 32.25 25.9 32.25H51.7V27.95H26.76C26.545 27.95 26.33 27.735 26.33 27.52V27.305L28.265 23.65H44.175C45.895 23.65 47.185 22.79 47.83 21.5L55.57 7.525C56 7.095 56 6.88 56 6.45C56 5.16 55.14 4.3 53.85 4.3H22.03L20.095 0H13Z"
                            fill="#0E0E0E" />
                    </svg>
                    <?php if (WC()->cart->get_cart_contents_count() > 0): ?>
                        <span class="cart-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
                    <?php endif; ?>
                </a>
            </div>
            <!-- <div class="cart-icon">
                <a href="<?php echo wc_get_cart_url(); ?>" class="xoo-wsc-cart-trigger">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 56 44" fill="none">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M26.3 35C23.935 35 22 36.935 22 39.3C22 41.665 23.935 43.6 26.3 43.6C28.665 43.6 30.6 41.665 30.6 39.3C30.6 36.935 28.665 35 26.3 35ZM47.8 35C45.435 35 43.5 36.935 43.5 39.3C43.5 41.665 45.435 43.6 47.8 43.6C50.165 43.6 52.1 41.665 52.1 39.3C52.1 36.935 50.165 35 47.8 35Z"
                            fill="#0E0E0E" />
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M13 0V4.3H17.3L25.04 20.64L22.03 25.8C21.815 26.445 21.6 27.305 21.6 27.95C21.6 30.315 23.535 32.25 25.9 32.25H51.7V27.95H26.76C26.545 27.95 26.33 27.735 26.33 27.52V27.305L28.265 23.65H44.175C45.895 23.65 47.185 22.79 47.83 21.5L55.57 7.525C56 7.095 56 6.88 56 6.45C56 5.16 55.14 4.3 53.85 4.3H22.03L20.095 0H13Z"
                            fill="#0E0E0E" />
                    </svg>
                    <?php if (WC()->cart->get_cart_contents_count() > 0): ?>
                        <span class="cart-count">
                            <?php echo WC()->cart->get_cart_contents_count(); ?>
                        </span>
                    <?php endif; ?>
                </a>
            </div> -->
        </div>
    </nav>
</header>
<header id="mobile-menu">
    <nav id="mobile-nav">
        <div class=" logo-img">
            <?php
            $custom_logo_id = get_theme_mod('custom_logo');
            $logo = wp_get_attachment_image_src($custom_logo_id, 'full');
            if ($logo): ?>
                <div class="logo-wrapper">
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="logo navlogo" id="logo">
                        <img class="img-fluid" alt="<?php bloginfo('name'); ?>" src="<?php echo esc_url($logo[0]); ?>">
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <div class="cart-icon">
            <a href="<?php
                        $current_lang = pll_current_language();
                        if ($current_lang === 'sr') {
                            echo get_site_url() . '/korpa'; // Link za srpski
                        } elseif ($current_lang === 'en') {
                            echo get_site_url() . '/en/cart'; // Link za engleski
                        } elseif ($current_lang === 'ru') {
                            echo get_site_url() . '/ru/корзина/'; // Link za ruski
                        } else {
                            echo get_site_url(); // Podrazumevani link (početna stranica)
                        }
                        ?>" class="">
                <svg xmlns="http://www.w3.org/2000/svg" width="39" height="39" viewBox="0 0 39 39" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M15.8508 25.5439C14.7985 25.5439 13.9375 26.4049 13.9375 27.4573C13.9375 28.5096 14.7985 29.3706 15.8508 29.3706C16.9032 29.3706 17.7642 28.5096 17.7642 27.4573C17.7642 26.4049 16.9032 25.5439 15.8508 25.5439ZM25.4175 25.5439C24.3652 25.5439 23.5042 26.4049 23.5042 27.4573C23.5042 28.5096 24.3652 29.3706 25.4175 29.3706C26.4699 29.3706 27.3309 28.5096 27.3309 27.4573C27.3309 26.4049 26.4699 25.5439 25.4175 25.5439Z"
                        fill="#0E0E0E" />
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M9.93359 9.96973V11.8831H11.8469L15.2909 19.1538L13.9516 21.4498C13.8559 21.7368 13.7603 22.1194 13.7603 22.4064C13.7603 23.4588 14.6213 24.3198 15.6736 24.3198H27.1537V22.4064H16.0563C15.9606 22.4064 15.8649 22.3108 15.8649 22.2151V22.1194L16.726 20.4931H23.8053C24.5707 20.4931 25.1447 20.1104 25.4317 19.5364L28.8757 13.3181C29.067 13.1267 29.067 13.0311 29.067 12.8397C29.067 12.2657 28.6843 11.8831 28.1103 11.8831H13.9516L13.0906 9.96973H9.93359Z"
                        fill="#0E0E0E" />
                </svg>
                <?php if (WC()->cart->get_cart_contents_count() > 0): ?>
                    <span class="cart-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
                <?php endif; ?>
            </a>
        </div>
        <div class="burger-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="43" height="39" viewBox="0 0 39 39" fill="none">
                <rect x="4" y="24.458" width="18.8938" height="1.54168" fill="black" />
                <rect x="4" y="19.0615" width="18.8938" height="1.54168" fill="black" />
                <rect x="4" y="13.666" width="18.8938" height="1.54168" fill="black" />
            </svg>
        </div>
        <div class="menu-wrapper">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'main-menu-srb',
                'container' => false,
                'menu_class' => 'nav-menu',
                'fallback_cb' => false,
                'depth' => 2
            ));
            ?>
            <div class="language-switcher">
                <?php
                if (function_exists('pll_the_languages')) {
                    $languages = pll_the_languages(array(
                        'show_flags' => 0,
                        'show_names' => 1,
                        'dropdown' => 0,
                        'hide_if_empty' => 0,
                        'hide_current' => 0,
                        'raw' => 1
                    ));

                    if ($languages) {
                        $total = count($languages);
                        $current = 0;

                        foreach ($languages as $language) {
                            $is_current_lang = in_array('current-lang', $language['classes'], true);
                            $lang_name = $language['name'];
                            $class = $is_current_lang ? 'current' : '';
                            echo '<a href="' . esc_url($language['url']) . '" class="' . esc_attr($class) . '">' . esc_html($lang_name) . '</a>';
                            $current++;

                            if ($current < $total) {
                                echo '<div class="separator"></div>';
                            }
                        }
                    }
                }
                ?>
            </div>
        </div>
        <div class="close-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="53" height="60" viewBox="0 0 53 60" fill="none">
                <path d="M24.5 24L36.5 36M24.5 36L36.5 24" stroke="#0E0E0E" />
            </svg>
        </div>
    </nav>

</header>

<section class="content-body position-relative">