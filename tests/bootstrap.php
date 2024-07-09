<?php
/**
 * PHPUnit bootstrap file
 *
 */
$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}
/**
 * The path to the main file of the plugin to test.
 */
define( 'WP_USE_THEMES', false );
define( 'WP_TESTS_FORCE_KNOWN_BUGS', true );
// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';
/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	//We need to load the sdk separately since phpunit is loading already the autoloader, and due to the exit condition in load.php on ABSPATH, the sdk is not loaded with the composer autoloader in phpunit env.
	require dirname( dirname( __FILE__ ) ) . '/vendor/codeinwp/themeisle-sdk/load.php';
	require dirname( dirname( __FILE__ ) ) . '/tweet-old-post.php';
}

tests_add_filter( 'plugins_loaded', '_manually_load_plugin' );
// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
activate_plugin( 'tweet-old-post/tweet-old-post.php' );
global $current_user;
$current_user = new WP_User( 1 );
$current_user->set_role( 'administrator' );
wp_update_user( array( 'ID' => 1, 'first_name' => 'Admin', 'last_name' => 'User' ) );