<?php
class Rop_User_Model extends Rop_Model_Abstract {

	private $user_id = '';
	private $user_name = '';
	private $user_picture = '';
	private $user_service = '';
	private $user_credentials = array();

	/**
	 * @var Rop_Services_Abstract
	 */
	private $service = null;

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

	public function get_service() {
		if ( isset( $this->user_service ) && $this->user_service != '' && ! empty( $this->user_credentials ) ) {
			$this->service = new Rop_Services_Factory( $this->user_service );
		}
		return $this->service;
	}

	private function update( $args ) {
		foreach ( $args as $key => $value ) {
			if ( in_array( $key, array( 'user_id', 'user_name', 'user_picture', 'user_service', 'user_credentials' ) ) ) {
				$this->$key = $value;
			}
		}
	}

	public function get_id() {
		return $this->user_id . '_' . $this->user_service;
	}

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
