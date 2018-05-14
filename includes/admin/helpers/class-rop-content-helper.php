<?php
/**
 * The file that defines the class to manage content text
 *
 * A class that is used manipulate text content.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/helpers
 */

/**
 * Class Rop_Content_Helper
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
class Rop_Content_Helper {

	/**
	 * The maximum length of the desired resulting string.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     int $length The maximum length. Default 160.
	 */
	private $length;

	/**
	 * Flag to specify if ellipses should be used.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     bool $end_ellipse Flag for use of ellipses. Default false.
	 */
	private $end_ellipse;

	/**
	 * The ellipse to use.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     string $ellipse The ellipse string.
	 */
	private $ellipse = '...';

	/**
	 * Rop_Content_Helper constructor.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   int  $length A maximum length to use for processing the string. Default 160.
	 * @param   bool $end_ellipse Flag to specify if ellipses should be use. Default false.
	 */
	public function __construct( $length = 160, $end_ellipse = false ) {
		$this->end_ellipse = $end_ellipse;

		$length       = $this->adjust_for_ellipse( $length );
		$this->length = $length;
	}

	/**
	 * Utility method to adjust length with respects to ellipse.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   int $length The maximum length targeted.
	 *
	 * @return int
	 */
	private function adjust_for_ellipse( $length ) {
		if ( $this->end_ellipse ) {
			// remove ellipse chars from length to accommodate ellipse and 1 space.
			$length = $length - strlen( $this->ellipse ) - 1;
		}

		return $length;
	}

	/**
	 * Utility method to enable or disable the use o ellipse.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   bool   $use Flag to specify if ellipse should be used. Default true.
	 * @param   string $ellipse A string to use as ellipse. Default '...'.
	 */
	public function use_ellipse( $use = true, $ellipse = '...' ) {
		$this->end_ellipse = $use;
		$this->ellipse     = $ellipse;
	}

	/**
	 * Method to truncate a string with respect to not breaking words.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string   $string The text to process.
	 * @param   bool|int $new_length Optional. Param for specifying a new desired maximum length. Default false.
	 *
	 * @return string
	 */
	public function token_truncate( $string, $new_length = false ) {
		if ( $new_length ) {
			$this->length = $this->adjust_for_ellipse( $new_length );
		}

		$parts       = preg_split( '/([\s\n\r]+)/', $string, null, PREG_SPLIT_DELIM_CAPTURE );
		$parts_count = count( $parts );

		$length = 0;
		for ( $last_part = 0; $last_part < $parts_count; ++ $last_part ) {
			$length += strlen( $parts[ $last_part ] );
			if ( $length > $this->length ) {
				break;
			}
		}

		$output = rtrim( implode( array_slice( $parts, 0, $last_part ) ) );
		/**
		 * If the string contains a single word, larger than the length,
		 * we should get the substring of it.
		 */
		if ( empty( $output ) && $parts_count === 1 ) {
			$output = substr( $string, 0, $new_length );
		}
		// add ellipse only if set and originating text is longer than set length
		if ( $this->end_ellipse && strlen( $string ) > $this->length ) {
			$output .= ' ' . $this->ellipse;
		}

		return $output;
	}

	/**
	 * Imported from Tweet Old Post v7.*.
	 * Author Daniel Brown (contact info at http://ymb.tc/contact)
	 * Includes hashtag in $tweet_content if possible
	 * to save space, avoid redundancy, and allow more hashtags
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param  string $string The regular text in the Tweet.
	 * @param  string $hashtag The hashtag to include in $string, if possible.
	 *
	 * @return mixed  The new $string or false if the $string doesn't contain the $hashtag
	 */
	public function mark_hashtags( $string, $hashtag ) {
		$location = stripos( $string, ' ' . $hashtag . ' ' );
		if ( $location !== false ) {
			$location ++; // the actual # location will be past the space
		} elseif ( stripos( $string, $hashtag . ' ' ) === 0 ) {
			// see if the hashtag is at the beginning
			$location = 0;
		} elseif ( stripos( rtrim( $string, " \t." ), ' ' . $hashtag ) !== false && stripos( rtrim( $string, " \t." ), ' ' . $hashtag ) + strlen( ' ' . $hashtag ) == strlen( rtrim( $string, " \t." ) ) ) {
			// see if the hashtag is at the end
			$location = stripos( rtrim( $string, " \t." ), ' ' . $hashtag ) + 1;
		}
		if ( $location !== false ) {
			return substr_replace( $string, '#', $location, 0 );
		}

		return false;
	}

}
