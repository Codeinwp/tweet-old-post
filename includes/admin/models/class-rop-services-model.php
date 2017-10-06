<?php
class Rop_Services_Model extends Rop_Model_Abstract {

    private $services_namespace = 'services';
    private $accounts_namespace = 'active_accounts';

    private $last_services_query = null;
    private $last_accounts_query = null;

    public function get_authenticated_services( $force = false ) {
        if( $this->last_services_query == null || $force == true ) {
            $default = array();
            $services = $this->get( $this->services_namespace );
            $this->last_services_query = wp_parse_args( $services, $default );
        }
        return $this->last_services_query;
    }

    public function update_authenticated_services( $new_authenticated_services ) {
        $this->last_services_query = wp_parse_args( $new_authenticated_services, $this->last_services_query );
        $this->set( $this->services_namespace, $this->last_services_query );
        return $this->last_services_query;
    }

    public function reset_authenticated_services() {
        $this->set( $this->services_namespace, array() );
        $this->last_services_query = null;
    }

    public function add_authenticated_service( $new_service ) {
        return $this->update_authenticated_services( wp_parse_args( $new_service, $this->get_authenticated_services() ) );
    }

    public function delete_authenticated_service( $id, $service ) {
        $this->last_services_query = $this->get_authenticated_services();
        $index = $service . '_' . $id;
        unset( $this->last_services_query[$index] );
        $this->set( $this->services_namespace, $this->last_services_query );
        return $this->last_services_query;
    }

    public function get_active_accounts( $force = false ) {
        if( $this->last_accounts_query == null || $force == true ) {
            $default = array();
            $accounts = $this->get( $this->accounts_namespace );
            $this->last_accounts_query = wp_parse_args( $accounts, $default );
        }
        return $this->last_accounts_query;
    }

    public function update_active_accounts( $new_active_accounts ) {
        $this->last_accounts_query = wp_parse_args( $new_active_accounts, $this->last_accounts_query );
        $this->set( $this->accounts_namespace, $this->last_accounts_query );
        return $this->last_accounts_query;
    }

    public function reset_active_accounts() {
        $this->set( $this->accounts_namespace, array() );
        $this->last_accounts_query = null;
    }

    public function add_active_accounts( $new_active_account ) {
        foreach ( $new_active_account as $index => $data ) {
            $this->toggle_account_state( $index, true );
        }
        return $this->update_active_accounts( wp_parse_args( $new_active_account, $this->get_active_accounts() ) );
    }

    public function delete_active_accounts( $index ) {
        $this->last_accounts_query = $this->get_active_accounts();
        $this->toggle_account_state( $index, false );

        unset( $this->last_accounts_query[$index] );
        $this->set( $this->accounts_namespace, $this->last_accounts_query );
        return $this->last_accounts_query;
    }

    private function toggle_account_state( $index, $state ) {
        $this->last_services_query = $this->get_authenticated_services();
        list( $service, $service_id, $account_id ) = explode( '_', $index );
        if( count( $this->last_services_query[$service . '_' . $service_id]['available_accounts'] ) > 1 ) {
            foreach ( $this->last_services_query[$service . '_' . $service_id]['available_accounts'] as $key => $account ) {
                if( $account['id'] == $account_id ) {
                    $this->last_services_query[$service . '_' . $service_id]['available_accounts'][$key]['active'] = $state;
                }
            }
            $this->set( $this->services_namespace, $this->last_services_query );
        }
    }

    public function find_service() {

    }

    public function find_account() {

    }

}