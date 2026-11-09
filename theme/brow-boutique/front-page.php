<?php get_header(); ?>
<main id="main" tabindex="-1">
<?php
if ( is_page() && have_posts() ) {
    while ( have_posts() ) {
        the_post();
        the_content();
    }
} else {
    echo apply_filters( 'the_content', brow_boutique_home_content() );
}
?>
</main>
<?php get_footer(); ?>
