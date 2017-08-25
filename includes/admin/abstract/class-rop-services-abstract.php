<?php
abstract class Rop_Services_Abstract {

	public $display_name;

	protected $service_name;

	protected $credentials;

	protected $model;

	protected $is_auth = false;

	public function __construct() {
		$this->model = new Rop_Service_Model( $this->service_name );
		$this->init();
	}

	public abstract function init();

	public abstract function credentials( $args );

	public abstract function get_token();

	/**
	 * @param   array $args Optional arguments needed by the implementation.
	 * @return Rop_User_Model
	 */
	public abstract function get_user( $args );

	public abstract function auth();

	public abstract function share( $post_details );

	public function is_auth() {
		return $this->is_auth;
	}

	protected function register_endpoint( $path, $callback, $method = 'GET' ) {
		$loader = new Rop_Loader();
		$loader->add_action( 'rest_api_init', $this, function( $path, $callback, $method ) {
			register_rest_route( 'tweet-old-post/v8', '/' . $this->service_name . '/' . $path, array(
				'methods' => $method,
				'callback' => array( $this, $callback ),
			) );
		} );
	}

}
