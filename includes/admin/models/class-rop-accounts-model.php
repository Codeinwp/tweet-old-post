<?php
/**
 * The file that defines the model for accounts.
 *
 * A class that is used as a model for accounts.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/models
 */

/**
 * Class Rop_Accounts_Model
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
class Rop_Accounts_Model extends Rop_Model_Abstract {

	/**
	 * The users array.
	 * Retrieved from DB.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     array $users Stores the users.
	 */
	protected $users;

	/**
	 * Rop_Accounts_Model constructor.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function __construct() {
		parent::__construct( 'rop_users' );
		$this->users = $this->data;
	}

	/**
	 * Method to add a new account.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   Rop_User_Model $user The user to add.
	 * @return mixed
	 */
	public function add( Rop_User_Model $user ) {
		$new_user = $user->to_array();
		return $this->set( $new_user['user_id'] . '_' . $new_user['user_service'] , $new_user );
	}

	/**
	 * Method to remove an account.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   Rop_User_Model $user The user to remove.
	 * @return mixed
	 */
	public function delete( Rop_User_Model $user ) {
		unset( $this->users[ $user->get_id() ] );
		return update_option( $this->namespace, $this->users );
	}

	/**
	 * Method to retrieve an user by key.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $user_key The user key.
	 * @return bool|Rop_User_Model
	 */
	public function get_by_id( $user_key ) {
		if ( isset( $this->users[ $user_key ] ) ) {
			return new Rop_User_Model( $this->users[ $user_key ], false );
		}
		return false;
	}

	/**
	 * Method to retrive an array of Rop_User_Model by service name.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $service_name The service name.
	 * @return array
	 */
	public function get_by_service( $service_name ) {
		$results = array();
		foreach ( $this->users as $id => $user_data ) {
			if ( $user_data['user_service'] == $service_name ) {
				$results[] = new Rop_User_Model( $user_data, false );
			}
		}
		return $results;
	}

}
