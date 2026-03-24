<?php do_action("get_header"); ?>

<?php while (have_posts()) : the_post(); ?>
    <div class="container py-5">
        <div class="row">
            <div class="col-12">
                <h1><?php echo get_the_title(); ?></h1>
                <div class="content mt-5">
                    <?php the_content(); ?>
                </div>
            </div>
        </div>
    </div>
<?php endwhile; ?>

<?php do_action("get_footer"); ?>