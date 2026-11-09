<?php get_header(); ?>
<main id="main" class="wrap entry" tabindex="-1">
    <?php while ( have_posts() ) : ?>
        <?php the_post(); ?>
        <h1><?php the_title(); ?></h1>
        <?php the_content(); ?>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
