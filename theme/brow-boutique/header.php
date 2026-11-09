<!doctype html>
<html <?php language_attributes(); ?> data-type="place" data-motion data-radius="sharp" data-buttons="solid">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main">Перейти к содержанию</a>
<header class="site-header">
    <div class="site-header-inner">
        <?php if ( has_custom_logo() ) : ?>
            <?php the_custom_logo(); ?>
        <?php else : ?>
            <a class="studio-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/logo.png' ) ); ?>" width="120" height="120" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
            </a>
        <?php endif; ?>
        <button class="menu-toggle" type="button" hidden aria-controls="studio-menu" aria-expanded="false">Меню<span aria-hidden="true"></span></button>
        <nav id="studio-menu" aria-label="Главное меню">
            <?php wp_nav_menu( array(
                'theme_location' => 'primary',
                'container'      => false,
                'menu_class'     => 'menu',
                'fallback_cb'    => 'brow_boutique_menu_fallback',
            ) ); ?>
        </nav>
    </div>
</header>
