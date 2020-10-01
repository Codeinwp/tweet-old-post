<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://revive.social/
 * @since      8.0.0
 *
 * @package    Rop
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$settings     = get_option( 'rop_data' );
$housekeeping = ! empty( $settings['general_settings']['housekeeping'] ) ? $settings['general_settings']['housekeeping'] : '';

if ( ! empty( $housekeeping ) ) {

	$option_keys = array(
		// Sharing
		'rop_data',
		'rop_queue',
		'rop_schedules_data',
		'rop-settings',
		'rop_opt_cat_filter',
		'rop_current_network_oauth',
		'rop_last_post_shared',
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
		'rop_toast',
		'cwp_rop_remote_trigger',
		'rop_notice_active',
		'rop_menu_pointer_queued',
		'rop_dashboard_pointers_queued',
		'rop_install_token',
		'rop_facebook_via_rs_app',
		'rop_twitter_via_rs_app',
		'rop_linkedin_via_rs_app',
		'rop_first_install_version',
		'rop_linkedin_refresh_token_notice',
		'rop_buffer_via_rs_app',
		'rop_tumblr_via_rs_app',
		'rop_data_migrated_tax',
		'rop_changed_shortener',
		/**
		 * Related functions
		 *
		 * @see Rop_Services_Model::facebook_exception_toast()
		 * @see Rop_Services_Model::facebook_exception_toast_remove()
		 * @see Rop_Admin::facebook_exception_toast_display()
		 * @see Rop_Rest_Api::fb_exception_toast()
		 */
		'rop_facebook_domain_toast',
		/**
		 * Related function
		 *
		 * @since 8.5.0
		 *
		 * @see Rop_Admin::check_cron_status()
		 * @see Rop_Cron_Helper::cron_status_global_change()
		 */
		'rop_is_sharing_cron_active',
	);

	foreach ( $option_keys as $key ) {
		delete_option( $key );
	}

	delete_metadata( 'user', 0, 'rop_publish_now_notice_dismissed', '', true );
	delete_metadata( 'user', 0, 'rop-linkedin-api-notice-dismissed', '', true );
	delete_metadata( 'user', 0, 'rop-buffer-addon-notice-dismissed', '', true );
	delete_metadata( 'user', 0, 'rop-wp-cron-notice-dismissed', '', true );
	delete_metadata( 'user', 0, 'rop-cron-event-status-notice-dismissed', '', true );
	delete_metadata( 'user', 0, 'rop-shortener-changed-notice-dismissed', '', true );
	delete_metadata( 'user', 0, 'rop-dropping-buffer-notice-dismissed', '', true );

	global $wpdb;
	$post_meta = $wpdb->prefix . 'postmeta';
	$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE meta_key = %s", 'rop_custom_messages_group' ) );
	$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE meta_key = %s", 'rop_custom_images_group' ) );
	$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE meta_key = %s", 'rop_variation_index' ) );
}
