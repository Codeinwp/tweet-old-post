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

	$rop_cron_token = get_option( 'rop_access_token', '' );

	if ( ! empty( $rop_cron_token ) ) {

		if ( ! defined( 'ROP_LITE_PATH' ) ) {
			define( 'ROP_LITE_PATH', plugin_dir_path( __FILE__ ) );
		}

		$vendor_file = ROP_LITE_PATH . 'vendor/autoload.php';
		if ( is_readable( $vendor_file ) ) {
			require_once $vendor_file;

			require( plugin_dir_path( __FILE__ ) . '/class-rop-autoloader.php' );
			Rop_Autoloader::define_namespaces( array( 'Rop' ) );
			/**
			 * Invocation of the Autoloader::loader method.
			 *
			 * @since   8.0.0
			 */
			spl_autoload_register( array( 'Rop_Autoloader', 'loader' ) );

			new RopCronSystem\Rop_Cron_Core();
			$request_call = new RopCronSystem\Curl_Helpers\Rop_Curl_Methods();

			$arguments = array(
				'type'         => 'POST',
				'request_path' => ':delete_account:',
			);

			$call_response = $request_call->create_call_process( $arguments );
		}
	}

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
		'rop_db_version',
		'rop_pro_db_version',
		'rop_first_install_date',
		'rop_first_install_version',
		'rop_linkedin_refresh_token_notice',
		'rop_buffer_via_rs_app',
		'rop_tumblr_via_rs_app',
		'rop_data_migrated_tax',
		'rop_changed_shortener',
		'rop_is_new_user',
		/**
		 * Old ROP options
		 */
		'cwp_topnew_active_status',
		'cwp_topnew_notice',
		'cwp_top_app_id',
		'cwp_top_app_secret',
		'cwp_top_consumer_key_tumblr',
		'cwp_top_consumer_secret_tumblr',
		'cwp_top_consumer_url_tumblr',
		'cwp_top_global_schedule',
		'cwp_top_lk_app_id',
		'cwp_top_lk_app_secret',
		'cwp_top_logged_in_users',
		'cwp_top_oauth_token',
		'cwp_top_oauth_token_secret',
		'cwp_top_oauth_token_secret_tumblr',
		'cwp_top_oauth_token_tumblr',
		'top_settings',

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
		/**
		 * Use remote or local Cron Job.
		 *
		 * @since 8.5.5
		 * @category New Cron System
		 *
		 * @see Rop_Cron_Helper::update_cron_type()
		 * @see Rop_Rest_Api::update_cron_type()
		 */
		'rop_use_remote_cron',
		/**
		 * Used in remote Cron Server debug test
		 *
		 * @since 8.5.5
		 *
		 * @see Debug_Page::load_custom_wp_admin_style()
		 */
		'rop_temp_debug',
		/**
		 * Holds information if the user agreed with terms and conditions of remote Cron system.
		 *
		 * @since 8.6.0
		 *
		 * Being removed here.
		 * @see \RopCronSystem\Pages\Debug_Page::reset_local_client()
		 *
		 * Being saved here.
		 */
		'rop_remote_cron_terms_agree',
		/**
		 * Removes the access key for remote Cron Service.
		 *
		 * @since 8.6.0
		 * @see Rop_Curl_Methods::create_register_data() creation.
		 */
		'rop_access_token',
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
	delete_metadata( 'user', 0, 'rop-remove-remote-cron-notice-dismissed', '', true );

	global $wpdb;
	$post_meta = $wpdb->prefix . 'postmeta';
	$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE meta_key = %s", 'rop_custom_messages_group' ) );
	$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE meta_key = %s", 'rop_custom_images_group' ) );
	$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE meta_key = %s", 'rop_variation_index' ) );
}
