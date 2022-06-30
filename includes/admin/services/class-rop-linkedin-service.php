<?php
/**
 * The file that defines the Linkedin Service specifics.
 *
 * A class that is used to interact with Linkedin.
 * It extends the Rop_Services_Abstract class.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/services
 */

/**
 * Class Rop_Linkedin_Service
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
class Rop_Linkedin_Service extends Rop_Services_Abstract {

	/**
	 * An instance of authenticated LinkedIn user.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $user An instance of the current user.
	 */
	public $user;
	/**
	 * Defines the service name in slug format.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     string $service_name The service name.
	 */
	protected $service_name = 'linkedin';

	/**
	 * Method to inject functionality into constructor.
	 * Defines the defaults and settings for this service.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function init() {
		$this->display_name = 'LinkedIn';
	}

	/**
	 * Method to register credentials for the service.
	 *
	 * @param array $args The credentials array.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function set_credentials( $args ) {
		$this->credentials = $args;
	}

	/**
	 * Returns information for the current service.
	 *
	 * @return mixed
	 * @since   8.0.0
	 * @access  public
	 */
	public function get_service() {
		return $this->service;
	}

	/**
	 * Method for publishing with Linkedin service.
	 *
	 * @param array $post_details The post details to be published by the service.
	 * @param array $args Optional arguments needed by the method.
	 *
	 * @return mixed
	 * @since   8.0.0
	 * @access  public
	 */
	public function share( $post_details, $args = array() ) {

		if ( Rop_Admin::rop_site_is_staging( $post_details['post_id'] ) ) {
			$this->logger->alert_error( Rop_I18n::get_labels( 'sharing.share_attempted_on_staging' ) );
			return false;
		}

		if ( ! empty( $args['credentials']['client_id'] ) ) {
			$this->logger->alert_error( Rop_Pro_I18n::get_labels( 'errors.reconnect_linkedin' ) );
			$this->rop_get_error_docs( Rop_Pro_I18n::get_labels( 'errors.reconnect_linkedin' ) );
			return false;
		}

		if ( isset( $args['id'] ) ) {
			$args['id'] = $this->treat_underscore_exception( $args['id'], true ); // Add the underscore back.
		}

		$token = $args['credentials'];

		$post_url = $post_details['post_url'];
		$share_as_image_post = $post_details['post_with_image'];
		$post_id = $post_details['post_id'];

		$model       = new Rop_Post_Format_Model;
		$post_format = $model->get_post_format( $post_details['account_id'] );

		$hashtags = $post_details['hashtags'];

		if ( ! empty( $post_format['hashtags_randomize'] ) && $post_format['hashtags_randomize'] ) {
			$hashtags = $this->shuffle_hashtags( $hashtags );
		}

		// LinkedIn link post
		if ( ! empty( $post_url ) && empty( $share_as_image_post ) && get_post_type( $post_id ) !== 'attachment' ) {
			$new_post = $this->linkedin_article_post( $post_details, $hashtags, $args );
		}

		// LinkedIn plain text post
		if ( empty( $share_as_image_post ) && empty( $post_url ) ) {
			$new_post = $this->linkedin_text_post( $post_details, $hashtags, $args );
		}

		// LinkedIn media post
		if ( ! empty( $share_as_image_post ) || get_post_type( $post_id ) === 'attachment' ) {

			// Linkedin Api v2 doesn't support video upload. Share as article post
			if ( strpos( get_post_mime_type( $post_details['post_id'] ), 'video' ) !== false ) {
				$new_post = $this->linkedin_article_post( $post_details, $hashtags, $args );
			} else {
				$new_post = $this->linkedin_image_post( $post_details, $hashtags, $args, $token );
			}
		}

		if ( empty( $new_post ) ) {
			$this->logger->alert_error( Rop_I18n::get_labels( 'misc.no_post_data' ) );
			return;
		}

		$api_url = 'https://api.linkedin.com/v2/ugcPosts';
		$response = wp_remote_post(
			$api_url,
			array(
				'body'    => json_encode( $new_post ),
				'headers' => array(
					'Content-Type' => 'application/json',
					'x-li-format' => 'json',
					'X-Restli-Protocol-Version' => '2.0.0',
					'Authorization' => 'Bearer ' . $token,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			$error_string = $response->get_error_message();
			$this->logger->alert_error( Rop_I18n::get_labels( 'errors.wordpress_api_error' ) . $error_string );
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( array_key_exists( 'id', $body ) ) {

			$this->logger->alert_success(
				sprintf(
					'Successfully shared %s to %s on %s ',
					html_entity_decode( get_the_title( $post_details['post_id'] ) ),
					$args['user'],
					$post_details['service']
				)
			);
			// check if the token will expire soon
			$this->rop_refresh_linkedin_token_notice();
			return true;
		} else {

			$this->logger->alert_error( 'Cannot share to linkedin. Error:  ' . print_r( $body, true ) );
			$this->rop_get_error_docs( $body );
			// check if the token will expire soon
			$this->rop_refresh_linkedin_token_notice();
			return false;
		}

	}


	/**
	 * Linkedin article post.
	 *
	 * @param array  $post_details The post details to be published by the service.
	 * @param string $hashtags hashtags list string.
	 * @param array  $args Arguments needed by the method.
	 *
	 * @return array
	 * @since   8.2.3
	 * @access  private
	 */
	private function linkedin_article_post( $post_details, $hashtags, $args ) {

		$author_urn = $args['is_company'] ? 'urn:li:organization:' : 'urn:li:person:';

		$new_post = array(
			'author'          => $author_urn . $args['id'],
			'lifecycleState'  => 'PUBLISHED',
			'specificContent' =>
				array(
					'com.linkedin.ugc.ShareContent' =>
						array(
							'shareCommentary'    =>
								array(
									'text' => $this->strip_excess_blank_lines( $post_details['content'] ) . $this->get_url( $post_details ) . $hashtags,
								),
							'shareMediaCategory' => 'ARTICLE',
							'media'              =>
								array(
									0 =>
										array(
											'status'      => 'READY',
											'originalUrl' => trim( $this->get_url( $post_details ) ),
										),
								),
						),
				),
			'visibility'      =>
				array(
					'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
				),
		);

		return $new_post;

	}

	/**
	 * Linkedin Text post.
	 *
	 * @param array  $post_details The post details to be published by the service.
	 * @param string $hashtags hashtags list string.
	 * @param array  $args Arguments needed by the method.
	 *
	 * @return array
	 * @since   8.6.0
	 * @access  private
	 */
	private function linkedin_text_post( $post_details, $hashtags, $args ) {

		$author_urn = $args['is_company'] ? 'urn:li:organization:' : 'urn:li:person:';

		$new_post = array(
			'author'          => $author_urn . $args['id'],
			'lifecycleState'  => 'PUBLISHED',
			'specificContent' =>
				array(
					'com.linkedin.ugc.ShareContent' =>
						array(
							'shareCommentary'    =>
								array(
									'text' => $this->strip_excess_blank_lines( $post_details['content'] ) . $this->get_url( $post_details ) . $hashtags,
								),
							'shareMediaCategory' => 'NONE',
						),
				),
			'visibility'      =>
				array(
					'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
				),
		);

		return $new_post;

	}

	/**
	 * Linkedin image post format.
	 *
	 * @param array  $post_details The post details to be published by the service.
	 * @param string $hashtags hashtags list string.
	 * @param array  $args Arguments needed by the method.
	 * @param string $token The user token.
	 *
	 * @return array
	 * @since   8.2.3
	 * @access  private
	 */
	private function linkedin_image_post( $post_details, $hashtags, $args, $token ) {

		// If this is an attachment post we need to make sure we pass the URL to get_path_by_url() correctly
		if ( get_post_type( $post_details['post_id'] ) === 'attachment' ) {
			$img = $this->get_path_by_url( wp_get_attachment_url( $post_details['post_id'] ), $post_details['mimetype'] );
		} else {
			$img = $this->get_path_by_url( $post_details['post_image'], $post_details['mimetype'] );
		}

		if ( empty( $img ) ) {
			$this->logger->info( 'No image set for post, but "Share as Image Post" is checked. Falling back to article post' );
			return $this->linkedin_article_post( $post_details, $hashtags, $args );
		}

		$author_urn = $args['is_company'] ? 'urn:li:organization:' : 'urn:li:person:';

		$register_image = array(
			'registerUploadRequest' =>
				array(
					'recipes'              =>
						array(
							0 => 'urn:li:digitalmediaRecipe:feedshare-image',
						),
					'owner'                => $author_urn . $args['id'],
					'serviceRelationships' =>
						array(
							0 =>
								array(
									'relationshipType' => 'OWNER',
									'identifier'       => 'urn:li:userGeneratedContent',
								),
						),
				),
		);

		$api_url = 'https://api.linkedin.com/v2/assets?action=registerUpload';
		$response = wp_remote_post(
			$api_url,
			array(
				'body'    => json_encode( $register_image ),
				'headers' => array(
					'Content-Type' => 'application/json',
					'x-li-format' => 'json',
					'X-Restli-Protocol-Version' => '2.0.0',
					'Authorization' => 'Bearer ' . $token,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			$error_string = $response->get_error_message();
			$this->logger->alert_error( Rop_I18n::get_labels( 'errors.wordpress_api_error' ) . $error_string );
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $body['value']['uploadMechanism']['com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest']['uploadUrl'] ) ) {
			$this->logger->alert_error( 'Cannot share to LinkedIn, empty upload url' );
			return false;
		}

		if ( empty( $body['value']['asset'] ) ) {
			$this->logger->alert_error( 'Cannot share to LinkedIn, empty asset' );
			return false;
		}

		$upload_url = $body['value']['uploadMechanism']['com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest']['uploadUrl'];
		$asset      = $body['value']['asset'];

		if ( function_exists( 'exif_imagetype' ) ) {
			$img_mime_type = image_type_to_mime_type( exif_imagetype( $img ) );
		} else {
			$this->logger->alert_error( Rop_I18n::get_labels( 'errors.linkedin_missing_exif_imagetype' ) );
			return false;
		}
		$img_data   = file_get_contents( $img );
		$img_length = strlen( $img_data );

		$wp_img_put = wp_remote_request(
			$upload_url,
			array(
				'method'  => 'PUT',
				'headers' => array( 'Authorization' => 'Bearer ' . $token, 'Content-type' => $img_mime_type, 'Content-Length' => $img_length ),
				'body'    => $img_data,
			)
		);

		if ( is_wp_error( $wp_img_put ) ) {
			$error_string = $wp_img_put->get_error_message();
			$this->logger->alert_error( Rop_I18n::get_labels( 'errors.wordpress_api_error' ) . $error_string );
			return false;
		}

		$response_code = $wp_img_put['response']['code'];

		if ( $response_code !== 201 ) {
			$response_message = $wp_img_put['response']['message'];
			$this->logger->alert_error( 'Cannot share to LinkedIn. Error:  ' . $response_code . ' ' . $response_message );
			return false;
		}

		$new_post = array(
			'author'          => $author_urn . $args['id'],
			'lifecycleState'  => 'PUBLISHED',
			'specificContent' =>
				array(
					'com.linkedin.ugc.ShareContent' =>
						array(
							'shareCommentary'    =>
								array(
									'text' => $this->strip_excess_blank_lines( $post_details['content'] ) . $this->get_url( $post_details ) . $hashtags,
								),
							'shareMediaCategory' => 'IMAGE',
							'media'              =>
								array(
									0 =>
										array(
											'status'      => 'READY',
											'description' =>
												array(
													'text' => html_entity_decode( get_the_title( $post_details['post_id'] ) ),
												),
											'media'       => $asset,
											'title'       =>
												array(
													'text' => html_entity_decode( get_the_title( $post_details['post_id'] ) ),
												),
										),
								),
						),
				),
			'visibility'      =>
				array(
					'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
				),
		);

		return $new_post;
	}

	/**
	 * This method will load and prepare the account data for LinkedIn user.
	 * Used in Rest Api.
	 *
	 * @param array $accounts_data Linked accounts data.
	 *
	 * @return  bool
	 * @since   8.5.0
	 * @access  public
	 */
	public function add_account_with_app( $accounts_data ) {
		if ( ! $this->is_set_not_empty( $accounts_data, array( 'id' ) ) ) {
			return false;
		}

		$the_id         = unserialize( base64_decode( $accounts_data['id'] ) );
		$accounts_array = unserialize( base64_decode( $accounts_data['pages'] ) );

		// last array item contains notify date
		$notify_user_at = array_pop( $accounts_array );
		// save timestamp for when to notify user to refresh their linkedin token
		// set notified count to 0
		$notify_data = array(
			'notify_at' => $notify_user_at['notify_user_at'],
			'notified_count' => 0,
		);

		update_option( 'rop_linkedin_refresh_token_notice', $notify_data );

		$accounts = array();

		for ( $i = 0; $i < sizeof( $accounts_array ); $i ++ ) {

			$account = $this->user_default;

			$account_data = $accounts_array[ $i ];

			$account['id']           = $this->treat_underscore_exception( $account_data['id'] );
			$account['img']          = apply_filters( 'rop_custom_li_avatar', $account_data['img'] );
			$account['account']      = $account_data['account'];
			$account['is_company']   = $account_data['is_company'];
			$account['user']         = $account_data['user'];
			$account['access_token'] = $account_data['access_token'];

			if ( $i === 0 ) {
				$account['active'] = true;
			} else {
				$account['active'] = false;
			}

			$accounts[] = $account;
		}

		// Prepare the data that will be saved as new account added.
		$this->service = array(
			'id'                 => $this->treat_underscore_exception( $the_id ),
			'service'            => $this->service_name,
			'credentials'        => $account['access_token'],
			'available_accounts' => $accounts,
		);

		return true;
	}

	/**
	 * Method used to decide whether or not to show Linked button
	 *
	 * @return  bool
	 * @since   8.5.0
	 * @access  public
	 */
	public function rop_show_li_app_btn() {
		$installed_at_version = get_option( 'rop_first_install_version' );
		if ( empty( $installed_at_version ) ) {
			return false;
		}
		if ( version_compare( $installed_at_version, '8.5.0', '>=' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Method used to notify user that they should refresh their LinkedIn token
	 *
	 * @since   8.5.0
	 * @access  public
	 */
	public function rop_refresh_linkedin_token_notice() {

		$notify = get_option( 'rop_linkedin_refresh_token_notice' );

		if ( empty( $notify ) ) {
			return;
		}

		// Backwards compatibility pre v8.6.4
		if ( ! is_array( $notify ) ) {
			$notify = array(
				'notify_at' => $notify,
				'notified_count' => 0,
			);
		}

		$notify_at = $notify['notify_at'];
		$notified_count = $notify['notified_count'];

		$now = time();

		if ( $notify_at <= $now && $notified_count <= 4 ) {

			$headers     = array( 'Content-Type: text/html; charset=UTF-8' );
			$admin_email = get_option( 'admin_email' );

			if ( $notified_count < 4 ) {
				$subject     = Rop_I18n::get_labels( 'emails.refresh_linkedin_token_subject' );
				$message     = Rop_I18n::get_labels( 'emails.refresh_linkedin_token_message' );
			} else {
				$subject     = Rop_I18n::get_labels( 'emails.refresh_linkedin_token_subject_final' );
				$message     = Rop_I18n::get_labels( 'emails.refresh_linkedin_token_message_final' );
			}

			// notify user to refresh token
			$sent = wp_mail( $admin_email, $subject, $message, $headers );

			if ( $sent ) {
				$notified_count++;
				$notify_data = array(
					'notify_at' => $notify_at,
					'notified_count' => $notified_count,
				);
				update_option( 'rop_linkedin_refresh_token_notice', $notify_data, false );
			}

			$this->logger->alert_error( Rop_I18n::get_labels( 'general.rop_linkedin_refresh_token' ) );

		}

	}

	/**
	 * Method to populate additional data.
	 *
	 * @since   8.5.13
	 * @access  public
	 * @return mixed
	 */
	public function populate_additional_data( $account ) {
		if ( $account['is_company'] ) {
			$account['link'] = sprintf( 'https://linkedin.com/company/%s', $account['id'] );
		} else {
			$account['link'] = 'https://linkedin.com/feed/';
		}
		return $account;
	}

}
