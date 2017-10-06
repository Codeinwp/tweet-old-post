<?php
/**
 * The class that handles the REST main calls for the  plugin.
 *
 * @link       https://themeisle.com
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/admin
 */

/**
 * Handles the REST main calls for the  plugin.
 *
 * Contains utility methods for the plugin REST API and the API switcher.
 *
 * @package    Rop
 * @subpackage Rop/admin
 * @author     Themeisle <friends@themeisle.com>
 */
class Rop_Rest_Api {

    /**
     * Rop_Rest_Api constructor.
     * Registers the API endpoint.
     *
     * @since   8.0.0
     * @access  public
     */
    public function __construct() {
        add_action( 'rest_api_init', function () {
            register_rest_route( 'tweet-old-post/v8', '/api', array(
                'methods' => array( 'GET', 'POST' ),
                'callback' => array( $this, 'api' ),
            ) );
        } );
    }

    public function api( WP_REST_Request $request ) {
        switch( $request->get_param( 'req' ) ) {
            case 'available_services':
                $response = $this->get_available_services();
                break;
            case 'service_sign_in_url':
                $data = json_decode( $request->get_body(), true );
                $response = $this->get_service_sign_in_url( $data );
                break;
            case 'authenticated_services':
                $response = $this->get_authenticated_services();
                break;
            case 'active_accounts':
                $response = $this->get_active_accounts();
                break;
            case 'update_accounts':
                $data = json_decode( $request->get_body(), true );
                $response = $this->update_active_accounts( $data );
                break;
            case 'remove_account':
                $data = json_decode( $request->get_body(), true );
                $response = $this->remove_account( $data );
                break;
            case 'authenticate_service':
                $data = json_decode( $request->get_body(), true );
                $response = $this->authenticate_service( $data );
                break;
            case 'remove_service':
                $data = json_decode( $request->get_body(), true );
                $response = $this->remove_service( $data );
                break;
            default:
                $response = array( 'status' => '200', 'data' => array( 'list', 'of', 'stuff', 'from', 'api' ) );
        }
        return $response;
    }

    private function get_available_services() {
        $global_settings = new Rop_Global_Settings();
        return $global_settings->get_available_services();
    }

    private function get_authenticated_services() {
        $model = new Rop_Services_Model();
        //$model->reset_authenticated_services();
        return $model->get_authenticated_services();
    }

    private function get_active_accounts() {
        $model = new Rop_Services_Model();
        //$model->reset_authenticated_services();
        return $model->get_active_accounts();
    }

    private function update_active_accounts( $data ) {
        $new_active = array();
        foreach ( $data['to_be_activated'] as $account ) {
            $id = $data['service'] . '_' . $data['service_id'] . '_' . $account['id'];
            $new_active[$id] = array(
                'service' => $data['service'],
                'user' => $account['name'],
                'img' => $account['img'],
                'account' => $account['account'],
                'created' => date('d/m/Y H:i')
            );
        }
        $model = new Rop_Services_Model();
        return $model->add_active_accounts( $new_active );
    }

    private function remove_account( $data ) {
        $model = new Rop_Services_Model();
        return $model->delete_active_accounts( $data['account_id'] );
    }

    private function authenticate_service( $data ) {
        $new_service = array();
        $factory = new Rop_Services_Factory();
        ${$data['service'].'_services'} = $factory->build( $data['service'] );
        $authenticated = ${$data['service'].'_services'}->authenticate();
        if( $authenticated ) {
            $service = ${$data['service'].'_services'}->get_service();
            $service_id = $service['service'] . '_' . $service['id'];
            $new_service[$service_id] = $service;
        }

        $model = new Rop_Services_Model();
        return $model->add_authenticated_service( $new_service );
    }

    private function remove_service( $data ) {
        $model = new Rop_Services_Model();
        return $model->delete_authenticated_service( $data['id'], $data['service'] );
    }

    private function get_service_sign_in_url( $data ) {
        $url = '';
        $factory = new Rop_Services_Factory();
        ${$data['service'].'_services'} = $factory->build( $data['service'] );
        if( ${$data['service'].'_services'} ) {
            $url = ${$data['service'].'_services'}->sign_in_url( $data );
        }
        return json_encode( array( 'url' => $url ) );
    }

}