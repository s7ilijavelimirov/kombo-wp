</section>


<footer>
    <div class="menu-footers">
        <?php if (is_active_sidebar('footer_logo')) : ?>
            <div class="footer-widget-logo" role="complementary">
                <?php dynamic_sidebar('footer_logo'); ?>
            </div>
        <?php endif; ?>
        <?php if (is_active_sidebar('footer_nav')) : ?>
            <div class="footer-nav">
                <?php dynamic_sidebar('footer_nav'); ?>
            </div>
        <?php endif; ?>
        <?php if (is_active_sidebar('language_switcher_bar')) : ?>
            <div class="footer-nav lang-switcher">
                <?php dynamic_sidebar('language_switcher_bar'); ?>
            </div>
        <?php endif; ?>
       
        <?php if (is_active_sidebar('address_bar')) : ?>
            <div class="footer_address">
                <?php dynamic_sidebar('address_bar'); ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="footer-copyright-wrapper">
        <?php if (is_active_sidebar('copyright_bar')) : ?>
            <div class="footer-widget-social" role="complementary">
                <?php dynamic_sidebar('copyright_bar'); ?>
            </div>
        <?php endif; ?>
    </div>
</footer>


<?php do_action("get_html_end"); ?>