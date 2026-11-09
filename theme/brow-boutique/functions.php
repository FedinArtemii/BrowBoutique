<?php
function brow_boutique_home_content() {
    return str_replace(
        '{{ASSETS}}',
        esc_url( get_theme_file_uri( 'assets' ) ),
        file_get_contents( get_theme_file_path( 'content/home.html' ) )
    );
}

function brow_boutique_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'custom-logo' );
    add_theme_support( 'editor-styles' );
    add_editor_style( 'assets/editor.css' );
    register_nav_menus( array( 'primary' => 'Главное меню' ) );
    register_block_pattern( 'brow-boutique/home', array(
        'title'       => 'Главная страница студии',
        'description' => 'Баннер, услуги, жемчужный уход, обучение и контакты.',
        'content'     => brow_boutique_home_content(),
        'categories'  => array( 'featured' ),
    ) );
}
add_action( 'after_setup_theme', 'brow_boutique_setup' );

function brow_boutique_assets() {
    $version = wp_get_theme()->get( 'Version' );
    wp_enqueue_style( 'brow-boutique', get_theme_file_uri( 'assets/site.css' ), array(), $version );
    wp_enqueue_style( 'brow-boutique-motion', get_theme_file_uri( 'assets/motion/motion.css' ), array(), $version );
    wp_enqueue_script_module( 'brow-boutique', get_theme_file_uri( 'assets/site.js' ), array(), $version );
}
add_action( 'wp_enqueue_scripts', 'brow_boutique_assets' );

function brow_boutique_image( $content, $block ) {
    $classes = explode( ' ', $block['attrs']['className'] ?? '' );
    $tags = new WP_HTML_Tag_Processor( $content );
    if ( in_array( 'hero-photo', $classes, true ) && $tags->next_tag( 'img' ) ) {
        $tags->set_attribute( 'fetchpriority', 'high' );
        $tags->set_attribute( 'loading', 'eager' );
        $tags->set_attribute( 'data-enter-media', '' );
    }
    if ( in_array( 'develop-photo', $classes, true ) && $tags->next_tag( 'figure' ) ) {
        $tags->set_attribute( 'data-moment', 'picture-develops' );
        $tags->set_attribute( 'data-moment-opts', '{"look":"film","duration":1250}' );
    }
    return $tags->get_updated_html();
}
add_filter( 'render_block_core/image', 'brow_boutique_image', 10, 2 );

function brow_boutique_hero( $content, $block ) {
    if ( ( $block['attrs']['className'] ?? '' ) !== 'hero' ) {
        return $content;
    }
    $tags = new WP_HTML_Tag_Processor( $content );
    if ( $tags->next_tag( 'section' ) ) {
        $tags->set_attribute( 'data-enter', '' );
    }
    return $tags->get_updated_html();
}
add_filter( 'render_block_core/group', 'brow_boutique_hero', 10, 2 );

function brow_boutique_head() {
    if ( ! is_front_page() ) {
        return;
    }
    $description = 'Школа-студия в Челябинске. Оформление бровей, уход за ресницами, перманентный макияж и обучение. Онлайн-запись в DIKIDI.';
    echo '<meta name="page-type" content="place"><meta name="site-kind" content="other"><meta name="page-kind" content="place">' . "\n";
    echo '<meta name="theme-color" content="#f3e1ca">' . "\n";
    echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr( $description ) . '">' . "\n";
    echo '<meta property="og:type" content="website">' . "\n";
    echo '<meta property="og:image" content="' . esc_url( get_theme_file_uri( 'assets/images/profile.webp' ) ) . '">' . "\n";
    echo '<link rel="icon" href="' . esc_url( get_theme_file_uri( 'assets/images/logo.png' ) ) . '">' . "\n";
}
add_action( 'wp_head', 'brow_boutique_head', 1 );

function brow_boutique_menu_fallback() {
    echo '<ul class="menu">';
    foreach ( array( 'services' => 'Услуги', 'school' => 'Школа', 'contacts' => 'Контакты' ) as $id => $label ) {
        printf( '<li><a href="%s">%s</a></li>', esc_url( home_url( '/#' . $id ) ), esc_html( $label ) );
    }
    echo '</ul>';
}
