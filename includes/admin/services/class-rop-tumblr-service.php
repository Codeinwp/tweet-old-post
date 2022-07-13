<?php
/**
 * The file that defines the Tumblr Service specifics.
 *
 * A class that is used to interact with Tumblr.
 * It extends the Rop_Services_Abstract class.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/services
 */

/**
 * Class Rop_Tumblr_Service
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
class Rop_Tumblr_Service extends Rop_Services_Abstract {

	/**
	 * Defines the service name in slug format.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     string $service_name The service name.
	 */
	protected $service_name = 'tumblr';

	/**
	 * Method to inject functionality into constructor.
	 * Defines the defaults and settings for this service.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function init() {
		$this->display_name = 'Tumblr';
	}

	/**
	 * Method to retrieve the api object.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $oauth_token The OAuth Token. Default empty.
	 * @param   string $oauth_token_secret The OAuth Token Secret. Default empty.
	 * @param   string $consumer_key The consumer key. Default empty.
	 * @param   string $consumer_secret The consumer secret. Default empty.
	 *
	 * @return mixed
	 */
	public function get_api( $oauth_token = '', $oauth_token_secret = '', $consumer_key = '', $consumer_secret = '' ) {
		if ( empty( $consumer_key ) ) {
			return $this->api;
		}
		if ( empty( $consumer_secret ) ) {
			return $this->api;
		}
		$this->set_api( $oauth_token, $oauth_token_secret, $consumer_key, $consumer_secret );

		return $this->api;
	}

	/**
	 * Method to define the api.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $oauth_token The OAuth Token. Default empty.
	 * @param   string $oauth_token_secret The OAuth Token Secret. Default empty.
	 * @param   string $consumer_key The consumer key. Default empty.
	 * @param   string $consumer_secret The consumer secret. Default empty.
	 *
	 * @return mixed
	 */
	public function set_api(  $oauth_token = '', $oauth_token_secret = '', $consumer_key = '', $consumer_secret = '' ) {
		if ( ! class_exists( 'Tumblr\API\Client' ) ) {
			return false;
		}
		if ( ! function_exists( 'curl_reset' ) ) {
			return false;
		}
		$this->api = new \Tumblr\API\Client( $this->strip_whitespace( $consumer_key ), $this->strip_whitespace( $consumer_secret ), $this->strip_whitespace( $oauth_token ), $this->strip_whitespace( $oauth_token_secret ) );

	}

	/**
	 * Utility method to retrieve users from the Tumblr account connected using the RS app.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   8.5.7
	 * @access  public
	 *
	 * @param   object $data Response data from Tumblr.
	 *
	 * @return array
	 */
	private function get_users_rs_app( $data = null ) {
		$users = array();

		foreach ( $data as $page ) {

			$user_details = wp_parse_args(
				array(
					'id'      => $page['id'],
					'user'    => $this->normalize_string( $page['account'] ),
					'account' => $this->normalize_string( $page['user'] ),
					'img'     => apply_filters( 'rop_custom_tmblr_avatar', $page['img'] ),
				),
				$this->user_default
			);
			$users[]      = $user_details;
		}

		return $users;
	}

	/**
	 * Returns information for the current service.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function get_service() {
		return $this->service;
	}

	/**
	 *
	 * Check if a post has custom variations saved for it.
	 *
	 * @param mixed $post_id The Post ID.
	 * @return bool
	 */
	private function has_custom_share_variations( $post_id ) {

		$custom_content = get_post_meta( $post_id, 'rop_custom_messages_group' );

		// If there's no variations in the DB for this post bail.
		if ( empty( $custom_content ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Extract the hashtags from custom share variation if it exists.
	 *
	 * Also remove the hashtags from the content since they are not clickable.
	 *
	 * @param array $post_details The post details array for the post that is being shared.
	 * @return string
	 */
	private function get_custom_share_variation_hashtags( $post_details ) {

		// If there's no variations in the DB for this post bail.
		if ( empty( $this->has_custom_share_variations( $post_details['post_id'] ) ) ) {
			return;
		}

		$content = $post_details['content'];

		preg_match_all( '/#(\w+)/', $content, $hashtags );

		$hashtags_array = $hashtags[0];

		$hashtags_string = implode( ' ', $hashtags_array );

		return $hashtags_string;
	}

	/**
	 * Method for publishing with Twitter service.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   array $post_details The post details to be published by the service.
	 * @param   array $args Optional arguments needed by the method.
	 *
	 * @return mixed
	 */
	public function share( $post_details, $args = array() ) {

		if ( Rop_Admin::rop_site_is_staging( $post_details['post_id'] ) ) {
			$this->logger->alert_error( Rop_I18n::get_labels( 'sharing.share_attempted_on_staging' ) );
			return false;
		}

		$api = $this->get_api( $args['credentials']['oauth_token'], $args['credentials']['oauth_token_secret'], $args['credentials']['consumer_key'], $args['credentials']['consumer_secret'] );

		$model       = new Rop_Post_Format_Model();
		$post_format = $model->get_post_format( $post_details['account_id'] );

		$settings = new Rop_Settings_Model();
		$post_id   = $post_details['post_id'] ?? '';

		/*
		 * If the Share Variations feature is turned off, or the post does not have share variations, check for hashtags in the post_details array,
		 * Otherwise check for it inside the custom share message.
		 */
		if ( empty( $settings->get_custom_messages() ) || $this->has_custom_share_variations( $post_id ) === false ) {
			$hashtags = $post_details['hashtags'];
		} else {
			$hashtags = $this->get_custom_share_variation_hashtags( $post_details );
			// Users might want the hashtags to be removed from the content. Provide a filter for custom manipulation.
			$post_details = apply_filters( 'rop_tumblr_custom_post_details', $post_details, $hashtags );
		}

		if ( ! empty( $post_format['hashtags_randomize'] ) && $post_format['hashtags_randomize'] ) {
			$hashtags = $this->shuffle_hashtags( $hashtags );
		}

		// Tumblr creates hashtags differently
		$hashtags = preg_replace( array( '/ /', '/#/' ), array( '', ',' ), $hashtags );
		$hashtags = ltrim( $hashtags, ',' );

		// Link post
		if ( ! empty( $post_details['post_url'] ) && empty( $post_details['post_with_image'] ) ) {

			$thumbnail = get_the_post_thumbnail_url( $post_id, 'large' );

			// If thumbnail parameter is set but empty, tumblr would return an error. So we prevent this here.
			if ( ! empty( $thumbnail ) ) {
				$new_post['thumbnail'] = $thumbnail;
			}

			$new_post['type']        = 'link';
			$new_post['url']         = trim( $this->get_url( $post_details ) );
			$new_post['title']       = html_entity_decode( get_the_title( $post_details['post_id'] ), ENT_QUOTES );
			$new_post['description'] = $this->strip_excess_blank_lines( html_entity_decode( $post_details['content'], ENT_QUOTES ) );
			$new_post['author']      = $this->get_author( $post_id );
			$new_post['tags']        = $hashtags;
		}

		// Text post
		if ( empty( $post_details['post_url'] ) && empty( $post_details['post_with_image'] ) ) {
			$new_post['type'] = 'text';
			$new_post['body'] = $this->strip_excess_blank_lines( html_entity_decode( $post_details['content'], ENT_QUOTES ) );
			$new_post['tags'] = $hashtags;
		}

		// Photo post
		if ( ! empty( $post_details['post_with_image'] ) && strpos( $post_details['mimetype']['type'], 'image' ) !== false ) {
			$new_post['type']       = 'photo';
			$new_post['source_url'] = esc_url( get_site_url() );

			// get image path
			$image_source = $this->get_path_by_url( $post_details['post_image'], $post_details['mimetype'] );
			// If the URL is returned instead of PATH, use the url.
			if ( $this->is_remote_file( $image_source ) ) {
				$new_post['data'] = $post_details['post_image'];
			} else {
				// If the file can't be read, it returns the normal path back.
				$get_base64 = $this->convert_image_to_base64( $image_source );
				// We need to check if it was encoded or not.
				if ( $get_base64 === $image_source ) {
					// This is normal path, but Tumblr API doesn't seem to have support for image path
					// Fallback to image URL.
					$new_post['data'] = $post_details['post_image'];

				} else { // This is base 64
					$new_post['data64'] = $get_base64;
				}
			}

			$new_post['caption'] = $this->strip_excess_blank_lines( html_entity_decode( $post_details['content'], ENT_QUOTES ) ) . ' ' . trim( $this->get_url( $post_details ) );
			$new_post['tags']    = $hashtags;
		}

		// Video post| HTML5 video doesn't support all our initially set video formats
		if ( ! empty( $post_details['post_image'] ) && strpos( $post_details['mimetype']['type'], 'video' ) !== false ) {
			$new_post['type']       = 'video';
			$new_post['source_url'] = esc_url( get_site_url() );
			$new_post['embed']      = '<video width="100%" height="auto" controls>
  																 <source src="' . $post_details['post_image'] . '" type="video/mp4">
																	 Your browser does not support the video tag.
																	 </video>';
			$new_post['caption']    = $this->strip_excess_blank_lines( html_entity_decode( $post_details['content'], ENT_QUOTES ) ) . ' ' . trim( $this->get_url( $post_details ) );
			$new_post['tags']       = $hashtags;
		}

		try {

			$api->createPost( $args['id'] . '.tumblr.com', $new_post );

			$this->logger->alert_success(
				sprintf(
					'Successfully shared %s to %s on %s ',
					html_entity_decode( get_the_title( $post_id ) ),
					$args['user'],
					$post_details['service']
				)
			);

			return true;

		} catch ( Exception $exception ) {
			$this->logger->alert_error( 'Posting failed to Tumblr. Error: ' . $exception->getMessage() );
			$this->rop_get_error_docs( $exception->getMessage() );

			return false;
		}

	}

	/**
	 * Method for getting post author.
	 *
	 * @since   8.1.0
	 * @access  private
	 *
	 * @param   int $post_id The post id.
	 *
	 * @return string
	 */
	private function get_author( $post_id ) {
		$author_id = get_post_field( 'post_author', $post_id );
		$author    = get_the_author_meta( 'display_name', $author_id );

		$author = ( $author !== 'admin' ) ? $author : '';

		// allow users to not include author in shared posts
		return apply_filters( 'rop_tumblr_post_author', $author );
	}

	/**
	 * This method will load and prepare the account data for Tumblr user.
	 * Used in Rest Api.
	 *
	 * @since   8.5.7
	 * @access  public
	 *
	 * @param   array $account_data Tumblr pages data.
	 *
	 * @return  bool
	 */
	public function add_account_with_app( $account_data ) {
		if ( ! $this->is_set_not_empty( $account_data, array( 'id' ) ) ) {
			return false;
		}

		$the_id         = unserialize( base64_decode( $account_data['id'] ) );
		$accounts_array = unserialize( base64_decode( $account_data['pages'] ) );

		// Prepare the data that will be saved as new account added.
		$this->service = array(
			'id'                 => $the_id,
			'service'            => $this->service_name,
			'credentials' => array(
				'oauth_token'        => $accounts_array[0]['credentials']['oauth_token'],
				'oauth_token_secret' => $accounts_array[0]['credentials']['oauth_token_secret'],
				'consumer_key'       => $accounts_array[0]['credentials']['consumer_key'],
				'consumer_secret'    => $accounts_array[0]['credentials']['consumer_secret'],
			),
			'available_accounts' => $this->get_users_rs_app( $accounts_array ),
		);

		return true;
	}

	/**
	 * Method to populate additional data.
	 *
	 * @since   8.5.13
	 * @access  public
	 * @return mixed
	 */
	public function populate_additional_data( $account ) {
		$account['link'] = sprintf( 'https://tumblr.com/blog/%s', $account['id'] );
		return $account;
	}

}
