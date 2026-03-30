<?php
/**
 * Primary sidebar — widget area `blog` (see WpTheme\Providers\Sidebars).
 *
 * @package kombo
 */

defined('ABSPATH') || exit;

if (!is_active_sidebar('blog')) {
    return;
}
?>
<aside class="sidebar widget-area blog-sidebar" role="complementary">
    <?php dynamic_sidebar('blog'); ?>
</aside>
