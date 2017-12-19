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
	 * Assign the post format settings.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $account_id The account ID.
	 */
	public function set_post_format( $account_id ) {
		$parts = explode( '_', $account_id );
		$service = $parts[0];
		$post_format_model = new Rop_Post_Format_Model( $service );
		$this->post_format = $post_format_model->get_post_format( $account_id );
	}

	/**
	 * Utility method to retrieve custom values from post.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   int    $post_id The post ID.
	 * @param   string $field_key The field key name.
	 * @return mixed
	 */
	public function get_custom_field_value( $post_id, $field_key ) {
		return get_post_custom_values( $field_key, $post_id );
	}

	/**
	 * Method to build the URL for a given post object.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   WP_Post $post The post object.
	 * @return mixed
	 */
	public function build_url( WP_Post $post ) {
		$post_url = get_permalink( $post->ID );
		if ( $this->post_format && $this->post_format['include_link'] ) {
			$post_url = get_permalink( $post->ID );
		    if ( isset( $this->post_format['url_from_meta'] ) && $this->post_format['url_from_meta'] && isset( $this->post_format['url_meta_key'] ) && ! empty( $this->post_format['url_meta_key'] ) ) {
		        preg_match_all( '#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', get_post_meta( $post->ID, $this->post_format['url_meta_key'], true ), $match );
				if ( isset( $match[0] ) ) {
					if ( isset( $match[0][0] ) ) {
						$post_url = $match[0][0];
					}
				}
			}
		}// End if().
		return $post_url;
	}

	/**
	 * Creates the base content as specified by the post format option.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   WP_Post $post The post object.
	 * @return mixed|string
	 */
	private function build_base_content( WP_Post $post ) {
		switch ( $this->post_format['post_content'] ) {
			case 'post_title':
				$content = $post->post_title;
				break;
			case 'post_content':
				$content = $post->post_content;
				break;
			case 'post_title_content':
				$content = $post->post_title . ' ' . $post->post_content;
				break;
			case 'custom_field':
				$content = $this->get_custom_field_value( $post->ID, $this->post_format['custom_meta_field'] );
				break;
			default :
				$content = '';
				break;
		}

		if ( ! is_string( $content ) ) {
			$content = '';
		}
		$content = wp_strip_all_tags( html_entity_decode( $content,ENT_QUOTES ) );

	    return $content;
	}

	/**
	 * Utility method to append custom content if specified by the post format option.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   string $content The content to use.
	 * @return array
	 */
	private function append_custom_text( $content ) {
		$custom_length = 0;
		if ( strlen( $this->post_format['custom_text'] ) != 0 ) {
			$custom_length = strlen( $this->post_format['custom_text'] ) + 1; // one char added for the space.
			switch ( $this->post_format['custom_text_pos'] ) {
				case 'beginning':
					$content = $this->post_format['custom_text'] . ' ' . $content;
					break;
				default :
					$content = $content . ' ' . $this->post_format['custom_text'];
					break;
			}
		}
		return array(
			'custom_length' => $custom_length,
			'content' => $content,
		);
	}

	/**
	 * Utility method to filter content and generate hashtags as specified by the post format options.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   string             $content The content to filter.
	 * @param   Rop_Content_Helper $content_helper The content helper class. Used for processing.
	 * @param   WP_Post            $post The post object.
	 * @return array
	 */
	private function make_hashtags( $content, Rop_Content_Helper $content_helper, WP_Post $post ) {
		$hashtags_length = $this->post_format['hashtags_length'];
		switch ( $this->post_format['hashtags'] ) {
			case 'common-hashtags':
				$result = $this->get_common_hashtags( $content, $hashtags_length, $content_helper );
				break;
			case 'categories-hashtags':
				$result = $this->get_categories_hashtags( $content, $hashtags_length, $content_helper, $post );
				break;
			case 'tags-hashtags':
				$result = $this->get_tags_hashtags( $content, $hashtags_length, $content_helper, $post );
				break;
			case 'custom-hashtags':
				$result = $this->get_custom_hashtags( $content, $hashtags_length, $content_helper, $post );
				break;
			default : // no-hashtags
				$result = array(
					'filtered_content' => $content,
					'hashtags_length' => $hashtags_length,
					'hashtags' => '',
				);
				break;
		}// End switch().

		return $result;
	}

	/**
	 * Utility method to generate the common hashtags.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   string             $content The content to filter.
	 * @param   int                $hashtags_length The hashtags length.
	 * @param   Rop_Content_Helper $content_helper The content helper class. Used for processing.
	 * @return array
	 */
	private function get_common_hashtags( $content, $hashtags_length, Rop_Content_Helper $content_helper ) {
		$hashtags = '';
		$hastags_list = explode( ',', str_replace( ' ', '', $this->post_format['hashtags_common'] ) );
		if ( ! empty( $hastags_list ) ) {
			foreach ( $hastags_list as $hashtag ) {
				$hashtag = str_replace( '#', '', $hashtag );
				if ( $content_helper->mark_hashtags( $content, $hashtag ) !== false ) { // if the hashtag exists in $content
					$content = $content_helper->mark_hashtags( $content, $hashtag ); // simply add a # there
					$hashtags_length--; // subtract 1 for the # we added to $content
				} elseif ( strlen( $hashtag . $hashtags ) <= $hashtags_length || $hashtags_length == 0 ) {
					$hashtags = $hashtags . ' #' . preg_replace( '/-/', '', strtolower( $hashtag ) );
				}
			}
		}

	    return array(
	        'filtered_content' => $content,
			'hashtags_length' => $hashtags_length,
			'hashtags' => $hashtags,
		);
	}

	/**
	 * Utility method to generate the categories hashtags.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   string             $content The content to filter.
	 * @param   int                $hashtags_length The hashtags length.
	 * @param   Rop_Content_Helper $content_helper The content helper class. Used for processing.
	 * @param   WP_Post            $post The post object.
	 * @return array
	 */
	private function get_categories_hashtags( $content, $hashtags_length, Rop_Content_Helper $content_helper, WP_Post $post ) {
		$hashtags = '';
		if ( $post->post_type == 'post' ) {
			$post_categories = get_the_category( $post->ID );
			foreach ( $post_categories as $category ) {
				$hashtag = $category->slug;
				if ( $content_helper->mark_hashtags( $content, $hashtag ) !== false ) { // if the hashtag exists in $content
					$content = $content_helper->mark_hashtags( $content, $hashtag ); // simply add a # there
					$hashtags_length--; // subtract 1 for the # we added to $content
				} elseif ( strlen( $hashtag . $hashtags ) <= $hashtags_length || $hashtags_length == 0 ) {
					$hashtags = $hashtags . ' #' . preg_replace( '/-/', '', strtolower( $hashtag ) );
				}
			}
		} else {
			// if ( CWP_TOP_PRO ) {
			// global $CWP_TOP_Core_PRO;
			// $newHashtags = $CWP_TOP_Core_PRO->topProGetCustomCategories( $postQuery, $maximum_hashtag_length );
			// } TODO
			$hashtags = '';
		}

		return array(
			'filtered_content' => $content,
			'hashtags_length' => $hashtags_length,
			'hashtags' => $hashtags,
		);
	}

	/**
	 * Utility method to generate the tags hashtags.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   string             $content The content to filter.
	 * @param   int                $hashtags_length The hashtags length.
	 * @param   Rop_Content_Helper $content_helper The content helper class. Used for processing.
	 * @param   WP_Post            $post The post object.
	 * @return array
	 */
	private function get_tags_hashtags( $content, $hashtags_length, Rop_Content_Helper $content_helper, WP_Post $post ) {
		$hashtags = '';
		$postTags = wp_get_post_tags( $post->ID );
		foreach ( $postTags as $postTag ) {
			$hashtag = $postTag->slug;
			if ( $content_helper->mark_hashtags( $content, $hashtag ) !== false ) { // if the hashtag exists in $content
				$content = $content_helper->mark_hashtags( $content, $hashtag ); // simply add a # there
				$hashtags_length--; // subtract 1 for the # we added to $content
			} elseif ( strlen( $hashtag . $hashtags ) <= $hashtags_length || $hashtags_length == 0 ) {
				$hashtags = $hashtags . ' #' . preg_replace( '/-/', '', strtolower( $hashtag ) );
			}
		}

		return array(
			'filtered_content' => $content,
			'hashtags_length' => $hashtags_length,
			'hashtags' => $hashtags,
		);
	}

	/**
	 * Utility method to generate the custom hashtags.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   string             $content The content to filter.
	 * @param   int                $hashtags_length The hashtags length.
	 * @param   Rop_Content_Helper $content_helper The content helper class. Used for processing.
	 * @param   WP_Post            $post The post object.
	 * @return array
	 */
	private function get_custom_hashtags( $content, $hashtags_length, Rop_Content_Helper $content_helper, WP_Post $post ) {
		$hashtags = '';
		if ( empty( $this->post_format['hashtags_custom'] ) ) {
			$log = new Rop_Logger();
			$log->warn( 'You need to add a custom field name in order to fetch the hashtags. Please set it from Post Format > $network > Hashtag Custom Field' );
			$log->info( 'No hashtags used due to previous warning.' );
			return array(
				'filtered_content' => $content,
				'hashtags_length' => $hashtags_length,
				'hashtags' => $hashtags,
			);
		}
		$hashtag = get_post_meta( $post->ID, $this->post_format['hashtags_custom'], true );
		if ( $hashtags_length != 0 ) {
			if ( strlen( $hashtag ) <= $hashtags_length ) {
				$content_helper->use_ellipse( false );
				$hashtags = $content_helper->token_truncate( $hashtag, $hashtags_length );
			}
		}

		return array(
			'filtered_content' => $content,
			'hashtags_length' => $hashtags_length,
			'hashtags' => $hashtags,
		);
	}

	/**
	 * Utility method to prepare the content based on the post format settings.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   WP_Post $post The post object.
	 * @return array
	 */
	public function build_content( WP_Post $post ) {
		$content_helper = new Rop_Content_Helper();
		$max_length = $this->post_format['maximum_length'];

		$general_settings = new Rop_Settings_Model();
		$custom_messages = get_post_meta( $post->ID, 'rop_custom_messages_group', true );

		if ( $this->post_format ) {

			if ( $general_settings->get_custom_messages() && ! empty( $custom_messages ) ) {

				$random_index = rand( 0, ( sizeof( $custom_messages ) - 1 ) );
				$content = $custom_messages[ $random_index ]['rop_custom_description'];

				$result = $this->make_hashtags( $content, $content_helper, $post );
				$hashtags_length = $result['hashtags_length'];
				$hashtags = $result['hashtags'];
				$content = $result['filtered_content'];

				$size = $max_length - $hashtags_length;

				$response = array(
					'display_content' => $content_helper->token_truncate( $content, $size ) . ' ' . $hashtags,
					'hashtags' => $hashtags,
				);

			} else {
				$content = $this->build_base_content( $post );

				$result = $this->append_custom_text( $content );
				$custom_length = $result['custom_length'];
				$content = $result['content'];

				$result = $this->make_hashtags( $content, $content_helper, $post );
				$hashtags_length = $result['hashtags_length'];
				$hashtags = $result['hashtags'];
				$content = $result['filtered_content'];

				$size = $max_length - $hashtags_length - $custom_length;

				$response = array(
					'display_content' => $content_helper->token_truncate( $content, $size ) . ' ' . $hashtags,
					'hashtags' => $hashtags,
				);
			}// End if().

			return $response;

		}// End if().

		return array(
			'display_content' => 'N/A',
			'hashtags' => '',
		);
	}

	/**
	 * Formats an object from the post data for sharing.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string     $account_id The account ID.
	 * @param   WP_Post    $post The post object to format.
	 * @param   bool|array $prev_data Optional. Previous data to retain if object is updated.
	 * @return array
	 */
	public function get_formated_object( $account_id, WP_Post $post, $prev_data = false ) {
		$this->set_post_format( $account_id );

		$parts = explode( '_', $account_id );
		$service = $parts[0];

		$content = $this->build_content( $post );

		$filtered_post = array();
		$filtered_post['post_id'] = $post->ID;
		$filtered_post['account_id'] = $account_id;
		$filtered_post['service'] = $service;
		$filtered_post['post_title'] = $post->post_title;
		$filtered_post['post_content'] = $content['display_content'];
		$filtered_post['hashtags'] = $content['hashtags'];
		$filtered_post['custom_content'] = ( isset( $prev_data['custom_content'] ) && $prev_data['custom_content'] != '' ) ? $prev_data['custom_content'] : '';
		$filtered_post['post_url'] = $this->build_url( $post );
		$filtered_post['short_url_service'] = $this->post_format['short_url_service'];
		$filtered_post['shortner_credentials'] = ( isset( $this->post_format['shortner_credentials'] ) ) ? $this->post_format['shortner_credentials'] : array();

		if ( $prev_data !== false && isset( $prev_data['custom_img'] ) ) {
			$filtered_post['custom_img'] = $prev_data['custom_img'];
			$filtered_post['post_img'] = $prev_data['post_img'];
		} else {
			if ( has_post_thumbnail( $post->ID ) ) {
				$filtered_post['post_img'] = get_the_post_thumbnail_url( $post->ID, 'large' );
			} else {
				$filtered_post['post_img'] = false;
			}
			$filtered_post['custom_img'] = false;
		}

		return $filtered_post;
	}

	/**
	 * Returns the short url for the given service.
	 *
	 * @Throws Exception If a service can not be built and defaults to passed URL.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $url The URL to shorten.
	 * @param   string $short_url_service The shorten service. Used by the factory to build the service.
	 * @param   array  $credentials Optional. If needed the service credentials.
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
			$log->warn( 'Could NOT get short URL.', $exception );
			$short_url = $url;
		}

		return $short_url;
	}

}
