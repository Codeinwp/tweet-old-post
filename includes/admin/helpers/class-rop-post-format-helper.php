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
	 * Utility method to prepare the content based on the post format settings.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   WP_Post $post The post object.
	 * @return string
	 */
	public function build_content( WP_Post $post ) {
		$ch = new Rop_Content_Helper();
		$max_length = $this->post_format['maximum_length'];
		if ( $this->post_format ) {
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
					$content = 'We found nothing here!!!';
					break;
			}

			if ( ! is_string( $content ) ) { $content = '';
			}
			$content = wp_strip_all_tags( html_entity_decode( $content,ENT_QUOTES ) );

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

			$hashtags_length = $this->post_format['hashtags_length'];
			switch ( $this->post_format['hashtags'] ) {
				case 'no-hashtags':
					$hashtags = '';
					break;
				case 'common-hashtags':
					$hashtags = '';
					$hastags_list = explode( ',', str_replace( ' ', '', $this->post_format['hashtags_common'] ) );
					if ( ! empty( $hastags_list ) ) {
					    foreach ( $hastags_list as $hashtag ) {
					        $hashtag = str_replace( '#', '', $hashtag );
							if ( $ch->mark_hashtags( $content, $hashtag ) !== false ) { // if the hashtag exists in $content
								$content = $ch->mark_hashtags( $content, $hashtag ); // simply add a # there
								$hashtags_length--; // subtract 1 for the # we added to $content
							} elseif ( strlen( $hashtag . $hashtags ) <= $hashtags_length || $hashtags_length == 0 ) {
								$hashtags = $hashtags . ' #' . preg_replace( '/-/', '', strtolower( $hashtag ) );
							}
						}
					}
					break;
				case 'categories-hashtags':
					$hashtags = '';
					if ( $post->post_type == 'post' ) {
						$post_categories = get_the_category( $post->ID );
						foreach ( $post_categories as $category ) {
							$hashtag = $category->slug;
							if ( $ch->mark_hashtags( $content, $hashtag ) !== false ) { // if the hashtag exists in $content
								$content = $ch->mark_hashtags( $content, $hashtag ); // simply add a # there
								$hashtags_length--; // subtract 1 for the # we added to $content
							} elseif ( strlen( $hashtag . $hashtags ) <= $hashtags_length || $hashtags_length == 0 ) {
								$hashtags = $hashtags . ' #' . preg_replace( '/-/', '', strtolower( $hashtag ) );
							}
						}
					} else {
						// if ( CWP_TOP_PRO ) {
						// global $CWP_TOP_Core_PRO;
						// $newHashtags = $CWP_TOP_Core_PRO->topProGetCustomCategories( $postQuery, $maximum_hashtag_length );
						// }
					}
					break;
				case 'tags-hashtags':
					$hashtags = '';
					$postTags = wp_get_post_tags( $post->ID );
					foreach ( $postTags as $postTag ) {
						$hashtag = $postTag->slug;
						if ( $ch->mark_hashtags( $content, $hashtag ) !== false ) { // if the hashtag exists in $content
							$content = $ch->mark_hashtags( $content, $hashtag ); // simply add a # there
							$hashtags_length--; // subtract 1 for the # we added to $content
						} elseif ( strlen( $hashtag . $hashtags ) <= $hashtags_length || $hashtags_length == 0 ) {
							$hashtags = $hashtags . ' #' . preg_replace( '/-/', '', strtolower( $hashtag ) );
						}
					}
					break;
				case 'custom-hashtags':
					$hashtags = '';
					if ( empty( $this->post_format['hashtags_custom'] ) ) {
						// self::addNotice("You need to add a custom field name in order to fetch the hashtags. Please set it from Post Format > $network > Hashtag Custom Field ",'error');
						break;
					}
					$hashtag = get_post_meta( $post->ID, $this->post_format['hashtags_custom'], true );
					if ( $hashtags_length != 0 ) {
						if ( strlen( $hashtag ) <= $hashtags_length ) {
							$ch->use_ellipse( false );
							$hashtags = $ch->token_truncate( $hashtag, $hashtags_length );
						}
					}
					break;
				default :
					$hashtags = '';
					break;
			}// End switch().

			$size = $max_length - $hashtags_length - $custom_length;

			$response = array(
			    'display_content' => $ch->token_truncate( $content, $size ) . ' ' . $hashtags,
			    'hashtags' => $hashtags,
			);

			return $response;

		}// End if().

		return 'N/A';
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

		// print_r( $this->post_format );
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
	 * @since   8.0.0
	 * @access  public
	 * @param   string $url The URL to shorten.
	 * @param   string $short_url_service The shorten service. Used by the factory to build the service.
	 * @param   array  $credentials Optional. If needed the service credentials.
	 * @return string
	 * @Throws Exception If a service can not be built and defaults to passed URL.
	 */
	public function get_short_url( $url, $short_url_service, $credentials = array() ) {
	    $shortner_factory = new Rop_Shortner_Factory();
		try {
			$shortner_service = $shortner_factory->build( $short_url_service );
			if ( ! empty( $credentials ) ) {
				$shortner_service->set_credentials( $credentials );
			}
			$short_url = $shortner_service->shorten_url( $url );
		} catch ( Exception $e ) {
			$short_url = $url;
		}

		return $short_url;
	}

}
