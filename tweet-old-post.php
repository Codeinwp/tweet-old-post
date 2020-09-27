<?php

/**
 * Main loader file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://revive.social
 * @since             3.0.0
 * @package           ROP
 *
 * @wordpress-plugin
 * Plugin Name: Revive Old Posts
 * Plugin URI: https://revive.social/
 * Description: WordPress plugin that helps you to keeps your old posts alive by sharing them and driving more traffic to them from twitter/facebook or linkedin. It also helps you to promote your content. You can set time and no of posts to share to drive more traffic.For questions, comments, or feature requests, <a href="http://revive.social/support/?utm_source=plugindesc&utm_medium=announce&utm_campaign=top">contact </a> us!
 * Version:           8.5.18
 * Author:            revive.social
 * Author URI:        https://revive.social/
 * Requires at least: 3.5
 * Tested up to:      5.5
 * Stable tag:        trunk
 * WordPress Available:  yes
 * Pro Slug:          tweet-old-post-pro
 * Requires License:    no
 * Requires PHP: 7.1
 * License:           GPLv2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: tweet-old-post
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( function_exists( 'phpversion' ) ) {

	if ( version_compare( phpversion(), '7.1', '<' ) ) {
		add_action( 'admin_notices', 'rop_php_notice' );
		add_action( 'admin_init', 'deactivate_rop', 1 );
		return;
	}
}

if ( defined( 'PHP_VERSION' ) ) {
	if ( version_compare( PHP_VERSION, '7.1', '<' ) ) {
		add_action( 'admin_notices', 'rop_php_notice' );
		add_action( 'admin_init', 'deactivate_rop', 1 );
		return;
	}
}

/**
 * Shows a notice for sites running PHP less than 5.6.
 *
 * @since    8.1.4
 */
function rop_php_notice() {
	?>

	<div class="notice notice-error is-dismissible">
		<?php echo sprintf( __( '%1$s You\'re using a PHP version lower than 7.1! Revive Old Posts requires at least %2$sPHP 7.1%3$s to function properly. Plugin has been deactivated. %4$sLearn more here%5$s. %6$s', 'tweet-old-post' ), '<p>', '<b>', '</b>', '<a href="https://docs.revive.social/article/947-how-to-update-your-php-version" target="_blank">', '</a>', '</p>' ); ?>
	</div>
	<?php
}

/**
 * Deactivates Revive Old Posts.
 *
 * @since    8.1.4
 */
function deactivate_rop() {
	if ( is_plugin_active( 'tweet-old-post/tweet-old-post.php' ) ) {
		deactivate_plugins( 'tweet-old-post/tweet-old-post.php' );
	}
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-rop-activator.php
 */
function rop_activation() {
	Rop_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-rop-deactivator.php
 */
function rop_deactivation() {
	Rop_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'rop_activation' );
register_deactivation_hook( __FILE__, 'rop_deactivation' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    8.0.0
 */
function run_rop() {

	define( 'ROP_PRO_URL', 'http://revive.social/plugins/revive-old-post/' );
	define( 'ROP_LITE_VERSION', '8.5.18' );
	define( 'ROP_LITE_BASE_FILE', __FILE__ );
	define( 'ROP_DEBUG', false );
	define( 'ROP_LITE_PATH', plugin_dir_path( __FILE__ ) );
	define( 'ROP_PRO_PATH', WP_PLUGIN_DIR . '/tweet-old-post-pro/' );
	define( 'ROP_PATH', plugin_dir_path( __FILE__ ) );
	define( 'ROP_LITE_URL', plugin_dir_url( __FILE__ ) );
	define( 'ROP_STATUS_ALERT', 6 );// How many consecutive errors count towards status alert "Status: Error (check logs)"

	// Authorization APP Data
	define( 'ROP_AUTH_APP_URL', 'https://app.revive.social' );
	define( 'ROP_APP_FACEBOOK_PATH', '/fb_auth' );
	define( 'ROP_APP_TWITTER_PATH', '/tw_auth' );
	define( 'ROP_APP_LINKEDIN_PATH', '/li_auth' );
	define( 'ROP_APP_BUFFER_PATH', '/buffer_auth' );
	define( 'ROP_APP_TUMBLR_PATH', '/tumblr_auth' );
	define( 'ROP_APP_GMB_PATH', '/gmb_auth' );
	define( 'ROP_INSTALL_TOKEN_OPTION', 'rop_install_token' );

	$vendor_file = ROP_LITE_PATH . '/vendor/autoload.php';
	if ( is_readable( $vendor_file ) ) {
		require_once $vendor_file;
	}
	add_filter(
		'themeisle_sdk_products',
		function ( $products ) {
			$products[] = ROP_LITE_BASE_FILE;

			return $products;
		}
	);

	$plugin = new Rop();
	$plugin->run();

}

require( plugin_dir_path( __FILE__ ) . '/class-rop-autoloader.php' );
Rop_Autoloader::define_namespaces( array( 'Rop' ) );
/**
 * Invocation of the Autoloader::loader method.
 *
 * @since   8.0.0
 */
spl_autoload_register( array( 'Rop_Autoloader', 'loader' ) );

run_rop();
