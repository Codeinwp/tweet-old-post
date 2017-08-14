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
 * Plugin Name: Revive Old Post (Former Tweet Old Post)
 * Plugin URI: https://revive.social/
 * Description: WordPress plugin that helps you to keeps your old posts alive by sharing them and driving more traffic to them from twitter/facebook or linkedin. It also helps you to promote your content. You can set time and no of posts to share to drive more traffic.For questions, comments, or feature requests, <a href="http://revive.social/support/?utm_source=plugindesc&utm_medium=announce&utm_campaign=top">contact </a> us!
 * Version:           7.4.8
 * Author:            revive.social
 * Author URI:        https://revive.social/
 * Requires at least: 3.5
 * Tested up to:      4.8
 * Stable tag:        trunk
 * WordPress Available:  yes
 * Pro Slug:          tweet-old-post-pro
 * Requires License:    no
 * License:           GPLv2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: tweet-old-post
 * Domain Path:       /languages
 */
/**
 * Bootstrap the plugin.
 */
function run_rop_lite() {

	define( 'ROP_PRO_URL', 'http://revive.social/plugins/revive-old-post/' );
	define( 'ROP_LITE_VERSION', '7.4.8' );
	define( 'ROP_LITE_PATH', plugin_dir_path( __FILE__ ) );
	define( 'ROP_LITE_URL', plugin_dir_url( __FILE__ ) );

	/*
	    $plugin = new ROP();
		$plugin->run();
	*/

	$vendor_file = ROP_LITE_PATH . '/vendor/autoload_52.php';
	if ( is_readable( $vendor_file ) ) {
		require_once $vendor_file;
	}
	add_filter(
		'themeisle_sdk_products', function ( $products ) {
			$products[] = __FILE__;

			return $products;
		}
	);
}

run_rop_lite();
