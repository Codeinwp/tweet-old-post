<?php
/**
 * The file that defines the class to manage telegram api request.
 *
 * A class that is used manipulate text content.
 *
 * @link       https://themeisle.com/
 * @since      9.1.3
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/helpers
 */

/**
 * Class Rop_Content_Helper
 *
 * @since   9.1.3
 * @link    https://themeisle.com/
 */
class Rop_Telegram_Api {

	/**
	 * Holds response
	 *
	 * @var mixed.
	 */
	private static $response = array();

	/**
	 * Holds the API url.
	 *
	 * @var string.
	 */
	private $api_url = 'https://api.telegram.org/bot';

	/**
	 * Holds the logger
	 *
	 * @since   9.1.3
	 * @access  protected
	 * @var     Rop_Logger $logger The logger handler.
	 */
	protected $logger;

	/**
	 * Construct.
	 *
	 * @param string $token API token.
	 */
	public function __construct( $token = '' ) {
		if ( empty( $token ) ) {
			return;
		}
		$this->logger   = new Rop_Logger();
		$this->api_url .= $token;
	}

	/**
	 * Get profile data.
	 *
	 * @throws Throwable Utils unwrap.
	 */
	public function get_user_accounts() {
		try {
			$response = wp_remote_get(
				$this->api_url . '/getMe',
				array(
					'headers' => array(
						'timeout' => 120,
					),
				)
			);

			if ( is_wp_error( $response ) ) {
				$this->logger->alert_error( 'Telegram accounts verify credentials API Error: ' . $response->get_error_message() );
				return false;
			}
			$response = wp_remote_retrieve_body( $response );
			$this->logger->info( 'Telegram accounts verify credentials API response: ' . print_r( $response, true ) );
			$response = json_decode( $response );

			if ( empty( $response->ok ) ) {
				$this->logger->alert_error( 'Telegram accounts verify credentials API Error: ' . print_r( $response, true ) );
				return false;
			}
			return $response->result;
		} catch ( \Exception $e ) {
			$this->logger->alert_error( 'Telegram API Error: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Get user profile photo.
	 *
	 * @param int $user_id Telegram user ID.
	 * @return string
	 */
	public function get_profile_photo( $user_id = 0 ) {
		try {
			$response = wp_remote_get(
				$this->api_url . '/getUserProfilePhotos',
				array(
					'headers' => array(
						'timeout' => 120,
					),
					'body'    => array(
						'user_id' => $user_id,
					),
				)
			);

			if ( is_wp_error( $response ) ) {
				$this->logger->alert_error( 'Telegram Profile Photos API Error: ' . $response->get_error_message() );
				return '';
			}
			$response = wp_remote_retrieve_body( $response );
			$this->logger->info( 'Telegram Profile Photos API response: ' . print_r( $response, true ) );
			$response = json_decode( $response );

			if ( empty( $response->ok ) ) {
				$this->logger->alert_error( 'Telegram Profile Photos API Error: ' . print_r( $response, true ) );
				return '';
			}
			if ( isset( $response->result ) ) {
				$photos = reset( $response->result->photos[0] );
				if ( isset( $photos->file_id ) ) {
					$response = wp_remote_get(
						$this->api_url . '/getFile',
						array(
							'headers' => array(
								'timeout' => 120,
							),
							'body'    => array(
								'file_id' => $photos->file_id,
							),
						)
					);
					if ( is_wp_error( $response ) ) {
						$this->logger->alert_error( 'Telegram File API Error: ' . $response->get_error_message() );
						return '';
					}
					$response = wp_remote_retrieve_body( $response );
					$this->logger->info( 'Telegram File API  response: ' . print_r( $response, true ) );
					$response = json_decode( $response );
					if ( empty( $response->ok ) ) {
						$this->logger->alert_error( 'Telegram File API Error: ' . print_r( $response, true ) );
						return '';
					}
					return str_replace( '/bot', '/file/bot', $this->api_url ) . '/' . $response->result->file_path;
				}
			}
			return '';
		} catch ( \Exception $e ) {
			$this->logger->alert_error( 'Telegram API Error: ' . $e->getMessage() );
		}
		return '';
	}

	/**
	 * Send message.
	 *
	 * @param array $args API args.
	 *
	 * @return false|object
	 */
	public function send_message( $args = array() ) {
		try {
			$message_type = 'sendMessage';
			if ( isset( $args['photo'] ) ) {
				$message_type = 'sendPhoto';
			}
			if ( isset( $args['video'] ) ) {
				$message_type = 'sendVideo';
			}
			$response = wp_remote_get(
				$this->api_url . '/' . $message_type,
				array(
					'headers' => array(
						'timeout' => 120,
					),
					'body'    => $args,
				)
			);

			if ( is_wp_error( $response ) ) {
				$this->logger->alert_error( sprintf( 'Telegram %s API Error: %s', $message_type, $response->get_error_message() ) );
				return false;
			}
			$response = wp_remote_retrieve_body( $response );
			$response = json_decode( $response );

			if ( empty( $response->ok ) ) {
				$this->logger->alert_error( sprintf( 'Telegram %s API Error: %s', $message_type, print_r( $response, true ) ) );
				return false;
			}
			return $response->result;
		} catch ( \Exception $e ) {
			$this->logger->alert_error( 'Telegram API Error: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Returns a MIME content type for a certain file.
	 *
	 * @param  string $file_path File path.
	 * @return string            MIME type.
	 */
	private function get_content_type( $file_path ) {
		if ( function_exists( 'mime_content_type' ) ) {
			$result = mime_content_type( $file_path );

			if ( is_string( $result ) ) {
				return $result;
			}
		}

		if ( function_exists( 'finfo_open' ) && function_exists( 'finfo_file' ) ) {
			$finfo  = finfo_open( FILEINFO_MIME_TYPE );
			$result = finfo_file( $finfo, $file_path );

			if ( is_string( $result ) ) {
				return $result;
			}
		}

		$ext = pathinfo( $file_path, PATHINFO_EXTENSION );
		if ( ! empty( $ext ) ) {
			$mime_types = wp_get_mime_types();
			foreach ( $mime_types as $key => $value ) {
				if ( in_array( $ext, explode( '|', $key ), true ) ) {
					return $value;
				}
			}
		}

		return 'application/octet-stream';
	}
}
