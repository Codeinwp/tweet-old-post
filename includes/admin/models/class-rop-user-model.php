<?php
/**
 * The file that defines the model for users.
 *
 * A class that is used as a model for users.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/models
 */

/**
 * Class Rop_User_Model
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
class Rop_User_Model extends Rop_Model_Abstract {

	/**
	 * Stores the user ID.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     mixed $user_id The user ID.
	 */
	private $user_id = '';

	/**
	 * Stores the user name.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     string $user_name The user name.
	 */
	private $user_name = '';

	/**
	 * Stores the user picture.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     string $user_picture The user picture path.
	 */
	private $user_picture = '';

	/**
	 * Stores the user service.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     string $user_service The user service.
	 */
	private $user_service = '';

	/**
	 * Stores the user service credentials.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     string $user_credentials The user service credentials.
	 */
	private $user_credentials = array();

	/**
	 * Stores an instance of the used service.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     Rop_Services_Abstract $service An instance of the service used.
	 */
	private $service = null;

	/**
	 * Rop_User_Model constructor.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   array $args Optional. Arguments to be passed to the constructor.
	 *                      For defining a user object.
	 * @param   bool  $new Optional. Flag to specify if the user is new or not.
	 */
	public function __construct( $args = array(), $new = true ) {
		parent::__construct( 'rop_users' );

		if ( ! $new ) {
			$users_data = $this->get( $args['user_id'] );

			if ( $users_data != null ) {
				$this->user_id = $users_data['user_id'];
				$this->user_name = $users_data['user_name'];
				$this->user_picture = $users_data['user_picture'];
				$this->user_service = $users_data['user_service'];
				$this->user_credentials = $users_data['user_credentials'];
			}
		}

		$this->update( $args );
	}

	/**
	 * Method to retrieve a service instance.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return Rop_Services_Abstract|Rop_Services_Factory
	 */
	public function get_service() {
		if ( isset( $this->user_service ) && $this->user_service != '' && ! empty( $this->user_credentials ) ) {
			$this->service = new Rop_Services_Factory( $this->user_service );
		}
		return $this->service;
	}

	/**
	 * Method to updated the model vars.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   array $args The list of arguments to try and update.
	 */
	private function update( $args ) {
		foreach ( $args as $key => $value ) {
			if ( in_array( $key, array( 'user_id', 'user_name', 'user_picture', 'user_service', 'user_credentials' ) ) ) {
				$this->$key = $value;
			}
		}
	}

	/**
	 * Utility method to get a formatted ID for DB storing.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return string
	 */
	public function get_id() {
		return $this->user_id . '_' . $this->user_service;
	}

	/**
	 * Utility method to convert object data to array.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return array
	 */
	public function to_array() {
		return array(
			'user_id' => $this->user_id,
			'user_name' => $this->user_name,
			'user_picture' => $this->user_picture,
			'user_service' => $this->user_service,
			'user_credentials' => $this->user_credentials,
		);
	}

}
