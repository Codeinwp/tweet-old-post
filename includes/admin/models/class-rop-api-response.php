<?php
/**
 * The model structuring a standard response for the Rop_Api class.
 *
 * @link       https://themeisle.com
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/admin/models
 */

/**
 * Class Rop_Api_Response
 */
class Rop_Api_Response {

	/**
	 * Stores a value for the response code.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     string $code A status code value.
	 */
	private $code;

	/**
	 * Stores a string value linked to a status code.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     string $status A status string reflecting a status code.
	 */
	private $status;

	/**
	 * Stores a title used for front end display.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     string $title A title for the response.
	 */
	private $title;

	/**
	 * Stores the message for the response.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     string $message The message of the response.
	 */
	private $message;

	/**
	 * The response data.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $data The data for the response. This is used on the frontend.
	 */
	private $data;

	/**
	 * Stores a flag to specify if the response should be visible to the user.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     bool $silent A flag for showing the response message to the user.
	 */
	private $silent;

	/**
	 * Stores a list of allowed status codes.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $allowed_status_codes The allowed status codes for the response.
	 */
	private $allowed_status_codes = array( '200', '201', '400', '401', '403', '500' );

	/**
	 * Rop_Api_Response constructor.
	 * Sets the default response on creation.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function __construct() {
		$this->code    = '403';
		$this->title   = '';
		$this->status  = 'error';
		$this->message = 'Requested operation is not allowed. No further action will be taken.';
		$this->silent  = true;
		$this->data    = array();
	}

	/**
	 * Method to set a new status code for the response.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $code A new status code to be set.
	 *
	 * @return Rop_Api_Response $this
	 */
	public function set_code( $code ) {
		if ( in_array( $code, $this->allowed_status_codes ) ) {
			$this->code = $code;
			switch ( $code ) {
				case '200':
					$this->title  = 'Info';
					$this->status = 'info';
					break;
				case '201':
					$this->title  = 'Everything looks ok';
					$this->status = 'success';
					break;
				case '400':
				case '401':
					$this->title  = 'Oho! Something happened';
					$this->status = 'warning';
					break;
				case '403':
				default: // code 500 assumed
					$this->title  = 'An error occurred';
					$this->status = 'error';
					break;
			}
		}

		return $this;
	}

	/**
	 * Method to set the response visible for the user.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return Rop_Api_Response $this
	 */
	public function is_not_silent() {
		$this->silent = false;

		return $this;
	}

	/**
	 * Method to set a new message for the response.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $message A new message for the response.
	 *
	 * @return Rop_Api_Response $this
	 */
	public function set_message( $message ) {
		if ( isset( $message ) && $message != '' ) {
			$this->message = $message;
		}

		return $this;
	}

	/**
	 * Method to set the response data.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   array $data The data to be set.
	 *
	 * @return Rop_Api_Response $this
	 */
	public function set_data( $data = array() ) {
		if ( ! empty( $data ) ) {
			$this->data = $data;
		}

		return $this;
	}

	/**
	 * Method to return the response as a formatted array.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return array
	 */
	public function to_array() {
		return array(
			'code'         => $this->code,
			'status'       => $this->status,
			'title'        => $this->title,
			'message'      => $this->message,
			'silent'       => $this->silent,
			'show_to_user' => ! $this->silent,
			'data'         => $this->data,
		);
	}


}
