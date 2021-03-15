<?php
/**
 * PHPUnit bootstrap file
 *
 * @package PirateForms
 */
$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

/************* UVLabs test environment *************/

if ( getenv( 'USER' ) === 'uvdev' ){
	$_tests_dir = '/Users/uvdev/git_repos/wordpress-develop/tests/phpunit';
};

/************* UVLabs test environment *************/


/************* contactashish13 test environment *************
 * $_tests_dir = 'E:\work\apps\wordpress-dev\tests\phpunit';
 * $_core_dir = 'E:\work\apps\wordpress-dev\src\\';
 ************* contactashish13 test environment *************/

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
	require dirname( dirname( __FILE__ ) ) . '/tweet-old-post.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );
// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
activate_plugin( 'tweet-old-post/tweet-old-post.php' );
global $current_user;
$current_user = new WP_User( 1 );
$current_user->set_role( 'administrator' );
wp_update_user( array( 'ID' => 1, 'first_name' => 'Admin', 'last_name' => 'User' ) );