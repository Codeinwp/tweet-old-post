<?php

/**
 * The class responsible for all ROP's notices.
 *
 * NOTE: Not all notices has been migrated to this class.
 *
 * @link       https://revive.social/
 * @since      8.7.0
 *
 * @package    Rop
 * @subpackage Rop/admin
 */
class Rop_Admin_Notices {

	/**
	 * Displays an upsell for Revive Network plugin
	 *
	 * @since    8.7.0
	 */
	public function rop_revive_network_nag_delayed() {

		$notice_id = 'rop_revive_network_nag';
		$user_id = wp_get_current_user()->ID;

		 $days_since_installed = Rop_Admin_Notices_Helpers::rop_get_days_since_installed();
		 $should_show_notice = Rop_Admin_Notices_Helpers::rop_should_show_notice( $user_id, $notice_id );
		 $revive_network_active = class_exists( 'Revive_Network_Admin' );

		if ( $days_since_installed >= 3 && $should_show_notice && $revive_network_active === false ) {

			$plugin_image_path = ROP_LITE_URL . 'assets/img/revive-network-logo.png';
			$upsell_title = Rop_I18n::get_labels( 'notices.revive_network_upsell_notice_title' );
			$upsell_body = Rop_I18n::get_labels( 'notices.revive_network_upsell_notice_body' );
			$learn_more = Rop_I18n::get_labels( 'misc.learn_more' );
			$dismiss = Rop_I18n::get_labels( 'notices.dismiss_permanently' );
			$nonce = wp_create_nonce( 'rop_notice_nonce_value' );

			$dismiss_url = admin_url( 'admin-ajax.php?action=rop_notice_dismissed&rop_notice_id=' . $notice_id . '&rop_notice_nonce=' . $nonce );
			$upsell_link = tsdk_utmify(Rop_Admin::RN_LINK,'rn','admin_notice');
			$markup = <<<UPSELLHTML
				
				<div class="update-nag rop-revive-network-admin-notice">
				<div class="rop-revive-network-notice-logo"></div> 
				<p class="rop-revive-network-notice-title">$upsell_title </p> 
				<p class="rop-revive-network-notice-body">$upsell_body </p>
				<br>
				<ul class="rop-revive-network-notice-body rop-revive-network-red">
				<li><span class="dashicons dashicons-share-alt2"></span><a target="_blank" href="$upsell_link" style="color: #2b4fa3">$learn_more</a></li>
				<li><span class="dashicons dashicons-dismiss"></span><a href="$dismiss_url" style="color: #2b4fa3">$dismiss</a></li>
				</ul>
				
				</div>
UPSELLHTML;
			echo $markup;
		}

	}


}
