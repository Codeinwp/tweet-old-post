<?php
/**
 * The file that defines the Google My Business Service specifics.
 *
 * A class that is used to interact with Google My Business.
 * It extends the Rop_Services_Abstract class.
 *
 * @link       https://themeisle.com/
 * @since      8.5.9
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/services
 */

/**
 * Class Rop_GMB_Service
 *
 * @since   8.5.9
 * @link    https://themeisle.com/
 */
class Rop_GMB_Service {

  /**
	 * Defines the service name in slug format.
	 *
	 * @since  8.5.9
	 * @access protected
	 * @var    string $service_name The service name.
	 */
	protected $service_name = 'gmb';

  /**
   * Method to expose desired endpoints.
   * This should be invoked by the Factory class
   * to register all endpoints at once.
   *
   * @since  8.5.9
   * @access public
   */
  public function expose_endpoints() {
    return;
  }

}
