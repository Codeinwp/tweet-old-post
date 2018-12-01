<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$settings = get_option( 'rop_data' );
$housekeeping = $settings['general_settings']['housekeeping'];

if ( isset( $housekeeping ) && $housekeeping ) {

	$option_keys = array(
		// Sharing
		'rop_data',
		'rop_queue',
		'rop_schedules_data',
		'rop-settings',
		'rop_opt_cat_filter',
		'rop_current_network_oauth',
		// Shortners
		'rop_shortners_bitly',
		'rop_shortners_rvivly',
		'rop_shortners_owly',
		'rop_shortners_rebrandly',
		'rop_shortners_isgd',
		'rop_shortners_googl',
		'rop_shortners_firebase',
		// Licensing
		'tweet_old_post_pro_failed_checks',
		'tweet_old_post_pro_license_data',
		'tweet_old_post_pro_hide_valid',
		'tweet_old_post_pro_license_plan',
		'tweet_old_post_install',
		'tweet_old_post_pro_install',
		'tweet_old_post_review_flag',
		// Misc
		'rop_logs',
		'cwp_rop_remote_trigger',
		'rop_notice_active',
		'rop_menu_pointer_queued',
		'rop_dashboard_pointers_queued',
	);

	foreach ( $option_keys as $key ) {
		delete_option( $key );
	}

	global $wpdb;
	$post_meta = $wpdb->prefix . 'postmeta';
	$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE meta_key = %s", 'rop_custom_messages_group' ) );
}
