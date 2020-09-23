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

/**
 * Class Rop_Logger
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
class Rop_Logger {

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

		$this->stream = new Rop_Log_Handler( 'rop_logs' );

	}

	/**
	 * Logs a success message.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $message The message to log.
	 * @param   array  $context [optional] A context for the message, if needed.
	 */
	public function alert_success( $message = '', $context = array() ) {

		if ( ! empty( $context ) ) {
			$message = $message . ' ' . json_encode( $context );
		}

		$record = array(
			'channel' => 'rop_logs',
			'context'  => array(
				'type' => 'success',
			),
			'formatted' => $message,
		);

		$this->stream->write( $record );
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
	public function alert_error( $message = '', $context = array() ) {

		if ( ! empty( $context ) ) {
			$message = $message . ' ' . json_encode( $context );
		}

		$record = array(
			'channel' => 'rop_logs',
			'context'  => array(
				'type' => 'error',
			),
			'formatted' => $message,
		);

		$this->stream->write( $record );
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

		if ( ! empty( $context ) ) {
			$message = $message . ' ' . json_encode( $context );
		}

		$record = array(
			'channel' => 'rop_logs',
			'context'  => array(
				'type' => 'info',
			),
			'formatted' => $message,
		);

		$this->stream->write( $record );
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
	 * Catches errors and gives a more clear message in return.
	 *
	 * Some error messages received by social media are not very clear
	 * and the users might not understand what the issue is.
	 *
	 * @since 8.4.3
	 * @access public
	 *
	 * @param string $direct_message The log message to transform.
	 *
	 * @return mixed
	 */
	public function translate_messages( $direct_message ) {
		$direct_message = trim( $direct_message );
		$direct_message = preg_replace( '/\s\s+/', ' ', $direct_message ); // we need to remove extra spaces

		$translated_message = $direct_message;

		$unreadable_messages = array(
			'Value is empty : pages in Array \( \[id\] => (.*) \[pages\] => Array \( \) \)'                                              => __( 'In order to connect with Facebook it\'s required to select at least one Page in the authentification process.', 'tweet-old-post' ),
			'Callback URL not approved for this client application. Approved callback URLs can be adjusted in your application settings' => __( 'The Callback URL of your Twitter application seems to be wrong.', 'tweet-old-post' ),
			'Error connecting twitter \{"errors":\[\{"code":32,"message":"Could not authenticate you\."\}\]\}'                           => __( 'Your Twitter credentials seem to be wrong. Please double check your API Key and API Secret Key.', 'tweet-old-post' ),
		);

		foreach ( $unreadable_messages as $to_decode => $readable_message ) {
			preg_match( '/' . $to_decode . '/i', $direct_message, $output_array );
			if ( ! empty( $output_array ) ) {
				$translated_message = $readable_message;
				break;
			}
		}

		return $translated_message;
	}

	/**
	 * Will look into the longs and if there are a number of ROP_STATUS_ALERT
	 * consecutive errors, this function will return true.
	 *
	 * @since 8.4.4
	 * @access public
	 *
	 * @param array $logs Contains all the plugin logs, if any.
	 *
	 * @return bool
	 */
	public function is_status_error_necessary( $logs = array() ) {
		if ( empty( $logs ) ) {
			$data_logs = $this->get_logs();
		} else {
			$data_logs = $logs['data'];
		}

		$consecutive_errors = 0;
		$it                 = 0;

		if ( ! empty( $data_logs ) ) {

			foreach ( $data_logs as $log_entry ) {
				if ( 'error' === $log_entry['type'] ) {
					$consecutive_errors ++;
				} else {
					break;
				}
				$it ++;

				if ( $it >= ROP_STATUS_ALERT ) {
					break;
				}
			}

			if ( ROP_STATUS_ALERT === $consecutive_errors ) {
				return true;
			}
		}

		return false;

	}

}
