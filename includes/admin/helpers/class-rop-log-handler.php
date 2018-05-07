<?php
/**
 *
 * Custom monolog implementation for log handling.
 *
 * @package     rop
 * @subpackage  rop/helpers
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since
 */

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

/**
 * Class Rop_Log_Handler, procesor for user logs.
 */
class Rop_Log_Handler extends AbstractProcessingHandler {
	/**
	 * List of logs.
	 *
	 * @var array $current_logs List  of logs.
	 */
	static private $current_logs;
	/**
	 * Hold initialization status.
	 *
	 * @var bool Init status.
	 */
	private $initialized = false;
	/**
	 * Option where to save the logs.
	 *
	 * @var string $namespace Option key.
	 */
	private $namespace;
	/**
	 * How many logs to save.
	 *
	 * @var int Number of logs.
	 */
	private $limit = 100;

	/**
	 * Rop_Log_Handler constructor.
	 *
	 * @param string $option_name Option where to save this.
	 * @param int    $level Level of log.
	 * @param bool   $bubble Bubble.
	 */
	public function __construct( $option_name, $level = Logger::DEBUG, $bubble = true ) {
		$this->namespace = $option_name;

		parent::__construct( $level, $bubble );
	}

	/**
	 * Get all the logs available.
	 *
	 * @return array Logs array.
	 */
	public function get_logs() {
		if ( ! $this->initialized ) {
			$this->initialize();
		}

		return array_reverse( self::$current_logs );
	}

	/**
	 * Initilize logger.
	 */
	private function initialize() {
		$current_logs = get_option( $this->namespace, array() );
		if ( ! is_array( $current_logs ) ) {
			$current_logs = array();
		}
		self::$current_logs = $current_logs;
		$this->initialized  = true;
	}

	/**
	 * Clear active logs.
	 *
	 * @return void
	 */
	public function clear_logs() {
		if ( ! $this->initialized ) {
			$this->initialize();
		}

		self::$current_logs = array();
		$this->save_logs( array() );
	}

	/*
	 * Get all logs.
	 */

	/**
	 * Save logs utility.
	 * Check the logs limit is reached, truncate the logs.
	 *
	 * @param array $logs Logs to save.
	 */
	private function save_logs( $logs ) {
		update_option( $this->namespace, $logs, 'no' );
	}

	/**
	 * Write log handler.
	 *
	 * @param array $record Record written.
	 */
	protected function write( array $record ) {
		if ( ! $this->initialized ) {
			$this->initialize();
		}
		self::$current_logs[] = array(
			'channel' => $record['channel'],
			'type'    => isset( $record['context']['type'] ) ? $record['context']['type'] : 'info',
			'level'   => $record['level'],
			'message' => $record['formatted'],
			'time'    => Rop_Scheduler_Model::get_current_time(),
		);
		if ( count( self::$current_logs ) > $this->limit ) {

			self::$current_logs = array_slice( self::$current_logs, count( self::$current_logs ) - $this->limit, $this->limit );
		}
		$this->save_logs( self::$current_logs );
	}
}
