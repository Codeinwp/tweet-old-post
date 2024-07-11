<?php
/**
 * ROP Test Accounts actions for PHPUnit.
 *
 * @package     ROP
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */

require_once dirname( __FILE__ ) . '/helpers/class-setup-accounts.php';

/**
 * Test accounts related logic.
 */
class Test_RopAccounts extends WP_UnitTestCase {
	public static function setUpBeforeClass() {
		Rop_InitAccounts::init();
	}

	/**
	 * Testing services avilability.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function test_services_model() {

		$services_model = new Rop_Services_Model();
		$this->assertNotEmpty( $services_model->get_active_accounts(), 'By default the account should be active.' );
		$this->assertNonEmptyMultidimensionalArray( $services_model->get_active_accounts() );

		$this->assertNotEmpty( $services_model->get_authenticated_services(), 'By default the services should be active.' );
		$this->assertNonEmptyMultidimensionalArray( $services_model->get_authenticated_services() );

		$accounts     = $services_model->get_active_accounts();
		$account_test = reset( $accounts );
		$account_key  = key( $accounts );
		$this->assertArrayHasKey( 'active', $account_test, 'Active key is not present' );
		$this->assertTrue( $account_test['active'], 'Active key is not set active into an active account.' );
		$this->assertArrayHasKey( 'id', $account_test, 'Id key is not set active into an active account.' );
		$this->assertNotEmpty( $account_test['id'], 'Id key is empty.' );

		$services_model->delete_active_accounts( $account_key );
		$this->assertEmpty( $services_model->get_active_accounts(), 'Delete active account not working.' );

		$services_model->add_active_accounts( $account_key );
		$this->assertEquals( 1, count( $services_model->get_active_accounts() ), 'Add active account not working.' );

	}

	/**
	 * Testing services
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @covers Rop_Services_Factory
	 * @covers Rop_Facebook_Service
	 * @covers Rop_Twitter_Service
	 * @covers Rop_Linkedin_Service
	 * @covers Rop_Tumblr_Service
	 * @covers Rop_Services_Abstract
	 */
	public function test_services_sign_in() {

		if ( version_compare( PHP_VERSION, '5.6.0' ) >= 0 ) {
			$service_factory = new Rop_Services_Factory();

			$global         = new Rop_Global_Settings();
			$services       = $global->get_available_services();
			$build_services = array();
			foreach ( $services as $key => $service ) {
				if ( ! $service['active'] ) {
					continue;
				}
				try {
					$build_services[ $key ] = $service_factory->build( $key );
				} catch ( Exception $exception ) {

				}
			}
			foreach ( $build_services as $service ) {
				$this->assertInstanceOf( 'Rop_Services_Abstract', $service );
				$service->get_api( Rop_InitAccounts::$baseApiClasses[ $service->display_name ]['credentials'][0], Rop_InitAccounts::$baseApiClasses[ $service->display_name ]['credentials'][1] );
				$service->expose_endpoints();
				$service->get_endpoint_url( 'authorize' );
				$service->set_api(Rop_InitAccounts::$baseApiClasses[ $service->display_name ]['credentials'][0], Rop_InitAccounts::$baseApiClasses[ $service->display_name ]['credentials'][1] );
				$api = $service->get_api();
				$this->assertInstanceOf( Rop_InitAccounts::$baseApiClasses[ $service->display_name ]['class'], $api );
				$data['credentials'] = array(
					Rop_InitAccounts::$baseApiClasses[ $service->display_name ]['credentials_name'][0] => Rop_InitAccounts::$baseApiClasses[ $service->display_name ]['credentials'][0],
					Rop_InitAccounts::$baseApiClasses[ $service->display_name ]['credentials_name'][1] => Rop_InitAccounts::$baseApiClasses[ $service->display_name ]['credentials'][1],
				);
				$service->set_credentials( $data['credentials'] );
				$singin_url = @$service->sign_in_url( $data );
				$this->assertNotFalse( filter_var( $singin_url, FILTER_VALIDATE_URL ) );
			}
		}

	}

