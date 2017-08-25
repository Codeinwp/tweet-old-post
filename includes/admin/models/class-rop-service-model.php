<?php
/**
 * The file that defines the model for services.
 *
 * A class that is used as a model for building services.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/models
 */

/**
 * Class Rop_Service_Model
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
class Rop_Service_Model extends Rop_Model_Abstract {

	/**
	 * Store the service name.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     string $service The service name as slug.
	 */
	private $service;

	/**
	 * The service specific options.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $options The model service options.
	 */
	private $options;

	/**
	 * Rop_Service_Model constructor.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $service The service name.
	 */
	public function __construct( $service ) {
		parent::__construct();
		$this->service = $service;
		$this->options = $this->get( $this->service );
		if ( $this->options == null ) {
			$this->options = array();
		}
	}

	/**
	 * The get method for a specific option.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $key The key to retrieve from service options.
	 * @return mixed
	 */
	public function get_option( $key ) {
		 $value = null;
		if ( isset( $this->options[ $key ] ) ) {
			$value = $this->options[ $key ];
		}

		return apply_filters( 'rop_get_' . $this->service . '_key_' . $key, $value, $key );
	}

	/**
	 * The set method for a specific option.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $key The option key.
	 * @param   mixed  $value The value to store.
	 * @return mixed
	 */
	public function set_option( $key, $value ) {
		if ( ! array_key_exists( $key, $this->options ) ) {
			$this->options[ $key ] = '';
		}
		$this->options[ $key ] = apply_filters( 'rop_set_' . $this->service . '_key_' . $key, $value );

		return $this->set( $this->service, $this->options );
	}
}
