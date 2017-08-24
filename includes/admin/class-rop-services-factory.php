<?php
class Rop_Services_Factory {

    /**
     * @param   string $service_name The service name.
     * @return Rop_Services_Abstract
     * @throws Exception
     */
	public static function build( $service_name ) {
		$service = 'Rop_' . str_replace( '-','_', ucwords( $service_name ) ) . '_Service';
		if ( class_exists( $service ) ) {
			return new $service;
		}
		// @codeCoverageIgnoreStart
		throw new Exception( 'Invalid service name given.' );
		// @codeCoverageIgnoreEnd
	}
}
