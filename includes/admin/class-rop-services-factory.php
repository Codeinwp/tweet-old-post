<?php
/**
 * The file that defines the factory for services plugin class
 *
 * A class that is used as a factory for building services.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin
 */

/**
 * Class Rop_Services_Factory
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
class Rop_Services_Factory {

	/**
	 * Build method for factory.
	 *
	 * @since   8.0.0
	 * @accesss public
	 * @param   string $service_name The service name.
	 * @return Rop_Services_Abstract
	 * @throws Exception An PHP exception is thrown if service not found.
	 */
	public static function build( $service_name ) {
		$service = 'Rop_' . str_replace( '-', '_', ucwords( $service_name ) ) . '_Service';
		if ( class_exists( $service ) ) {
			return new $service;
		}
		// @codeCoverageIgnoreStart
		throw new Exception( 'Invalid service name given.' );
		// @codeCoverageIgnoreEnd
	}
}
