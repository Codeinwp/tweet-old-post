<?php
/**
 * The file that defines the class to manage service exceptions
 *
 * A class that is used to define methods for handeling exceptions.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/helpers
 */

/**
 * Class Rop_Exception_Handler
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
class Rop_Exception_Handler {

	/**
	 * Stores an array of exceptions.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $exception The exceptions array.
	 */
	private $exception = array();
	/**
	 * Monolog logger.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $logger The Monolog instance.
	 */
	private $logger;

	/**
	 * Rop_Exception_Handler constructor.
	 */
	public function __construct() {
		$this->logger = new Rop_Logger();
	}

	/**
	 * Utility method to parse Facebook exceptions.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   object $helper The Facebook helper object.
	 *
	 * @return string
	 */
	public function get_fb_exeption_message( $helper ) {
		$message = 'Error: ' . $helper->getError() . PHP_EOL;
		$message .= 'Error Code: ' . $helper->getErrorCode() . PHP_EOL;
		$message .= 'Error Reason: ' . $helper->getErrorReason() . PHP_EOL;
		$message .= 'Error Description: ' . $helper->getErrorDescription() . PHP_EOL;

		return $message;
	}

	/**
	 * Utility method to add to the exceptions array.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $message An exception message.
	 */
	public function register_exception( $message ) {
		$this->exception[] = $message;
	}

	/**
	 * Sets the headers and message as a REST response to an exception.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $header The header as rfc2616 HTTP compliant code.
	 * @param   string $message The message.
	 */
	public function throw_exception( $header, $message ) {

		$this->logger->alert_error( $message );
		if ( ! ROP_DEBUG ) {
			return;
		}
		header( 'HTTP/1.0 ' . $header );
		$backtrace = wp_debug_backtrace_summary();
		echo $message . ' BackTrace: ' . $backtrace;
		exit;
	}

}