	/**
	 * Test adding a webhook account.
	 * 
	 * @since 9.1.0
	 * 
	 * @covers Rop_Webhook_Service
	 */
	public function test_add_account_webhook() {
		$rest_controller = new Rop_Rest_Api();

		$webhook_data = array(
			'url'          => 'https://test.add.com',
			'display_name' => 'Add Webhook',
			'headers'      => array(
				'Content-Type: application/json',
			),
		);

		$request = new WP_REST_Request( 'POST', '/example' );
        $request->set_param( 'req', 'add_account_webhook' );
        $request->set_body( json_encode( $webhook_data ) );

		$response = $rest_controller->api( $request );

		$this->assertEquals( '200', $response['code'] );

		$model = new Rop_Services_Model();

		$authenticated_services = $model->get_authenticated_services();

		$account_added = false;
		foreach ( $authenticated_services as $service_id => $service ) {
			if ( isset( $service['credentials']['url'] ) && $service['credentials']['url'] === $webhook_data['url'] ) {
				$account_added = true;
				$this->assertEquals( $service['credentials']['display_name'], $webhook_data['display_name'] );
				$this->assertEquals( $service['credentials']['headers'], $webhook_data['headers'] );
			}
		}

		$this->assertTrue( $account_added );

		$saved_data = get_option( 'rop_data', array() );
		
		$webhooks_services = $saved_data[Rop_Services_Model::WEBHOOK_NAMESPACE];
		$this->assertNotEmpty( $webhooks_services );
		foreach ( $webhooks_services as $webhook_service_id => $webhook_service ) {
			$this->assertEquals( $webhook_service['credentials']['url'], $webhook_data['url'] );
			$this->assertEquals( $webhook_service['credentials']['display_name'], $webhook_data['display_name'] );
			$this->assertEquals( $webhook_service['credentials']['headers'], $webhook_data['headers'] );
		}

		// No webhooks saved in the main services array. Backwards compatibility!
		$services = $saved_data['services'];
		foreach ( $services as $service_key => $service ) {
			$this->assertNotEquals( Rop_Webhook_Service::SERVICE_SLUG, $service['service'] );
		}
	}

	/**
	 * Test editing a webhook account.
	 * 
	 * @since 9.1.0
	 * 
	 * @covers Rop_Webhook_Service
	 */
	public function test_edit_account_webhook() {
		$rest_controller = new Rop_Rest_Api();

		$webhook_data = array(
			'url'          => 'https://test.edit.com',
			'display_name' => 'Edit Webhook',
			'headers'      => array(
				'Content-Type: application/json',
			),
		);

		$request = new WP_REST_Request( 'POST', '/example' );
		$request->set_param( 'req', 'add_account_webhook' );
		$request->set_body( json_encode( $webhook_data ) );

		$response = $rest_controller->api( $request );

		$this->assertEquals( '200', $response['code'] );

		$model = new Rop_Services_Model();

		$authenticated_services = $model->get_authenticated_services();

		$account_added          = false;
		$service_id_added       = null;
		$service_added          = array();
		$full_id_active_account = '';

		foreach ( $authenticated_services as $service_id => $service ) {
			if ( isset( $service['credentials']['url'] ) && $service['credentials']['url'] === $webhook_data['url'] ) {
				$account_added = true;
				$this->assertEquals( $service['credentials']['display_name'], $webhook_data['display_name'] );
				$this->assertEquals( $service['credentials']['headers'], $webhook_data['headers'] );
				
				$service_added    = $service;
				$service_id_added = $service_id;

				foreach ( $service['available_accounts'] as $full_id => $account ) {
					$full_id_active_account = $full_id;
					break;
				}

				break;
			}
		}

		$this->assertTrue( $account_added );

		$webhook_data['display_name'] = 'Test Webhook 2';
		$webhook_data['headers']      = array(
			'Content-Type: application/json',
			'Authorization: Bearer test',
		);

		$webhook_data['id']         = $service_added['id'];
		$webhook_data['active']     = true;
		$webhook_data['full_id']    = $full_id_active_account;
		$webhook_data['service_id'] = $service_id_added;

		$request = new WP_REST_Request( 'POST', '/example' );
		$request->set_param( 'req', 'edit_account_webhook' );
		$request->set_body( json_encode( $webhook_data ) );

		$response = $rest_controller->api( $request );

		$this->assertEquals( '200', $response['code'] );

		$model                  = new Rop_Services_Model();
		$authenticated_services = $model->get_authenticated_services();

		$account_edited = false;
		foreach ( $authenticated_services as $service_id => $service ) {
			if ( isset( $service['credentials']['url'] ) && $service['credentials']['url'] === $webhook_data['url'] ) {
				$account_edited = true;
				$this->assertEquals( $service['credentials']['display_name'], $webhook_data['display_name'] );
				$this->assertEquals( $service['credentials']['headers'], $webhook_data['headers'] );
				$this->assertTrue( $service['available_accounts'][$full_id_active_account]['active'] );
			}
		}

		$this->assertTrue( $account_edited );
	}
}
