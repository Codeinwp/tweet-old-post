<?php
abstract class Rop_Model_Abstract {

	protected $namespace = 'rop_data';

	protected $data;

	public function __construct( $namespace = false ) {
		if ( $namespace != false ) {
			$this->namespace = $namespace;
		}
		$this->data = get_option( $this->namespace, array() );
	}

	protected function get( $key ) {
		$value = null;
		if ( isset( $this->data[ $key ] ) ) {
			$value = $this->data[ $key ];
		}

		return apply_filters( 'rop_get_key_' . $key, $value, $key );
	}

	protected function set( $key, $value = '' ) {
		if ( is_array( $this->data ) && ! array_key_exists( $key, $this->data ) ) {
			$this->data[ $key ] = '';
		}
		$this->data[ $key ] = apply_filters( 'rop_set_key_' . $key, $value );

		return update_option( $this->namespace, $this->data );
	}

}
