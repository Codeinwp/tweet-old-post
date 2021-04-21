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
	 * Utility method to clear authenticated services.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function reset_authenticated_services() {
		$this->set( $this->services_namespace, array() );
		$this->reset_active_accounts();
	}

	/**
	 * Utility method to clear active accounts.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function reset_active_accounts() {
		$this->set( $this->accounts_namespace, array() );
	}

	/**
	 * Add a new service to DB.
	 *
	 * @param array $new_service The new service array.
	 *
	 * @return mixed|null
	 * @since   8.0.0
	 * @access  public
	 */
	public function add_authenticated_service( $new_service ) {
		if ( empty( $new_service ) ) {
			return false;
		}

		// FB toast message init.
		// We will disable this for now, but will leave the code because it can be used in the future if required
		// $this->facebook_exception_toast( $new_service );
		return $this->update_authenticated_services( wp_parse_args( $new_service, $this->get_authenticated_services() ) );
	}

	/**
	 * When a new facebook account is added we need to inform the user about their domain
	 * to be verified in the facebook account.
	 * This function will help us create a toast message to do that.
	 *
	 * @param array $new_service The new service array.
	 *
	 * @return bool
	 * @since 8.4.3
	 * @access public
	 */
	public function facebook_exception_toast( $new_service ) {

		foreach ( $new_service as $index_account => $account_info ) {
			if ( isset( $account_info['service'] ) && 'facebook' === $account_info['service'] ) {
				update_option( 'rop_facebook_domain_toast', 'yes' );
			}
		}

		return true;
	}

	/**
	 * If there are no facebook account available
	 * We will update the facebook toast message option to now display
	 *
	 * @param array $accounts_list An array of the connected accounts.
	 *
	 * @return bool
	 * @since 8.4.3
	 * @access public
	 */
	public function facebook_exception_toast_remove( $accounts_list ) {
		$remove_toast_option = false;
		// If there are no services in the list, just mark option for removal.
		if ( empty( $accounts_list ) ) {
			$remove_toast_option = true;
		}

		if ( false === $remove_toast_option ) {
			$fb_valid_accounts = 0;
			foreach ( $accounts_list as $service_key => $service_data ) {
				if ( 'facebook' === $service_data['service'] ) {
					if ( ! empty( $service_data['available_accounts'] ) ) {
						foreach ( $service_data['available_accounts'] as $account_key => $value ) {
							if ( true === filter_var( $value['active'], FILTER_VALIDATE_BOOLEAN ) ) {
								$fb_valid_accounts ++;
							}
						}
					}
				}
			}
			if ( empty( $fb_valid_accounts ) ) {
				$remove_toast_option = true;
			}
		}

		if ( true === $remove_toast_option ) {
			update_option( 'rop_facebook_domain_toast', 'no' );
		}

		return true;
	}

	/**
	 * Method to update authenticated services.
	 *
	 * @param array $new_auth_services The new services array.
	 *
	 * @return boolean
	 * @since   8.0.0
	 * @access  public
	 */
	public function update_authenticated_services( $new_auth_services ) {
		if ( empty( $new_auth_services ) ) {
			return false;
		}

		foreach ( $new_auth_services as $service_key => $service_data ) {
			$accounts = array();
			if ( ! is_array( $service_data['available_accounts'] ) ) {
				$service_data['available_accounts'] = array();
			}
			foreach ( $service_data['available_accounts'] as $account ) {
				$key = $service_key . '_' . $account['id'];
				// Linkedin Exception.
				if ( 'linkedin' === $service_key ) {
					$key = $service_key . '_' . str_replace( '_', '!sp!', $account['id'] );
				}

				$accounts[ $key ] = $account;
			}
			$new_auth_services[ $service_key ]['available_accounts'] = $accounts;
		}

		$this->facebook_exception_toast_remove( $new_auth_services );
		$this->set( $this->services_namespace, $new_auth_services );
		$this->sync_active_accounts();

		return true;
	}

	/**
	 * Sync active accounts after a service change.
	 */
	private function sync_active_accounts() {
		$services = $this->get_authenticated_services();
		foreach ( $services as $service_key => $service_details ) {
			if ( empty( $service_details['available_accounts'] ) ) {
				continue;
			}
			foreach ( $service_details['available_accounts'] as $account ) {

				if ( 'linkedin' === $service_details['service'] ) {
					$service_details_id = str_replace( '_', '!sp!', $service_details['id'] );
					$account_id         = str_replace( '_', '!sp!', $account['id'] );
				} else {
					$service_details_id = $service_details['id'];
					$account_id         = $account['id'];
				}

				$id = $service_details['service'] . '_' . $service_details_id . '_' . $account_id; // Replace the underscore.
				if ( $account['active'] ) {
					$this->add_active_accounts( $id );
				} else {
					$this->delete_active_accounts( $id );
				}
			}
		}
	}

	/**
	 * Method to retrieve the authenticated services from DB.
	 *
	 * @param string $service Service type.
	 *
	 * @return array
	 * @since   8.0.0
	 * @access  public
	 */
	public function get_authenticated_services( $service = '' ) {

		$services = $this->get( $this->services_namespace );

		if ( ! is_array( $services ) ) {
			return array();
		}

		$services = array_filter(
			$services,
			function ( $value ) use ( $service ) {

				if ( ! isset( $value['service'] ) ) {
					return false;
				}
				if ( empty( $service ) ) {
					return true;
				}
				if ( $value['service'] === $service ) {
					return true;
				}

				return false;
			}
		);

		$services = array_map(
			function ( $service ) {
				/**
				 * Normalize available accounts by remove null values.
				 */
				$service['available_accounts'] = array_map(
					function ( $account ) {
						// Added in v8.6.2 to prevent fatal errors due to us dropping buffer
						// TODO: Remove below code in later version of ROP
						if ( $account['service'] === 'buffer' ) {
							unset( $account['service'] );
							return;
						}
						$service = Rop_Services_Factory::build( $account['service'] );
						$account = $service->populate_additional_data( $account );
						return $this->normalize_account( $account );
					},
					$service['available_accounts']
				);

				/**
				 * If there is no available account, clear the service app.
				 */
				if ( empty( $service['available_accounts'] ) ) {
					return array();
				}

				return $service;
			},
			$services
		);

		return $services;
	}

	/**
	 * Normalize account component by removing null values.
	 *
	 * @param array $account Account data.
	 *
	 * @return array Normalized array.
	 */
	private function normalize_account( $account ) {
		return array_map(
			function ( $value ) {
				return is_null( $value ) ? '' : $value;
			},
			$account
		);
	}

	/**
	 * Add a new account to DB.
	 *
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 *
	 * @param array|string $new_active_accounts The new account array.
	 *
	 * @return mixed|null
	 * @since   8.0.0
	 * @access  public
	 */
	public function add_active_accounts( $new_active_accounts ) {

		if ( empty( $new_active_accounts ) ) {
			return false;
		}
		if ( ! is_array( $new_active_accounts ) ) {
			$new_active_accounts = array( $new_active_accounts );
		}
		$account_details = array();
		foreach ( $new_active_accounts as $index ) {
			$account_details[ $index ] = $this->toggle_account_state( $index, true );
		}

		return $this->update_active_accounts( wp_parse_args( $account_details, $this->get_active_accounts() ) );
	}

	/**
	 * Method to updated the state of an account from the services array.
	 *
	 * @param string  $index The active account index.
	 * @param boolean $state The desired state (true/false).
	 *
	 * @return array|mixed
	 * @since   8.0.0
	 * @access  private
	 */
	private function toggle_account_state( $index, $state ) {
		$scheduler = new Rop_Scheduler_Model();
		$queue     = new Rop_Queue_Model();

		$services = $this->get_authenticated_services();
		$return   = array();

		list( $service, $service_id, $account_id ) = $this->handle_underscore_exception( $index );

		$service_key = $service . '_' . $service_id;

		if ( count( $services[ $service_key ]['available_accounts'] ) > 0 ) {
			foreach ( $services[ $service_key ]['available_accounts'] as $key => $account ) {
				if ( $account['id'] == $account_id ) {
					/**
					 * Reset events timeline for this account when switching state.
					 */
					$scheduler->refresh_events( $account_id );
					/**
					 * Clear queue on switching account.
					 */
					$queue->clear_queue( $account_id );
					$services[ $service . '_' . $service_id ]['available_accounts'][ $key ]['active'] = $state;
					$return                                                                           = $services[ $service_key ]['available_accounts'][ $key ];
				}
			}
			$this->set( $this->services_namespace, $services );
		}

		return $return;
	}

	/**
	 * Method to update active accounts.
	 *
	 * @param array $new_active_accounts The new active accounts array.
	 *
	 * @return mixed|null
	 * @since   8.0.0
	 * @access  public
	 */
	public function update_active_accounts( $new_active_accounts ) {
		$this->set( $this->accounts_namespace, $new_active_accounts );

		return $new_active_accounts;
	}

	/**
	 * Method to retrieve the active accounts from DB.
	 *
	 * @return mixed|null
	 * @since   8.0.0
	 * @access  public
	 */
	public function get_active_accounts() {
		$accounts = $this->get( $this->accounts_namespace );
		if ( ! is_array( $accounts ) ) {
			$accounts = array();
		}
		$accounts = array_map(
			function ( $account ) {
				return $this->normalize_account( $account );
			},
			$accounts
		);
		$accounts = array_filter(
			$accounts,
			function ( $account ) {
				return ! empty( $account );
			}
		);

		return wp_parse_args( $accounts, array() );
	}

	/**
	 * Remove an account from DB.
	 *
	 * @param string $index The account index.
	 *
	 * @return mixed|null
	 * @since   8.0.0
	 * @access  public
	 */
	public function delete_active_accounts( $index ) {
		$accounts = $this->get_active_accounts();
		$this->toggle_account_state( $index, false );
		unset( $accounts[ $index ] );
		$this->update_active_accounts( $accounts );

		return $accounts;
	}

	/**
	 * Remove a service from DB.
	 *
	 * @param string $service_id The service ID.
	 * @param string $service The service name.
	 *
	 * @return mixed|null
	 * @since   8.0.0
	 * @access  public
	 */
	public function delete_authenticated_service( $service_id, $service ) {
		$services           = $this->get_authenticated_services();
		$index              = $service . '_' . $service_id;
		$available_accounts = $services[ $index ]['available_accounts'];
		foreach ( $available_accounts as $account ) {
			$this->delete_active_accounts( $index . '_' . $account['id'] );
		}
		unset( $services[ $index ] );
		$this->update_authenticated_services( $services );

		return $services;
	}

	/**
	 * Remove account id from the service.
	 *
	 * @param string $account_id Account id to remove.
	 *
	 * @return bool Update status.
	 */
	public function remove_service_account( $account_id = '' ) {
		if ( empty( $account_id ) ) {
			return false;
		}

		list( $service, $service_id, $account_id_num ) = $this->handle_underscore_exception( $account_id );

		$service_id = $service . '_' . $service_id;

		$services = $this->get_authenticated_services();

		unset( $services[ $service_id ]['available_accounts'][ $account_id ] );

		$this->update_authenticated_services( $services );

		return true;

	}

	/**
	 * Utility method to find a service.
	 */
	public function find_service() {

	}

	/**
	 * Utility method to find an account.
	 *
	 * @param string $account_id The account ID to look for.
	 *
	 * @return bool|array
	 * @since   8.0.0
	 * @access  public
	 */
	public function find_account( $account_id ) {
		$services = $this->get_authenticated_services();

		list( $service, $service_id, $user_id ) = $this->handle_underscore_exception( $account_id );

		if ( count( $services[ $service . '_' . $service_id ]['available_accounts'] ) >= 1 ) {
			foreach ( $services[ $service . '_' . $service_id ]['available_accounts'] as $key => $account ) {
				if ( $account['id'] == $user_id ) {
					$response = array(
						'id'          => $services[ $service . '_' . $service_id ]['available_accounts'][ $key ]['id'],
						'service'     => $services[ $service . '_' . $service_id ]['service'],
						'credentials' => $services[ $service . '_' . $service_id ]['credentials'],
					);

					if ( $service == 'facebook' ) {
						$response['access_token'] = $services[ $service . '_' . $service_id ]['available_accounts'][ $key ]['access_token'];
					}
					$response['user']       = isset( $account['user'] ) ? $account['user'] : '';
					$response['is_company'] = ! isset( $account['is_company'] ) ? false : $account['is_company'];
					$response['account_type'] = ! empty( $account['account_type'] ) ? $account['account_type'] : '';

					return $response;
				}
			}
		}

		return false;
	}

}
