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
		$filtered_post['mimetype']          = empty( $filtered_post['post_image'] ) ? '' : wp_check_filetype( $filtered_post['post_image'] );
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

		if ( class_exists( 'Rop_Pro_Post_Format_Helper' ) ) {
			$pro_format_helper = new Rop_Pro_Post_Format_Helper;
		}

		/**
		 * Content edited through queue.
		 */

		$custom_content = get_post_meta( $post_id, '_rop_edit_' . md5( $this->account_id ), true );
		if ( ! empty( $custom_content ) ) {
			$share_content = isset( $custom_content['text'] ) ? $custom_content['text'] : '';
			if ( isset( $pro_format_helper ) ) {
				$share_content = $pro_format_helper->rop_replace_magic_tags( $share_content, $post_id );
			}
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

			if ( isset( $pro_format_helper ) ) {
					$share_content = $pro_format_helper->rop_replace_magic_tags( $share_content, $post_id );
			}

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
		$result = $this->make_hashtags( $base_content, $content_helper, $post_id );

		$base_content  = $content_helper->token_truncate( $result['content'], $max_length );
		$custom_length = $this->get_custom_length();

		$hashtags = $result['hashtags'];
		$size     = $max_length - ( $this->string_length( $hashtags ) ) - $custom_length;
		if ( $size <= 0 ) {
			$size = $max_length;
		}
		$service = $this->get_service();
		if ( $service === 'twitter' && $this->post_format['include_link'] ) {
			$size = $size - 24;
		}
		$base_content = $content_helper->token_truncate( $base_content, $size );

		$base_content = $this->append_custom_text( $base_content, $post_id );
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

		return $this->string_length( $this->post_format['custom_text'] ) + 1; // For the extra space

	}

	/**
	 * Wrapper around strlen/mb_strlen for string length.
	 *
	 * @param string $string String to check.
	 *
	 * @return int String length.
	 */
	public function string_length( $string ) {
		if ( function_exists( 'mb_strlen ' ) ) {
			return mb_strlen( $string );
		}

		return strlen( $string );
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
				'content'         => $content,
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
				'content'         => $content,
			);
		}
		$result   = $this->clean_hashtags( $result );
		$hashtags = '';
		$result   = array_filter( $result, [ $this, 'string_length' ] );
		$result   = array_map(
			function ( $value ) {
				return str_replace( '#', '', $value );
			},
			$result
		);

		$service = $this->get_service();

		foreach ( $result as $hashtag ) {
			if ( $content_helper->mark_hashtags( $content, $hashtag ) !== false && $service !== 'tumblr' ) { // if the hashtag exists in $content
				$content = $content_helper->mark_hashtags( $content, $hashtag ); // simply add a # there
				$hashtags_length --; // subtract 1 for the # we added to $content
			} elseif ( $this->string_length( $hashtag . $hashtags ) <= $hashtags_length || $hashtags_length == 0 ) {
				$hashtags = $hashtags . ' #' . preg_replace( '/-/', '', strtolower( $hashtag ) );
			}
		}

		return array(
			'hashtags_length' => $hashtags_length,
			'hashtags'        => $hashtags,
			'content'         => $content,
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

		if ( class_exists( 'Rop_Pro_Post_Format_Helper' ) ) {
				$pro_format_helper = new Rop_Pro_Post_Format_Helper;
		}

		if ( ! isset( $pro_format_helper ) ) {
			$post_categories = get_the_category( $post_id );
			if ( empty( $post_categories ) ) {
				return array();
			}
			return wp_list_pluck( $post_categories, 'name' );
		} else {
			return $pro_format_helper->pro_get_categories_hashtags( $post_id );
		}

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

		if ( class_exists( 'Rop_Pro_Post_Format_Helper' ) ) {
				$pro_format_helper = new Rop_Pro_Post_Format_Helper;
		}

		if ( ! isset( $pro_format_helper ) ) {
			$tags = wp_get_post_tags( $post_id );
			if ( empty( $tags ) ) {
				return array();
			}
			return wp_list_pluck( $tags, 'name' );
		} else {
			return $pro_format_helper->pro_get_tags_hashtags( $post_id );
		}

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
		// split custom hashtags by space or pound sign
		$hashtag = preg_split( '/\s|#/', $hashtag );

		if ( empty( $hashtag ) ) {
			return array();
		}
		if ( is_string( $hashtag ) ) {
			return [ $hashtag ];
		}

		return $hashtag;
	}

	/**
	 * Removes certain characters from hashtags.
	 *
	 * @since   8.1.0
	 * @access  private
	 *
	 * @param   array $hashtags The hashtags to clean.
	 *
	 * @return array
	 */
	private function clean_hashtags( $hashtags ) {
		// WP terms with > and < are stored as entities
		if ( is_string( $hashtags ) ) {
			$hashtags = [ $hashtags ];
		}
		if ( empty( $hashtags ) ) {
			return [];
		}
		$hashtags = array_map(
			function ( $value ) {

				preg_match_all( Rop_Post_Format_Helper::valid_hashtag_regex(), $value, $matches );

				if ( ! isset( $matches[2] ) || empty( $matches[2] ) ) {
					return '';
				}

				return implode( '', $matches[2] );
			},
			$hashtags
		);

		return $hashtags;
	}

	/**
	 * Get matching hashtag regex.
	 * Based on https://github.com/ngnpope/twitter-text-php
	 *
	 * @return string Regex pattern.
	 */
	public static function valid_hashtag_regex() {
		// Expression to match latin accented characters.
		//
		// 0x00C0-0x00D6
		// 0x00D8-0x00F6
		// 0x00F8-0x00FF
		// 0x0100-0x024f
		// 0x0253-0x0254
		// 0x0256-0x0257
		// 0x0259
		// 0x025b
		// 0x0263
		// 0x0268
		// 0x026f
		// 0x0272
		// 0x0289
		// 0x028b
		// 0x02bb
		// 0x0300-0x036f
		// 0x1e00-0x1eff
		//
		// Excludes 0x00D7 - multiplication sign (confusable with 'x').
		// Excludes 0x00F7 - division sign.
		$tmp['latin_accents'] = '\x{00c0}-\x{00d6}\x{00d8}-\x{00f6}\x{00f8}-\x{00ff}';
		$tmp['latin_accents'] .= '\x{0100}-\x{024f}\x{0253}-\x{0254}\x{0256}-\x{0257}';
		$tmp['latin_accents'] .= '\x{0259}\x{025b}\x{0263}\x{0268}\x{026f}\x{0272}\x{0289}\x{028b}\x{02bb}\x{0300}-\x{036f}\x{1e00}-\x{1eff}';
		// Expression to match non-latin characters.
		//
		// Cyrillic (Russian, Ukranian, ...):
		//
		// 0x0400-0x04FF Cyrillic
		// 0x0500-0x0527 Cyrillic Supplement
		// 0x2DE0-0x2DFF Cyrillic Extended A
		// 0xA640-0xA69F Cyrillic Extended B
		$tmp['non_latin_hashtag_chars'] = '\x{0400}-\x{04ff}\x{0500}-\x{0527}\x{2de0}-\x{2dff}\x{a640}-\x{a69f}';
		// Hebrew:
		//
		// 0x0591-0x05bf Hebrew
		// 0x05c1-0x05c2
		// 0x05c4-0x05c5
		// 0x05c7
		// 0x05d0-0x05ea
		// 0x05f0-0x05f4
		// 0xfb12-0xfb28 Hebrew Presentation Forms
		// 0xfb2a-0xfb36
		// 0xfb38-0xfb3c
		// 0xfb3e
		// 0xfb40-0xfb41
		// 0xfb43-0xfb44
		// 0xfb46-0xfb4f
		$tmp['non_latin_hashtag_chars'] .= '\x{0591}-\x{05bf}\x{05c1}-\x{05c2}\x{05c4}-\x{05c5}\x{05c7}\x{05d0}-\x{05ea}\x{05f0}-\x{05f4}';
		$tmp['non_latin_hashtag_chars'] .= '\x{fb12}-\x{fb28}\x{fb2a}-\x{fb36}\x{fb38}-\x{fb3c}\x{fb3e}\x{fb40}-\x{fb41}\x{fb43}-\x{fb44}\x{fb46}-\x{fb4f}';
		// Arabic:
		//
		// 0x0610-0x061a Arabic
		// 0x0620-0x065f
		// 0x066e-0x06d3
		// 0x06d5-0x06dc
		// 0x06de-0x06e8
		// 0x06ea-0x06ef
		// 0x06fa-0x06fc
		// 0x06ff
		// 0x0750-0x077f Arabic Supplement
		// 0x08a0        Arabic Extended A
		// 0x08a2-0x08ac
		// 0x08e4-0x08fe
		// 0xfb50-0xfbb1 Arabic Pres. Forms A
		// 0xfbd3-0xfd3d
		// 0xfd50-0xfd8f
		// 0xfd92-0xfdc7
		// 0xfdf0-0xfdfb
		// 0xfe70-0xfe74 Arabic Pres. Forms B
		// 0xfe76-0xfefc
		$tmp['non_latin_hashtag_chars'] .= '\x{0610}-\x{061a}\x{0620}-\x{065f}\x{066e}-\x{06d3}\x{06d5}-\x{06dc}\x{06de}-\x{06e8}\x{06ea}-\x{06ef}\x{06fa}-\x{06fc}\x{06ff}';
		$tmp['non_latin_hashtag_chars'] .= '\x{0750}-\x{077f}\x{08a0}\x{08a2}-\x{08ac}\x{08e4}-\x{08fe}';
		$tmp['non_latin_hashtag_chars'] .= '\x{fb50}-\x{fbb1}\x{fbd3}-\x{fd3d}\x{fd50}-\x{fd8f}\x{fd92}-\x{fdc7}\x{fdf0}-\x{fdfb}\x{fe70}-\x{fe74}\x{fe76}-\x{fefc}';
		//
		// 0x200c-0x200c Zero-Width Non-Joiner
		// 0x0e01-0x0e3a Thai
		$tmp['non_latin_hashtag_chars'] .= '\x{200c}\x{0e01}-\x{0e3a}';
		// Hangul (Korean):
		//
		// 0x0e40-0x0e4e Hangul (Korean)
		// 0x1100-0x11FF Hangul Jamo
		// 0x3130-0x3185 Hangul Compatibility Jamo
		// 0xA960-0xA97F Hangul Jamo Extended A
		// 0xAC00-0xD7AF Hangul Syllables
		// 0xD7B0-0xD7FF Hangul Jamo Extended B
		// 0xFFA1-0xFFDC Half-Width Hangul
		$tmp['non_latin_hashtag_chars'] .= '\x{0e40}-\x{0e4e}\x{1100}-\x{11ff}\x{3130}-\x{3185}\x{a960}-\x{a97f}\x{ac00}-\x{d7af}\x{d7b0}-\x{d7ff}\x{ffa1}-\x{ffdc}';
		// Expression to match other characters.
		//
		// 0x30A1-0x30FA   Katakana (Full-Width)
		// 0x30FC-0x30FE   Katakana (Full-Width)
		// 0xFF66-0xFF9F   Katakana (Half-Width)
		// 0xFF10-0xFF19   Latin (Full-Width)
		// 0xFF21-0xFF3A   Latin (Full-Width)
		// 0xFF41-0xFF5A   Latin (Full-Width)
		// 0x3041-0x3096   Hiragana
		// 0x3099-0x309E   Hiragana
		// 0x3400-0x4DBF   Kanji (CJK Extension A)
		// 0x4E00-0x9FFF   Kanji (Unified)
		// 0x20000-0x2A6DF Kanji (CJK Extension B)
		// 0x2A700-0x2B73F Kanji (CJK Extension C)
		// 0x2B740-0x2B81F Kanji (CJK Extension D)
		// 0x2F800-0x2FA1F Kanji (CJK supplement)
		// 0x3003          Kanji (CJK supplement)
		// 0x3005          Kanji (CJK supplement)
		// 0x303B          Kanji (CJK supplement)
		$tmp['cj_hashtag_characters'] = '\x{30A1}-\x{30FA}\x{30FC}-\x{30FE}\x{FF66}-\x{FF9F}\x{FF10}-\x{FF19}\x{FF21}-\x{FF3A}\x{FF41}-\x{FF5A}\x{3041}-\x{3096}\x{3099}-\x{309E}\x{3400}-\x{4DBF}\x{4E00}-\x{9FFF}\x{3003}\x{3005}\x{303B}\x{020000}-\x{02a6df}\x{02a700}-\x{02b73f}\x{02b740}-\x{02b81f}\x{02f800}-\x{02fa1f}';

		$tmp['hashtag_alpha']        = '[a-z_' . $tmp['latin_accents'] . $tmp['non_latin_hashtag_chars'] . $tmp['cj_hashtag_characters'] . ']';
		$tmp['hashtag_alphanumeric'] = '[a-z0-9_' . $tmp['latin_accents'] . $tmp['non_latin_hashtag_chars'] . $tmp['cj_hashtag_characters'] . ']';
		$tmp['hashtag_boundary']     = '(?:\A|\z|[^&a-z0-9_' . $tmp['latin_accents'] . $tmp['non_latin_hashtag_chars'] . $tmp['cj_hashtag_characters'] . '])';

		$tmp['hashtag'] = '(' . $tmp['hashtag_boundary'] . ')(' . $tmp['hashtag_alphanumeric'] . '*' . $tmp['hashtag_alpha'] . $tmp['hashtag_alphanumeric'] . '*)';

		$valid_hashtag = '/' . $tmp['hashtag'] . '(?=(.*|$))/iu';

		return $valid_hashtag;
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
	private function append_custom_text( $content, $post_id ) {

		if ( class_exists( 'Rop_Pro_Post_Format_Helper' ) ) {
			$pro_format_helper = new Rop_Pro_Post_Format_Helper;
		}

		if ( isset( $pro_format_helper ) ) {
			$this->post_format['custom_text'] = $pro_format_helper->rop_replace_magic_tags( $this->post_format['custom_text'], $post_id );
		}

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

		$post_url        = apply_filters( 'rop_raw_post_url', $post_url, $post );
		$global_settings = new Rop_Global_Settings();
		$settings_model  = new Rop_Settings_Model();

		if ( $settings_model->get_ga_tracking() && $global_settings->license_type() <= 0 ) {
			$params                 = array();
			$params['utm_source']   = 'ReviveOldPost';
			$params['utm_medium']   = 'social';
			$params['utm_campaign'] = 'ReviveOldPost';
			$post_url               = add_query_arg( $params, $post_url );
		}

		if ( $settings_model->get_ga_tracking() && $global_settings->license_type() > 0 ) {
			$utm_source   = $this->get_utm_tags( 'utm_campaign_source' );
			$utm_medium   = $this->get_utm_tags( 'utm_campaign_medium' );
			$utm_campaign = $this->get_utm_tags( 'utm_campaign_name' );

			$params                 = array();
			$params['utm_source']   = empty( $utm_source ) ? 'ReviveOldPost' : $utm_source;
			$params['utm_medium']   = empty( $utm_medium ) ? 'social' : $utm_medium;
			$params['utm_campaign'] = empty( $utm_campaign ) ? 'ReviveOldPost' : $utm_campaign;
			$post_url               = empty( $post_url ) ? '' : add_query_arg( $params, $post_url );

		}

		return $post_url;
	}

	/**
	 * Gets UTM tags
	 *
	 * @since   8.1.0
	 * @access  public
	 *
	 * @param   array $tag The UTM tag to pull.
	 *
	 * @return string
	 */
	public function get_utm_tags( $tag ) {
		return $this->set_utm_tags( $tag );
	}

	/**
	 * Sets UTM tags
	 *
	 * @since   8.1.0
	 * @access  public
	 *
	 * @param   array $tag The UTM tag to set from get_utm_tags().
	 *
	 * @return string
	 */
	public function set_utm_tags( $tag ) {
		$tags = array();

		$tags['utm_campaign_source'] = $this->get_service();
		$tags['utm_campaign_medium'] = $this->post_format['utm_campaign_medium'];
		$tags['utm_campaign_name']   = $this->post_format['utm_campaign_name'];

		return urlencode( $tags[ $tag ] );
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

		if ( get_post_type( $post_id ) == 'attachment' ) {
			return wp_get_attachment_url( $post_id );
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
