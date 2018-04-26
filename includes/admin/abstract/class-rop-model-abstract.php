<?php
/**
 * The file that defines the abstract class inherited by all models
 *
 * A class that is used to define the models class and utility methods.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/abstract
 */

/**
 * Class Rop_Model_Abstract
 *
 * @since   8.0.0
 * @link       https://themeisle.com/
 */
abstract class Rop_Model_Abstract {

	/**
	 * The namespace for the data inside the wp_options table.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     string $namespace The namespace to use for the model.
	 */
	protected $namespace = 'rop_data';

	/**
	 * The data used by the model.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     array $data The data used by the model.
	 */
	protected $data;

	/**
	 * Rop_Model_Abstract constructor.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   bool|string $namespace Optional. Used to specify a new namespace for a model.
	 */
	public function __construct( $namespace = false ) {
		if ( $namespace != false ) {
			$this->namespace = $namespace;
		}
		$this->data = get_option( $this->namespace, array() );
	}

	/**
	 * The get method for the model data.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @param   string $key The key to retrieve from model data.
	 * @return mixed
	 */
	protected function get( $key ) {
		$value = null;
		if ( isset( $this->data[ $key ] ) ) {
			$value = $this->data[ $key ];
		}

		return apply_filters( 'rop_get_key_' . $key, $value, $key );
	}

	/**
	 * The set method for the model data.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @param   string $key The key to set inside the model data.
	 * @param   mixed  $value The value for the specified key.
	 * @return mixed
	 */
	protected function set( $key, $value = '' ) {
		if ( is_array( $this->data ) && ! array_key_exists( $key, $this->data ) ) {
			$this->data[ $key ] = '';
		}
		$this->data[ $key ] = apply_filters( 'rop_set_key_' . $key, $value );
		return update_option( $this->namespace, $this->data );
	}

}
