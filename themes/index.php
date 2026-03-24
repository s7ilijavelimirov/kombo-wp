<?php
get_header(); // Učitaj header.php
echo '<p>INDEX PAGE</p>';
if ( have_posts() ) :
    while ( have_posts() ) : the_post();
        the_content(); // Prikaži sadržaj posta
    endwhile;
else :
    echo '<p>No content found</p>';
endif;

get_sidebar(); // Učitaj sidebar.php ako postoji
get_footer(); // Učitaj footer.php
?>
