<?php

/**
 * The class responsible for dismissing ROP's notices.
 *
 * NOTE: Not all notice dismissals have been migrated to this class.
 *
 * @link       https://revive.social/
 * @since      8.7.0
 *
 * @package    Rop
 * @subpackage Rop/admin
 */
class Rop_Admin_Notices_Helpers {

	/**
	 * Returns the number of days since ROP has been installed.
	 *
	 * If the rop_first_install_date option is not found. We return 2 days.
	 *
	 * @since    8.7.0
	 * @return int Days since plugin has been installed.
	 */
	public static function rop_get_days_since_installed() {

		// Get the installed date.
		// If option does not exist then set installed date as two days ago.
		$installed_date = get_option( 'rop_first_install_date' );

		if ( ! empty( $installed_date ) ) {
			$installed_date = '@' . $installed_date;
		} else {
			$installed_date  = '@' . mktime( 0, 0, 0, gmdate( 'm' ), gmdate( 'd' ) - 2, gmdate( 'Y' ) );
		}

		$installed_date = new DateTime( $installed_date );
		$today = new DateTime( 'today' );
		$date_difference = $installed_date->diff( $today );
		$days_since_installed = $date_difference->format( '%a' );
		return (int) $days_since_installed;
	}

	/**
	 * Check whether the user has dismissed an admin notice and add the option to the database if they did.
	 *
	 * @since    8.7.0
	 */
	public static function rop_notice_dismissed() {

		if ( ! isset( $_REQUEST['rop_notice_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['rop_notice_nonce'] ) ), 'rop_notice_nonce_value' ) ) {
			exit( 'Failed to verify nonce. Please try going back and refreshing the page to try again.' );
		}

		$notice_id = ! empty( $_REQUEST['rop_notice_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['rop_notice_id'] ) ) : '';

		if ( ! empty( $notice_id ) ) {

			$user_id = wp_get_current_user()->ID;

			add_user_meta( $user_id, $notice_id, 'true', true );

			if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
				wp_safe_redirect( esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) );
				exit;
			}
		}
	}

	/**
	 * Check whether the user has dismissed an admin notice and add the option to the database if they did.
	 *
	 * @param int    $user_id The user ID.
	 * @param string $notice_id  unique notice ID.
	 * @since    8.7.0
	 */
	public static function rop_should_show_notice( $user_id, $notice_id ) {

		$rop_user_dismissed_notice = get_user_meta( $user_id, $notice_id );

		// We shouldn't show the notice if user has dismissed it.
		if ( ! empty( $rop_user_dismissed_notice ) ) {
			return false;
		}

		$shownotice = false;

		// These notices should only show to admin users
		if ( is_multisite() && current_user_can( 'create_sites' ) ) {
			$shownotice = true;
		} elseif ( is_multisite() == false && current_user_can( 'install_plugins' ) ) {
			$shownotice = true;
		} else {
			$shownotice = false;
		}

		return $shownotice;
	}
}
