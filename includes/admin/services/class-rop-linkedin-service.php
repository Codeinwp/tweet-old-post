<?php
/**
 * The file that defines the Linkedin Service specifics.
 *
 * A class that is used to interact with Linkedin.
 * It extends the Rop_Services_Abstract class.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/services
 */

/**
 * Class Rop_Linkedin_Service
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
class Rop_Linkedin_Service extends Rop_Services_Abstract {

	/**
	 * An instance of authenticated LinkedIn user.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $user An instance of the current user.
	 */
	public $user;
	/**
	 * Defines the service name in slug format.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     string $service_name The service name.
	 */
	protected $service_name = 'linkedin';
	/**
	 * Permissions required by the app.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     array $scopes The scopes to authorize with LinkedIn.
	 */
	protected $scopes = array( 'r_liteprofile', 'r_emailaddress', 'w_member_social');
	// protected $scopes = array( 'r_basicprofile', 'r_emailaddress', 'rw_company_admin', 'w_share' );


	/**
	 * Method to inject functionality into constructor.
	 * Defines the defaults and settings for this service.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function init() {
		$this->display_name = 'LinkedIn';
	}

	/**
	 * Method to expose desired endpoints.
	 * This should be invoked by the Factory class
	 * to register all endpoints at once.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function expose_endpoints() {
		$this->register_endpoint( 'authorize', 'authorize' );
		$this->register_endpoint( 'authenticate', 'maybe_authenticate' );
	}

	/**
	 * Method for authorizing the service.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function authorize() {
		header( 'Content-Type: text/html' );
		if ( ! session_id() ) {
			session_start();
		}

		if ( ! $this->is_set_not_empty(
			$_SESSION,
			array(
				'rop_linkedin_credentials',
			)
		) ) {
			return false;
		}

		$credentials = $_SESSION['rop_linkedin_credentials'];

		$api         = $this->get_api( $credentials['client_id'], $credentials['secret'] );
		$accessToken = $api->getAccessToken( $_GET['code'] );

		$_SESSION['rop_linkedin_token'] = $accessToken->getToken();

		parent::authorize();
		// echo '<script>window.setTimeout("window.close()", 500);</script>';
	}

	/**
	 * Method to retrieve the api object.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $client_id The Client ID. Default empty.
	 * @param   string $client_secret The Client Secret. Default empty.
	 *
	 * @return \LinkedIn\Client Client Linkedin.
	 */
	public function get_api( $client_id = '', $client_secret = '' ) {
		if ( $this->api == null ) {
			$this->set_api( $client_id, $client_secret );
		}

		return $this->api;
	}

	/**
	 * Method to define the api.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $client_id The Client ID. Default empty.
	 * @param   string $client_secret The Client Secret. Default empty.
	 *
	 * @return mixed
	 */
	public function set_api( $client_id = '', $client_secret = '' ) {
		if ( ! class_exists( '\LinkedIn\Client' ) ) {
			return false;
		}
		$this->api = new \LinkedIn\Client( $this->strip_whitespace( $client_id ), $this->strip_whitespace( $client_secret ) );

		// $this->api->setApiHeaders([
		// 'Content-Type' => 'application/json',
		// 'x-li-format' => 'json',
		// 'X-Restli-Protocol-Version' => '2.0.0', // use protocol v2
		// ]);
		$this->api->setApiRoot( 'https://api.linkedin.com/v2/' );

		$this->api->setRedirectUrl( $this->get_legacy_url( 'linkedin' ) );
	}

	/**
	 * Method for maybe authenticate the service.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function maybe_authenticate() {
		if ( ! session_id() ) {
			session_start();
		}
		if ( ! $this->is_set_not_empty(
			$_SESSION,
			array(
				'rop_linkedin_credentials',
				'rop_linkedin_token',
			)
		) ) {
			return false;
		}
		if ( ! $this->is_set_not_empty(
			$_SESSION['rop_linkedin_credentials'],
			array(
				'client_id',
				'secret',
			)
		) ) {
			return false;
		}

		$credentials          = $_SESSION['rop_linkedin_credentials'];
		$token                = $_SESSION['rop_linkedin_token'];
		$credentials['token'] = $token;

		unset( $_SESSION['rop_linkedin_credentials'] );
		unset( $_SESSION['rop_linkedin_token'] );

		return $this->authenticate( $credentials );
	}

	/**
	 * Method for authenticate the service.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function authenticate( $args ) {
		if ( ! $this->is_set_not_empty(
			$args,
			array(
				'client_id',
				'token',
				'secret',
			)
		) ) {
			return false;
		}

		$token = $args['token'];

		$api = $this->get_api( $args['client_id'], $args['secret'] );

		$this->credentials['token']     = $token;
		$this->credentials['client_id'] = $args['client_id'];
		$this->credentials['secret']    = $args['secret'];

		$api->setAccessToken( new LinkedIn\AccessToken( $args['token'] ) );
		try {
			$profile = $api->api(
				// 'me',
				'me?projection=(id,firstName,lastName,profilePicture(displayImage~:playableStreams))',
				array(),
				'GET'
			);
		} catch ( Exception $e ) {
			$this->logger->alert_error( 'Can not get linkedin user details. Error ' . $e->getMessage() );
		}
		if ( ! isset( $profile['id'] ) ) {
			return false;
		}
		$this->service = array(
			'id'                 => $profile['id'],
			'service'            => $this->service_name,
			'credentials'        => $this->credentials,
			'public_credentials' => array(
				'client_id' => array(
					'name'    => 'Client ID',
					'value'   => $this->credentials['client_id'],
					'private' => false,
				),
				'secret'    => array(
					'name'    => 'Client Secret',
					'value'   => $this->credentials['secret'],
					'private' => true,
				),
			),
			'available_accounts' => $this->get_users( $profile ),
		);

		return true;

	}

	/**
	 * Utility method to retrieve users from the Twitter account.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   object $data Response data from Twitter.
	 *
	 * @return array
	 */
	private function get_users( $data = null ) {
		if ( empty( $data ) ) {
			return array();
		}

		$img = '';
		if ( isset( $data['pictureUrl'] ) && $data['pictureUrl'] ) {
			$img = $data['pictureUrl'];
		}

		$img = $data['profilePicture(displayImage~:playableStreams)'];
		$user_details            = $this->user_default;
		// $name = $data['firstName']['localized']['en_US'] . $data['lastName']['localized']['en_US'];
		// $name = json_encode($data['firstName'] ). ' ' . json_encode($data['lastName']) . ' ' . json_encode($img);
		// $fname_array = $data['firstName']['localized'];
		$fname_array = array_values( $data['firstName']['localized'] );
		$firstname = $fname_array[0];

		$lname_array = array_values( $data['lastName']['localized'] );
		$lastname = $lname_array[0];

		// $localized = $fname_array['localized'];
		// $lname_array = $data['lastName']['localized'];
		// $firstnamekey = array_values($fname_array);
		// $firstname = $firstnamekey[0];
		$user_details['id']      = $this->strip_underscore( $data['id'] );
		$user_details['account'] = $this->normalize_string( $data['id'] );
		// $user_details['user']    = $this->normalize_string( $name );
		$user_details['user']    = $firstname . ' ' . $lastname;
		// $user_details['user']    = $this->normalize_string( $data['formattedName'] );
		$user_details['img']     = $img;

		$users = array( $user_details );
		try {
			$companies = $this->api->api(
				'companies?format=json&is-company-admin=true',
				array(),
				'GET'
			);
		} catch ( Exception $e ) {
			return $users;
		}
		if ( empty( $companies ) ) {
			return $users;
		}
		if ( empty( $companies['values'] ) ) {
			return $users;
		}
		foreach ( $companies['values'] as $company ) {
			$users[] = wp_parse_args(
				array(
					'id'         => $this->strip_underscore( $company['id'] ),
					'account'    => $company['name'],
					'is_company' => true,
					'user'       => $company['name'],
				),
				$this->user_default
			);
		}

		return $users;
	}


	/**
	 * Method to register credentials for the service.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   array $args The credentials array.
	 */
	public function set_credentials( $args ) {
		$this->credentials = $args;
	}

	/**
	 * Method to request a token from api.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @return mixed
	 */
	public function request_api_token() {
		if ( ! session_id() ) {
			session_start();
		}

		$api           = $this->get_api();
		$request_token = $api->oauth( 'oauth/request_token', array( 'oauth_callback' => $this->get_legacy_url( 'linkedin' ) ) );

		$_SESSION['rop_twitter_request_token'] = $request_token;

		return $request_token;
	}

	/**
	 * Returns information for the current service.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function get_service() {
		return $this->service;
	}

	/**
	 * Generate the sign in URL.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   array $data The data from the user.
	 *
	 * @return mixed
	 */
	public function sign_in_url( $data ) {
		$credentials = $data['credentials'];
		// @codeCoverageIgnoreStart
		if ( ! session_id() ) {
			session_start();
		}
		// @codeCoverageIgnoreEnd
		$_SESSION['rop_linkedin_credentials'] = $credentials;
		$this->set_api( $credentials['client_id'], $credentials['secret'] );
		$api = $this->get_api();
		$url = $api->getLoginUrl( $this->scopes );

		return $url;
	}

	/**
	 * Method for publishing with Linkedin service.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   array $post_details The post details to be published by the service.
	 * @param   array $args Optional arguments needed by the method.
	 *
	 * @return mixed
	 */
	public function share( $post_details, $args = array() ) {
		if ( Rop_Admin::rop_site_is_staging() ) {
			return false;
		}

		$this->set_api( $this->credentials['client_id'], $this->credentials['secret'] );
		$api   = $this->get_api();
		$token = new \LinkedIn\AccessToken( $this->credentials['token'] );
		$api->setAccessToken( $token );

//add conditionals for type of post

		$new_post = $this->linkedin_article_post( $post_details, $args );
		$new_post = $this->linkedin_image_post( $post_details, $args, $token, $api );

		try {
			if ( isset( $args['is_company'] ) && $args['is_company'] === true ) {
				$api->post( sprintf( 'companies/%s/shares?format=json', $this->unstrip_underscore( $args['id'] ) ), $new_post );
			} else {
				$api->post( 'ugcPosts', $new_post );
			}
			$this->logger->alert_success(
				sprintf(
					'Successfully shared %s to %s on %s ',
					html_entity_decode( get_the_title( $post_details['post_id'] ) ),
					$args['user'],
					$post_details['service']
				)
			);
		} catch ( Exception $exception ) {
			$this->logger->alert_error( 'Can not share to linkedin. Error:  ' . $exception->getMessage() );
			$this->rop_get_error_docs( $exception->getMessage() );

			return false;
		}

		return true;
	}


	/**
	 * Linkedin article post.
	 *
	 * @since   8.2.3
	 * @access  private
	 *
	 * @param   array $post_details The post details to be published by the service.
	 * @param   array $args Arguments needed by the method.
	 *
	 * @return array
	 */
	private function linkedin_article_post( $post_details, $args ) {

		$new_post = array (
			'author' => 'urn:li:person:' . $args['id'],
			'lifecycleState' => 'PUBLISHED',
			'specificContent' =>
			array (
				'com.linkedin.ugc.ShareContent' =>
				array (
					'shareCommentary' =>
					array (
						'text' => $this->strip_excess_blank_lines( $post_details['content'] ) . $post_details['hashtags'],
					),
					'shareMediaCategory' => 'ARTICLE',
					'media' =>
					array (
						0 =>
						array (
							'status' => 'READY',
							'description' =>
							array (
								'text' => $this->strip_excess_blank_lines( $post_details['content'] ),
							),
							'originalUrl' => trim( $this->get_url( $post_details ) ),
							'title' =>
							array (
								'text' => html_entity_decode( get_the_title( $post_details['post_id'] ) ),
							),
						),
					),
				),
			),
			'visibility' =>
			array (
				'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
			),
		);

		return $new_post;

	}

	/**
	 * Linkedin image post format.
	 *
	 * @since   8.2.3
	 * @access  private
	 *
	 * @param   array  $post_details The post details to be published by the service.
	 * @param   array  $args Arguments needed by the method.
	 * @param   string $token The user token.
	 *
	 * @return array
	 */
	private function linkedin_image_post( $post_details, $args, $token, $api ) {

		$register_image = array (
			'registerUploadRequest' =>
			array (
				'recipes' =>
				array (
					0 => 'urn:li:digitalmediaRecipe:feedshare-image',
				),
				'owner' => 'urn:li:person:' . $args['id'],
				'serviceRelationships' =>
				array (
					0 =>
					array (
						'relationshipType' => 'OWNER',
						'identifier' => 'urn:li:userGeneratedContent',
					),
				),
			),
		);

		$response = $api->post( 'https://api.linkedin.com/v2/assets?action=registerUpload', $register_image );
		$upload_url = $response['value']['uploadMechanism']['com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest']['uploadUrl'];
		$asset = $response['value']['asset'];

		$img = $post_details['post_image'];

		if( ! class_exists('\GuzzleHttp\Client') ){
			$this->logger->alert_error( 'Error: Cannot find Guzzle' );
			return;
		}
		$guzzle = new \GuzzleHttp\Client();
		$guzzle->request(
			'PUT',
			$upload_url,
			[
				'headers' => [
					'Authorization' => 'Bearer ' . $token,
				],
				'body' => fopen( $img, 'r' ),

			]
		);

		  $new_post = array (
			  'author' => 'urn:li:person:' . $args['id'],
			  'lifecycleState' => 'PUBLISHED',
			  'specificContent' =>
			  array (
				  'com.linkedin.ugc.ShareContent' =>
				  array (
					  'shareCommentary' =>
					  array (
						  'text' => $this->strip_excess_blank_lines( $post_details['content'] ) . $post_details['hashtags'],
					  ),
					  'shareMediaCategory' => 'IMAGE',
					  'media' =>
					  array (
						  0 =>
						  array (
							  'status' => 'READY',
							  'description' =>
							  array (
								  'text' => html_entity_decode( get_the_title( $post_details['post_id'] ) ),
							  ),
							  'media' => $asset,
							  'title' =>
							  array (
								  'text' => html_entity_decode( get_the_title( $post_details['post_id'] ) ),
							  ),
						  ),
					  ),
				  ),
			  ),
			  'visibility' =>
			  array (
				  'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
			  ),
		  );

		return $new_post;
	}

	private function rop_linkedin_api_v1( $post_details ) {

		$new_post = array(
			'comment'    => '',
			'content'    => array(
				'title'         => '',
				'description'   => '',
				'submitted-url' => '',
			),
			'visibility' => array(
				'code' => 'anyone',
			),
		);

		if ( ! empty( $post_details['post_image'] ) ) {
			// If we have an video, share the placeholder, otherwise, share the image.
			if ( strpos( $post_details['mimetype']['type'], 'video' ) === false ) {
				$new_post['content']['submitted-image-url'] = $post_details['post_image'];
			} else {
				$new_post['content']['submitted-image-url'] = ROP_LITE_URL . 'assets/img/video_placeholder.jpg';
			}
		}

		$new_post['comment']                = $this->strip_excess_blank_lines( $post_details['content'] ) . $post_details['hashtags'];
		$new_post['content']['description'] = $post_details['content'];
		$new_post['content']['title']       = html_entity_decode( get_the_title( $post_details['post_id'] ) );

		$url_to_share = $this->get_url( $post_details );
		/**
		 * If the url is not present, use the image instead in order for the share to be successful.
		 */
		if ( empty( $url_to_share ) && ! empty( $post_details['post_image'] ) ) {
			$url_to_share = $post_details['post_image'];
		}
		$new_post['content']['submitted-url'] = $url_to_share;

		$new_post['visibility']['code'] = 'anyone';

		return $new_post;

	}

}
