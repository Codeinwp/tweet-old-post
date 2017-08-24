<?php
class Rop_Accounts_Model extends Rop_Model_Abstract {

    protected $users;

    public function __construct() {
        parent::__construct( 'rop_users' );
        $this->users = $this->data;
    }

    public function add( Rop_User_Model $user ) {
        $new_user = $user->to_array();
        return $this->set( $new_user['user_id'] . '_' . $new_user['user_service'] , $new_user  );
    }

    public function delete( Rop_User_Model $user ) {
        unset( $this->users[$user->get_id()] );
        return update_option( $this->namespace, $this->users );
    }

    public function get_by_id( $user_key ) {
        if( isset( $this->users[ $user_key ] ) ) {
            return $this->users[ $user_key ];
        }
        return false;
    }

    public function get_by_service( $service_name ) {
        $results = array();
        foreach ( $this->users as $id => $user_data ) {
            if( $user_data['user_service'] == $service_name ) {
                $results[$id] = $user_data;
            }
        }
        return $results;
    }

}