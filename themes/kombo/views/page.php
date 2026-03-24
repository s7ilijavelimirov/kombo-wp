<?php do_action("get_header"); ?>

<?php while (have_posts()) : the_post(); ?>
    <!-- <div class="container my-4 py-4">
        <div class="row">
            <div class="col-12"> -->
                <!-- <h1><?php echo get_the_title(); ?></h1> -->
                <!-- <?php if (get_the_excerpt()) : ?>
                    <p><?php echo get_the_excerpt(); ?></p>
                <?php endif; ?> -->
                <!-- <hr> -->
                <!-- <div class="content <?php echo the_title(); ?>"> -->
                    <?php the_content(); ?>
                <!-- </div> -->
            <!-- </div>
        </div>
    </div> -->
<?php endwhile; ?>

<?php do_action("get_footer"); ?>