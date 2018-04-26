<?php
/**
 * The file that defines the Log specific methods.
 *
 * A class that is used to log events.
 * It uses Monolog.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin
 */

use Monolog\Formatter\LineFormatter;
use Monolog\Logger;

/**
 * Class Rop_Logger
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
class Rop_Logger {

	/**
	 * An instance of the Logger class.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     Logger $logger An instance of the Logger class.
	 */
	private $logger;
	/**
	 * An stream class.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     Rop_Log_Handler $stream An instance of the stream class.
	 */
	private $stream;

	/**
	 * Rop_Logger constructor.
	 * Instantiate the Logger with specified formatter and stream.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function __construct() {

		$this->stream = new Rop_Log_Handler( 'rop_logs', ( ROP_DEBUG ) ? Logger::DEBUG : Logger::ALERT );
		$formatter    = new LineFormatter( '%message% %context.extra%' . PHP_EOL, 'd-m-Y H:i:s', false, true );
		$this->stream->setFormatter( $formatter );
		$this->logger = new Logger( 'rop_logs' );
		$this->logger->pushHandler( $this->stream );

	}

	/**
	 * Logs an info message.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $message The message to log.
	 * @param   array  $context [optional] A context for the message, if needed.
	 */
	public function info( $message = '', $context = array() ) {
		$this->logger->info( $message, $context );
	}

	/**
	 * Logs an alert error message.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $message The message to log.
	 * @param   array  $context [optional] A context for the message, if needed.
	 */
	public function alert_error( $message = '', $context = array() ) {
		$context_new = array_merge( array( 'type' => 'error' ), $context );
		$this->logger->alert( $message, $context_new );
	}

	/**
	 * Logs an alert success message.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $message The message to log.
	 * @param   array  $context [optional] A context for the message, if needed.
	 */
	public function alert_success( $message = '', $context = array() ) {
		$context_new = array_merge( array( 'type' => 'success' ), $context );
		$this->logger->alert( $message, $context_new );
	}

	/**
	 * Get all logs.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function get_logs() {
		$logs = $this->stream->get_logs();

		return $logs;
	}

	/**
	 * Clear user logs.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function clear_user_logs() {
		$this->stream->clear_logs();

	}

	/**
	 * Logs a warning message.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $message The message to log.
	 * @param   array  $context [optional] A context for the message, if needed.
	 */
	public function warn( $message = '', $context = array() ) {
		$this->logger->warn( $message );
	}

	/**
	 * Logs an error message.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $message The message to log.
	 * @param   array  $context [optional] A context for the message, if needed.
	 */
	public function error( $message = '', $context = array() ) {
		$this->logger->error( $message );
	}

}
