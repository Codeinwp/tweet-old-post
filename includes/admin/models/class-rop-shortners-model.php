<?php
/**
 * The model for the shortners options of the plugin.
 *
 * @link       https://themeisle.com
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/admin/models
 */

/**
 * Class Rop_Shortners_Model
 */
class Rop_Shortners_Model extends Rop_Model_Abstract {

	/**
	 * Stores the credentials from DB.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @var     array $credentials The credentials.
	 */
	private $credentials;

	/**
	 * Stores the shortner name.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @var     string $shortner_name The name of the shortner.
	 */
	private $shortner_name;

	/**
	 * Rop_Shortners_Model constructor.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $shortner The name of the shortner.
	 * @param   array  $default_credentials The default credentials for shortner.
	 */
	public function __construct( $shortner, $default_credentials ) {
		$this->shortner_name = str_replace( '.', '', $shortner );
		parent::__construct( 'rop_shortners_' . $this->shortner_name );
		$this->credentials = ( $this->get( $this->shortner_name . '_credentials' ) != null ) ? $this->get( $this->shortner_name . '_credentials' ) : $default_credentials;

	}

	/**
	 * Method to save credentials for shortner.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   array $credentials The credentials to store.
	 * @return mixed
	 */
	public function save( $credentials ) {
		return $this->set( $this->shortner_name . '_credentials', $credentials );
	}

	/**
	 * Method to retrieve credentials of service.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return array|mixed
	 */
	public function credentials() {
		$this->credentials = ( $this->get( $this->shortner_name . '_credentials' ) != null ) ? $this->get( $this->shortner_name . '_credentials' ) : $this->credentials;
		return $this->credentials;
	}
}
