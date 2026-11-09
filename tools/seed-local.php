<?php
if ( PHP_SAPI !== 'cli' || $argc !== 3 ) {
    exit( 1 );
}
$root = realpath( $argv[1] );
$url = $argv[2];
$config_path = $root . '/wp-config.php';
if ( ! file_exists( $config_path ) ) {
    $constants = array(
        'DB_NAME' => 'local', 'DB_USER' => 'local', 'DB_PASSWORD' => '', 'DB_HOST' => 'localhost',
        'DB_CHARSET' => 'utf8', 'DB_COLLATE' => '', 'DB_ENGINE' => 'sqlite',
        'WP_HOME' => $url, 'WP_SITEURL' => $url, 'DISABLE_WP_CRON' => true,
    );
    foreach ( array( 'AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY', 'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT' ) as $key ) {
        $constants[$key] = bin2hex( random_bytes( 32 ) );
    }
    $config = "<?php\n";
    foreach ( $constants as $key => $value ) {
        $config .= 'define(' . var_export( $key, true ) . ', ' . var_export( $value, true ) . ");\n";
    }
    $config .= '$table_prefix = "brow_";' . "\nif (!defined('ABSPATH')) { define('ABSPATH', __DIR__ . '/'); }\nrequire_once ABSPATH . 'wp-settings.php';\n";
    file_put_contents( $config_path, $config );
}
define( 'WP_INSTALLING', true );
require_once $root . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';

if ( ! is_blog_installed() ) {
    $password = wp_generate_password( 28, false );
    wp_install( 'Бутик Бровей', 'studio', 'studio@example.test', false, '', $password, 'ru_RU' );
    file_put_contents( dirname( $root ) . '/admin.txt', "studio\n$password\n" );
}
if ( get_stylesheet() !== 'brow-boutique' ) {
    switch_theme( 'brow-boutique' );
}
require_once $root . '/wp-content/themes/brow-boutique/functions.php';

if ( ! get_option( 'page_on_front' ) ) {
    update_option( 'blogname', 'Бутик Бровей' );
    update_option( 'blogdescription', 'Школа-студия в Челябинске' );
    update_option( 'timezone_string', 'Asia/Yekaterinburg' );
    update_option( 'blog_public', 0 );
    $existing = get_page_by_path( 'studio' );
    $id = $existing ? $existing->ID : wp_insert_post( array(
        'post_type'    => 'page',
        'post_status'  => 'publish',
        'post_name'    => 'studio',
        'post_title'   => 'Главная',
        'post_author'  => get_user_by( 'login', 'studio' )->ID,
        'post_content' => brow_boutique_home_content(),
    ), true );
    if ( is_wp_error( $id ) ) {
        fwrite( STDERR, $id->get_error_message() . PHP_EOL );
        exit( 1 );
    }
    update_option( 'show_on_front', 'page' );
    update_option( 'page_on_front', $id );
}
echo "WordPress: $url/\n";
echo 'Редактор: ' . $url . '/wp-admin/post.php?post=' . (int) get_option( 'page_on_front' ) . "&action=edit\n";
