<?php

/**
 * Main loader file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://themeisle.com/
 * @since             3.0.0
 * @package           ROP
 *
 * @wordpress-plugin
 * Plugin Name: Revive Old Posts (Former Tweet Old Post)
 * Plugin URI: https://revive.social/
 * Description: WordPress plugin that helps you to keeps your old posts alive by sharing them and driving more traffic to them from twitter/facebook or linkedin. It also helps you to promote your content. You can set time and no of posts to share to drive more traffic.For questions, comments, or feature requests, <a href="http://revive.social/support/?utm_source=plugindesc&utm_medium=announce&utm_campaign=top">contact </a> us!
 * Version:           8.0.9
 * Author:            revive.social
 * Author URI:        https://revive.social/
 * Requires at least: 3.5
 * Tested up to:      4.8
 * Stable tag:        trunk
 * WordPress Available:  yes
 * Pro Slug:          tweet-old-post-pro
 * Requires License:    no
 * Requires PHP: 5.4
 * License:           GPLv2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: tweet-old-post
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-rop-activator.php
 */
function activate_rop() {
	Rop_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-rop-deactivator.php
 */
function deactivate_rop() {
	Rop_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_rop' );
register_deactivation_hook( __FILE__, 'deactivate_rop' );

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
	define( 'ROP_LITE_VERSION', '8.0.9' );
	define( 'ROP_LITE_BASE_FILE', __FILE__ );
	define( 'ROP_DEBUG', false );
	define( 'ROP_LITE_PATH', plugin_dir_path( __FILE__ ) );
	define( 'ROP_PATH', plugin_dir_path( __FILE__ ) );
	define( 'ROP_LITE_URL', plugin_dir_url( __FILE__ ) );

	$vendor_file = ROP_LITE_PATH . '/vendor/autoload.php';
	if ( is_readable( $vendor_file ) ) {
		require_once $vendor_file;
	}
	add_filter(
		'themeisle_sdk_products', function ( $products ) {
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
