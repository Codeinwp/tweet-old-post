<?php
/**
 * The file that defines the class in charge of
 * preparing the queue post format.
 *
 * A class that is used to format the content with respect to post format options.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/helpers
 */

/**
 * Class Rop_Post_Format_Helper
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
class Rop_Post_Format_Helper {

	/**
	 * Stores the post format options if not false.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var bool|array $post_format The post format options or false.
	 */
	private $post_format = false;
	/**
	 * Stores the account id.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var string $account_id The account id used..
	 */
	private $account_id = false;

	/**
	 * Formats an object from the post data for sharing.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   int        $post_id The post ID.
	 * @param   string|int $account_id The post account id.
	 *
	 * @return array
	 */
	public function get_formated_object( $post_id, $account_id = 0 ) {
		if ( ! empty( $account_id ) ) {
			$this->set_post_format( $account_id );
		} else {
			if ( empty( $this->post_format ) ) {
				return array();
			}
		}
		$service                            = $this->get_service();
		$content                            = $this->build_content( $post_id );
		$filtered_post                      = array();
		$filtered_post['post_id']           = $post_id;
		$filtered_post['account_id']        = $this->account_id;
		$filtered_post['service']           = $service;
		$filtered_post['content']           = apply_filters( 'rop_content_filter', $content['display_content'], $post_id, $account_id, $service );
		$filtered_post['hashtags']          = $content['hashtags'];
		$filtered_post['post_url']          = $this->build_url( $post_id );
		$filtered_post['post_image']        = $this->post_format['image'] ? $this->build_image( $post_id ) : '';
		$filtered_post['short_url']         = $this->post_format['short_url'];
		$filtered_post['short_url_service'] = ( $this->post_format['short_url'] ) ? $this->post_format['short_url_service'] : '';
		$filtered_post['post_with_image']   = $this->post_format['image'];

		$filtered_post['shortner_credentials'] = ( isset( $this->post_format['shortner_credentials'] ) ) ? $this->post_format['shortner_credentials'] : array();

		return $filtered_post;
	}

	/**
	 * Assign the post format settings.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $account_id The account ID.
	 */
	public function set_post_format( $account_id ) {
		$parts             = explode( '_', $account_id );
		$service           = $parts[0];
		$post_format_model = new Rop_Post_Format_Model( $service );
		$this->account_id  = $account_id;
		$this->post_format = $post_format_model->get_post_format( $account_id );
	}

	/**
	 * Get service by account name.
	 *
	 * @return string Service slug.
	 */
	private function get_service() {
		$parts   = explode( '_', $this->account_id );
		$service = $parts[0];

		return $service;
	}

	/**
	 * Utility method to prepare the content based on the post format settings.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   int $post_id The post object.
	 *
	 * @return array
	 */
	public function build_content( $post_id ) {
		$default_content = array( 'display_content' => '', 'hashtags' => '' );
		$content_helper  = new Rop_Content_Helper();
		$max_length      = $this->post_format['maximum_length'];

		/**
		 * Content edited thru queue.
		 */

		$custom_content = get_post_meta( $post_id, '_rop_edit_' . md5( $this->account_id ), true );
		if ( ! empty( $custom_content ) ) {
			$share_content = isset( $custom_content['text'] ) ? $custom_content['text'] : '';
			if ( ! empty( $share_content ) ) {
				$share_content = $content_helper->token_truncate( $share_content, $max_length );

				return wp_parse_args( array( 'display_content' => $share_content ), $default_content );
			}
		}
		/**
		 * Check custom messages if exists.
		 */
		$custom_messages = get_post_meta( $post_id, 'rop_custom_messages_group', true );

		if ( ! empty( $custom_messages ) ) {
			$custom_messages = array_values( $custom_messages );
			$random_index    = rand( 0, ( count( $custom_messages ) - 1 ) );
			$share_content   = $custom_messages[ $random_index ]['rop_custom_description'];
			$share_content   = $content_helper->token_truncate( $share_content, $max_length );

			return wp_parse_args( array( 'display_content' => $share_content ), $default_content );
		}
		if ( empty( $this->post_format ) ) {
			return $default_content;
		}
		/**
		 * Generate content based on the post format settings.
		 */

		$base_content  = $this->build_base_content( $post_id );
		$base_content  = $content_helper->token_truncate( $base_content, $max_length );
		$custom_length = $this->get_custom_length();

		$result = $this->make_hashtags( $base_content, $content_helper, $post_id );

		$hashtags = $result['hashtags'];
		$size     = $max_length - ( strlen( $hashtags ) ) - $custom_length;
		if ( $size <= 0 ) {
			$size = $max_length;
		}
		$service = $this->get_service();
		if ( $service === 'twitter' && $this->post_format['include_link'] ) {
			$size = $size - 24;
		}
		$base_content = $content_helper->token_truncate( $base_content, $size );

		$base_content = $base_content . $hashtags;
		$base_content = $this->append_custom_text( $base_content );
		/**
		 * Adds safe check for content length.
		 */
		$response = array(
			'display_content' => $content_helper->token_truncate( $base_content, $max_length ),
			'hashtags'        => $hashtags,
		);

		return $response;

	}

	/**
	 * Creates the base content as specified by the post format option.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   int $post_id The post object.
	 *
	 * @return mixed|string
	 */
	private function build_base_content( $post_id ) {
		switch ( $this->post_format['post_content'] ) {
			case 'post_title':
				$content = get_the_title( $post_id );
				break;
			case 'post_content':
				$content = apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) );
				break;
			case 'post_title_content':
				$content = get_the_title( $post_id ) . ' ' . apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) );
				break;
			case 'custom_field':
				$content = $this->get_custom_field_value( $post_id, $this->post_format['custom_meta_field'] );
				break;
			default:
				$content = '';
				break;
		}
		$content = strip_shortcodes( $content );
		$content = wp_strip_all_tags( html_entity_decode( $content, ENT_QUOTES ) );

		$content = trim( $content );

		return $content;
	}

	/**
	 * Utility method to retrieve custom values from post.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   int    $post_id The post ID.
	 * @param   string $field_key The field key name.
	 *
	 * @return mixed
	 */
	public function get_custom_field_value( $post_id, $field_key ) {
		if ( empty( $field_key ) ) {
			return '';
		}

		return get_post_meta( $post_id, $field_key, true );
	}

	/**
	 * Utility method to append custom content if specified by the post format option.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @return int
	 */
	private function get_custom_length() {
		if ( empty( $this->post_format['custom_text'] ) ) {
			return 0;
		}

		return strlen( $this->post_format['custom_text'] ) + 1; // For the extra space

	}

	/**
	 * Utility method to filter content and generate hashtags as specified by the post format options.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   string             $content The content to filter.
	 * @param   Rop_Content_Helper $content_helper The content helper class. Used for processing.
	 * @param   int                $post The post object.
	 *
	 * @return array
	 */
	private function make_hashtags( $content, Rop_Content_Helper $content_helper, $post ) {
		$hashtags_length = intval( $this->post_format['hashtags_length'] );
		if ( empty( $hashtags_length ) ) {
			return array(
				'hashtags_length' => 0,
				'hashtags'        => '',
			);
		}
		switch ( $this->post_format['hashtags'] ) {
			case 'common-hashtags':
				$result = $this->get_common_hashtags();
				break;
			case 'categories-hashtags':
				$result = $this->get_categories_hashtags( $post );
				break;
			case 'tags-hashtags':
				$result = $this->get_tags_hashtags( $post );
				break;
			case 'custom-hashtags':
				$result = $this->get_custom_hashtags( $post );
				break;
			default: // no-hashtags
				$result = array();
				break;
		}// End switch().

		if ( empty( $result ) ) {
			return array(
				'hashtags_length' => 0,
				'hashtags'        => '',
			);
		}
		$hashtags = '';
		$result   = array_filter( $result, 'strlen' );
		$result   = array_map(
			function ( $value ) {
				return str_replace( '#', '', $value );
			}, $result
		);
		foreach ( $result as $hashtag ) {
			if ( $content_helper->mark_hashtags( $content, $hashtag ) !== false ) { // if the hashtag exists in $content
				$content = $content_helper->mark_hashtags( $content, $hashtag ); // simply add a # there
				$hashtags_length --; // subtract 1 for the # we added to $content
			} elseif ( strlen( $hashtag . $hashtags ) <= $hashtags_length || $hashtags_length == 0 ) {
				$hashtags = $hashtags . ' #' . preg_replace( '/-/', '', strtolower( $hashtag ) );
			}
		}

		return array(
			'hashtags_length' => $hashtags_length,
			'hashtags'        => $hashtags,
		);

	}

	/**
	 * Utility method to generate the common hashtags.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @return array
	 */
	private function get_common_hashtags() {
		$hashtags_list = explode( ',', str_replace( ' ', ',', $this->post_format['hashtags_common'] ) );
		if ( empty( $hashtags_list ) ) {
			return array();
		}

		return $hashtags_list;
	}

	/**
	 * Utility method to generate the categories hashtags.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   int $post_id The post object.
	 *
	 * @return array
	 */
	private function get_categories_hashtags( $post_id ) {

		$post_categories = get_the_category( $post_id );
		if ( empty( $post_categories ) ) {
			return array();
		}

		return wp_list_pluck( $post_categories, 'slug' );

	}

	/**
	 * Utility method to generate the tags hashtags.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   int $post_id The post object.
	 *
	 * @return array
	 */
	private function get_tags_hashtags( $post_id ) {

		$tags = wp_get_post_tags( $post_id );
		if ( empty( $tags ) ) {
			return array();
		}

		return wp_list_pluck( $tags, 'slug' );
	}

	/**
	 * Utility method to generate the custom hashtags.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   int $post_id The post object.
	 *
	 * @return array
	 */
	private function get_custom_hashtags( $post_id ) {

		if ( empty( $this->post_format['hashtags_custom'] ) ) {

			return array();
		}
		$hashtag = get_post_meta( $post_id, $this->post_format['hashtags_custom'], true );
		if ( empty( $hashtag ) ) {
			return array();
		}

		return array( $hashtag );
	}

	/**
	 * Utility method to append custom content if specified by the post format option.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   string $content The content to use.
	 *
	 * @return string
	 */
	private function append_custom_text( $content ) {

		if ( empty( $this->post_format['custom_text'] ) > 0 ) {
			return $content;
		}
		switch ( $this->post_format['custom_text_pos'] ) {
			case 'beginning':
				$content = $this->post_format['custom_text'] . ' ' . $content;
				break;
			default:
				$content = $content . ' ' . $this->post_format['custom_text'];
				break;
		}

		return $content;
	}

	/**
	 * Method to build the URL for a given post object.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   int $post The post object.
	 *
	 * @return mixed
	 */
	public function build_url( $post ) {
		$include_link = (bool) $this->post_format['include_link'];
		if ( ! $include_link ) {
			return '';
		}

		if ( $this->post_format['short_url'] && $this->post_format['short_url_service'] === 'wp_short_url' ) {
			$post_url = wp_get_shortlink( $post );
		} else {
			$post_url = get_permalink( $post );
		}

		if ( isset( $this->post_format['url_from_meta'] ) && $this->post_format['url_from_meta'] && isset( $this->post_format['url_meta_key'] ) && ! empty( $this->post_format['url_meta_key'] ) ) {
			preg_match_all( '#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', get_post_meta( $post, $this->post_format['url_meta_key'], true ), $match );
			if ( isset( $match[0] ) ) {
				if ( isset( $match[0][0] ) ) {
					$post_url = trim( $match[0][0] );
				}
			}
		}
		$settings = new Rop_Settings_Model();
		if ( $settings->get_ga_tracking() ) {
			$params                 = array();
			$params['utm_source']   = 'ReviveOldPost';
			$params['utm_medium']   = 'social';
			$params['utm_campaign'] = 'ReviveOldPost';
			$post_url               = add_query_arg( $params, $post_url );
		}

		return $post_url;
	}

	/**
	 * Get post image share url.
	 *
	 * @param int $post_id Id of the post.
	 *
	 * @return string Post share img.
	 */
	public function build_image( $post_id ) {
		$custom_content = get_post_meta( $post_id, '_rop_edit_' . md5( $this->account_id ), true );
		if ( ! empty( $custom_content ) ) {
			$share_image = isset( $custom_content['image'] ) ? $custom_content['image'] : '';
			if ( ! empty( $share_image ) ) {
				return $share_image;
			}
		}
		if ( has_post_thumbnail( $post_id ) ) {
			return get_the_post_thumbnail_url( $post_id, 'large' );
		}

		return '';

	}

	/**
	 * Returns the short url for the given service.
	 *
	 * @Throws  Exception If a service can not be built and defaults to passed URL.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $url The URL to shorten.
	 * @param   string $short_url_service The shorten service. Used by the factory to build the service.
	 * @param   array  $credentials Optional. If needed the service credentials.
	 *
	 * @return string
	 */
	public function get_short_url( $url, $short_url_service, $credentials = array() ) {
		$shortner_factory = new Rop_Shortner_Factory();

		try {
			$shortner_service = $shortner_factory->build( $short_url_service );
			if ( ! empty( $credentials ) ) {
				$shortner_service->set_credentials( $credentials );
			}
			$short_url = $shortner_service->shorten_url( $url );
		} catch ( Exception $exception ) {
			$log = new Rop_Logger();
			$log->alert_error( 'Could NOT get short URL. Error: ' . $exception->getMessage() );
			$short_url = $url;
		}

		return $short_url;
	}
}
