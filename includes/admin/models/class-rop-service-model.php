<?php
class Rop_Service_Model extends Rop_Model_Abstract {

    private $service;

    private $options;

    public function __construct( $service ) {
        parent::__construct();
        $this->service = $service;
        $this->options = $this->get( $this->service );
        if( $this->options == null ) {
            $this->options = array();
        }
    }

    public function get_option( $key ) {
         $value = null;
        if( isset( $this->options[$key] ) ) {
            $value = $this->options[$key];
        }

        return apply_filters( 'rop_get_' . $this->service . '_key_' . $key, $value, $key );
    }

    public function set_option( $key, $value ) {
        if ( ! array_key_exists( $key, $this->options ) ) {
            $this->options[ $key ] = '';
        }
        $this->options[ $key ] = apply_filters( 'rop_set_' . $this->service . '_key_' . $key, $value );

        return $this->set( $this->service, $this->options );
    }
}