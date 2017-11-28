<?php
/**
 * The file that defines the model for services.
 *
 * A class that is used as a model for building services.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/models
 */

/**
 * Class Rop_Services_Model
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
class Rop_Services_Model extends Rop_Model_Abstract {

	/**
	 * Holds the services namespace.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     string $services_namespace Defaults 'services'.
	 */
	private $services_namespace = 'services';

	/**
	 * Holds the active accounts namespace.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     string $accounts_namespace Defaults 'active_accounts'.
	 */
	private $accounts_namespace = 'active_accounts';

	/**
	 * Has the results of the last run query.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     mixed|null $last_services_query The last services query results.
	 */
	private $last_services_query = null;

	/**
	 * Has the results of the last run query.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     mixed|null $last_accounts_query The last active accounts query results.
	 */
	private $last_accounts_query = null;

	/**
	 * Method to retrieve the authenticated services from DB.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   bool $force Flag to specify if last query should be ignored. Get fresh results.
	 * @return mixed|null
	 */
	public function get_authenticated_services( $force = false ) {
		if ( $this->last_services_query == null || $force == true ) {
			$default = array();
			$services = $this->get( $this->services_namespace );
			$this->last_services_query = wp_parse_args( $services, $default );
		}
		return $this->last_services_query;
	}

	/**
	 * Method to update authenticated services.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   array $new_authenticated_services The new services array.
	 * @return mixed|null
	 */
	public function update_authenticated_services( $new_authenticated_services ) {
		$this->last_services_query = wp_parse_args( $new_authenticated_services, $this->last_services_query );
		$this->set( $this->services_namespace, $this->last_services_query );
		return $this->last_services_query;
	}

	/**
	 * Utility method to clear authenticated services.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function reset_authenticated_services() {
		$this->set( $this->services_namespace, array() );
		$this->last_services_query = null;
	}

	/**
	 * Add a new service to DB.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   array $new_service The new service array.
	 * @return mixed|null
	 */
	public function add_authenticated_service( $new_service ) {
		return $this->update_authenticated_services( wp_parse_args( $new_service, $this->get_authenticated_services() ) );
	}

	/**
	 * Remove a service from DB.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $id The service ID.
	 * @param   string $service The service name.
	 * @return mixed|null
	 */
	public function delete_authenticated_service( $id, $service ) {
		$this->last_services_query = $this->get_authenticated_services();
		$index = $service . '_' . $id;
		unset( $this->last_services_query[ $index ] );
		$this->set( $this->services_namespace, $this->last_services_query );
		return $this->last_services_query;
	}

	/**
	 * Method to retrieve the active accounts from DB.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   bool $force Flag to specify if last query should be ignored. Get fresh results.
	 * @return mixed|null
	 */
	public function get_active_accounts( $force = false ) {
		if ( $this->last_accounts_query == null || $force == true ) {
			$default = array();
			$accounts = $this->get( $this->accounts_namespace );
			$this->last_accounts_query = wp_parse_args( $accounts, $default );
		}
		return $this->last_accounts_query;
	}

	/**
	 * Method to update active accounts.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   array $new_active_accounts The new active accounts array.
	 * @return mixed|null
	 */
	public function update_active_accounts( $new_active_accounts ) {
		$this->last_accounts_query = wp_parse_args( $new_active_accounts, $this->last_accounts_query );
		$this->set( $this->accounts_namespace, $this->last_accounts_query );
		return $this->last_accounts_query;
	}

	/**
	 * Utility method to clear active accounts.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function reset_active_accounts() {
		$this->set( $this->accounts_namespace, array() );
		$this->last_accounts_query = null;
	}

	/**
	 * Add a new account to DB.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   array $new_active_account The new account array.
	 * @return mixed|null
	 */
	public function add_active_accounts( $new_active_account ) {
		foreach ( $new_active_account as $index => $data ) {
			$this->toggle_account_state( $index, true );
		}
		return $this->update_active_accounts( wp_parse_args( $new_active_account, $this->get_active_accounts() ) );
	}

	/**
	 * Remove an account from DB.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $index The account index.
	 * @return mixed|null
	 */
	public function delete_active_accounts( $index ) {
		$this->last_accounts_query = $this->get_active_accounts();
		$this->toggle_account_state( $index, false );

		unset( $this->last_accounts_query[ $index ] );
		$this->set( $this->accounts_namespace, $this->last_accounts_query );
		return $this->last_accounts_query;
	}

	/**
	 * Method to updated the state of an account from the services array.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   string  $index The active account index.
	 * @param   boolean $state The desired state (true/false).
	 */
	private function toggle_account_state( $index, $state ) {
		$this->last_services_query = $this->get_authenticated_services();
		list( $service, $service_id, $account_id ) = explode( '_', $index );
		if ( count( $this->last_services_query[ $service . '_' . $service_id ]['available_accounts'] ) > 1 ) {
			foreach ( $this->last_services_query[ $service . '_' . $service_id ]['available_accounts'] as $key => $account ) {
				if ( $account['id'] == $account_id ) {
					$this->last_services_query[ $service . '_' . $service_id ]['available_accounts'][ $key ]['active'] = $state;
				}
			}
			$this->set( $this->services_namespace, $this->last_services_query );
		}
	}

	/**
	 * Utility method to find a service.
	 */
	public function find_service() {

	}

	/**
	 * Utility method to find an account.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $account_id The account ID to look for.
	 * @return bool|array
	 */
	public function find_account( $account_id ) {
		$this->last_services_query = $this->get_authenticated_services();
		list( $service, $service_id, $id ) = explode( '_', $account_id );
		if ( count( $this->last_services_query[ $service . '_' . $service_id ]['available_accounts'] ) >= 1 ) {
			foreach ( $this->last_services_query[ $service . '_' . $service_id ]['available_accounts'] as $key => $account ) {
				if ( $account['id'] == $id ) {
					return array(
						'id' => $this->last_services_query[ $service . '_' . $service_id ]['available_accounts'][ $key ]['id'],
						'service' => $this->last_services_query[ $service . '_' . $service_id ]['service'],
						'credentials' => $this->last_services_query[ $service . '_' . $service_id ]['credentials'],
					);
				}
			}
		}
		return false;
	}

}
