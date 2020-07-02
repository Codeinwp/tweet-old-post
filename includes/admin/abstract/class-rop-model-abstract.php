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
	 * @param bool|string $namespace Optional. Used to specify a new namespace for a model.
	 *
	 * @since   8.0.0
	 * @access  public
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
	 * @param string $key The key to retrieve from model data.
	 *
	 * @return mixed
	 * @since   8.0.0
	 * @access  protected
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
	 * @param string $key The key to set inside the model data.
	 * @param mixed  $value The value for the specified key.
	 * @param bool   $refresh Whether to refresh the rop_data property in class with new rop_data option values.
	 *
	 * @return mixed
	 * @since   8.0.0
	 * @access  protected
	 */
	protected function set( $key, $value = '', $refresh = false ) {
		if ( is_array( $this->data ) && ! array_key_exists( $key, $this->data ) ) {
			$this->data[ $key ] = '';
		}

	    if ( $refresh ) {
				  $this->data = get_option( 'rop_data' );
		}

		$this->data[ $key ] = apply_filters( 'rop_set_key_' . $key, $value );

		return update_option( $this->namespace, $this->data );
	}


	/**
	 * This method will treat the exception that may exist in Linkedin service account key.
	 *
	 * @param string $index The long concatenated string.
	 * @param bool   $is_treat_any Treat any exception, not just for Linkedin.
	 *
	 * @return array
	 * @since 8.5.3
	 */
	protected function handle_underscore_exception( $index, $is_treat_any = false ) {

		$explode_index    = explode( '_', $index );
		$count_underscore = count( $explode_index );

		list( $service, $service_key, $account_id ) = $explode_index;

		if ( 'linkedin' === $service || $is_treat_any ) {
			// Exception: When there are 2 extra underscores e.g. "linkedin_33_test_33_test"
			if ( 5 === $count_underscore ) {
				$service_key = $explode_index[1] . '_' . $explode_index[2];
				$account_id  = $explode_index[3] . '_' . $explode_index[4];
			} elseif ( 4 === $count_underscore ) {
				// Exception: When there is one extra underscore "linkedin_33_test_33test"
				$service_key = $explode_index[1] . '_' . $explode_index[2];
				$account_id  = $explode_index[3];
			}
		}

		$return_correct_format = array(
			$service,
			$service_key,
			$account_id,
		);

		return $return_correct_format;
	}

}
