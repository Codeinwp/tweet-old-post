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
     * Store the service id.
     *
     * @since   8.0.0
     * @access  private
     * @var     string $service_id The service id.
     */
    private $service_id;

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
	private $service_data;

    /**
     * Handle for accessing DB data.
     *
     * @since   8.0.0
     * @access  private
     * @var     string $handle The handle for accessing DB data.
     */
	private $handle;

	/**
	 * Rop_Service_Model constructor.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $service The service name.
	 */
	public function __construct( $service_id, $service ) {
		parent::__construct();
		$this->service_id = $service_id;
		$this->service = $service;
		$this->handle = $this->service . '_' . $this->service_id;
		$this->service_data = $this->get( $this->handle );
		if ( $this->service_data == null ) {
			$this->service_data = array();
		}

		$this->service_data = wp_parse_args( $this->service_data, $this->service_data_defaults() );
	}

    /**
     * Service data defaults structure.
     *
     * @since   8.0.0
     * @access  private
     * @return array
     */
	private function service_data_defaults() {
	    $default_structure = array(
	        'id' => $this->service_id,
	        'service' => $this->service,
            'credentials' => array(),
            'available_accounts' => array(),
        );
	    return $default_structure;
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
		if ( isset( $this->service_data[ $key ] ) ) {
			$value = $this->service_data[ $key ];
		}

		return apply_filters( 'rop_get_' . $this->handle . '_key_' . $key, $value, $key );
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
		if ( ! array_key_exists( $key, $this->service_data ) ) {
			$this->service_data[ $key ] = '';
		}
		$this->service_data[ $key ] = apply_filters( 'rop_set_' . $this->handle. '_key_' . $key, $value );

		return $this->set( $this->handle, $this->service_data );
	}
}
