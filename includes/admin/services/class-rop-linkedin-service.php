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
	protected $scopes = array( 'r_liteprofile', 'r_emailaddress', 'w_member_social' );
	// Company(organization) sharing scope cannot be used unless app approved for this scope.
	// Added here for future reference
	// https://stackoverflow.com/questions/54821731/in-linkedin-api-v2-0-how-to-get-company-list-by-persons-token
	// https://business.linkedin.com/marketing-solutions/marketing-partners/become-a-partner/marketing-developer-program
	// protected $scopes = array( 'r_liteprofile', 'r_emailaddress', 'w_member_social', , 'w_organization_social');

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

		$api = $this->get_api( $credentials['client_id'], $credentials['secret'] );
		try {
			$accessToken = $api->getAccessToken( $_GET['code'] );

			$_SESSION['rop_linkedin_token'] = $accessToken->getToken();

			parent::authorize();

		} catch ( GuzzleHttp\Exception\ConnectException $e ) {
			$this->error->throw_exception( '400 Bad Request', 'Linkedin API returned an error: ' . $e->getMessage() );
		} catch ( Exception $e ) {
			$this->error->throw_exception( '400 Bad Request', 'Linkedin API returned an error X: ' . $e->getMessage() );
		}
		return false;
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
				'me?projection=(id,firstName,lastName,profilePicture(displayImage~:playableStreams))',
				array(),
				'GET'
			);
		} catch ( Exception $e ) {
			$this->logger->alert_error( 'Cannot get linkedin user details. Error ' . $e->getMessage() );
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
			'available_accounts' => $this->get_users( $profile, $api ),
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
	private function get_users( $data = null, $api ) {
		if ( empty( $data ) ) {
			return array();
		}

		try {
			$email_array = $api->api(
				'emailAddress?q=members&projection=(elements*(handle~))',
				array(),
				'GET'
			);
		} catch ( Exception $e ) {
			$this->logger->alert_error( 'Cannot get linkedin user email. Error ' . $e->getMessage() );
		}

		$email = $email_array['elements']['0']['handle~']['emailAddress'];

		$img          = $data['profilePicture']['displayImage~']['elements']['0']['identifiers']['0']['identifier'];
		$user_details = $this->user_default;

		$fname_array = array_values( $data['firstName']['localized'] );
		$firstname   = $fname_array[0];

		$lname_array = array_values( $data['lastName']['localized'] );
		$lastname    = $lname_array[0];

		$user_details['id']      = $this->strip_underscore( $data['id'] );
		$user_details['account'] = $email;
		$user_details['user']    = $firstname . ' ' . $lastname;
		$user_details['img']     = $img;

		$users = array( $user_details );

		/*
	We're not requesting the w_organization_social scope so code below wouldn't be used.
	See scope property at the top of file for more details.

	try {
		$companies = $this->api->api(
			'organizationalEntityAcls?q=roleAssignee&role=ADMINISTRATOR&projection=(elements*(organizationalTarget~(localizedName,vanityName,logoV2)))',
			array(),
			'GET'
		);
	} catch ( Exception $e ) {
		return $users;
	}
	if ( empty( $companies ) ) {
		return $users;
	}
	if ( empty( $companies['elements'] ) ) {
		return $users;
	}
	foreach ( $companies['elements'] as $company ) {
		$users[] = wp_parse_args(
			array(
				'id'         => $this->strip_underscore( $company['id'] ),
				'account'    => $company['localizedName'],
				'is_company' => true,
				'user'       => $company['localizedName'],
			),
			$this->user_default
		);
	}
	*/

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
		try{

		}catch ( Exception $e ) {
			$this->error->throw_exception( '400 Bad Request', 'TEST: ' . $e->getMessage() );
		}
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

		if ( get_post_type( $post_details['post_id'] ) !== 'attachment' ) {
			// If post image option unchecked, share as article post
			if ( empty( $post_details['post_image'] ) ) {
				$new_post = $this->linkedin_article_post( $post_details, $args );
			} else {
				$new_post = $this->linkedin_image_post( $post_details, $args, $token, $api );
			}
		} elseif ( get_post_type( $post_details['post_id'] ) === 'attachment' ) {
			// Linkedin Api v2 doesn't support video upload. Share as article post
			if ( strpos( get_post_mime_type( $post_details['post_id'] ), 'video' ) !== false ) {
				$new_post = $this->linkedin_article_post( $post_details, $args );
			} else {
				$new_post = $this->linkedin_image_post( $post_details, $args, $token, $api );
			}
		}

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
			$this->logger->alert_error( 'Cannot share to linkedin. Error:  ' . $exception->getMessage() );
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

		$new_post = array(
			'author'          => 'urn:li:person:' . $args['id'],
			'lifecycleState'  => 'PUBLISHED',
			'specificContent' =>
				array(
					'com.linkedin.ugc.ShareContent' =>
						array(
							'shareCommentary'    =>
								array(
									'text' => $this->strip_excess_blank_lines( $post_details['content'] ) . $this->get_url( $post_details ) . $post_details['hashtags'],
								),
							'shareMediaCategory' => 'ARTICLE',
							'media'              =>
								array(
									0 =>
										array(
											'status'      => 'READY',
											'description' =>
												array(
													'text' => $this->strip_excess_blank_lines( $post_details['content'] ),
												),
											'originalUrl' => trim( $this->get_url( $post_details ) ),
											'title'       =>
												array(
													'text' => html_entity_decode( get_the_title( $post_details['post_id'] ) ),
												),
										),
								),
						),
				),
			'visibility'      =>
				array(
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
	 * @param   array $post_details The post details to be published by the service.
	 * @param   array $args Arguments needed by the method.
	 * @param   string $token The user token.
	 *
	 * @return array
	 */
	private function linkedin_image_post( $post_details, $args, $token, $api ) {

		$register_image = array(
			'registerUploadRequest' =>
				array(
					'recipes'              =>
						array(
							0 => 'urn:li:digitalmediaRecipe:feedshare-image',
						),
					'owner'                => 'urn:li:person:' . $args['id'],
					'serviceRelationships' =>
						array(
							0 =>
								array(
									'relationshipType' => 'OWNER',
									'identifier'       => 'urn:li:userGeneratedContent',
								),
						),
				),
		);

		$response   = $api->post( 'https://api.linkedin.com/v2/assets?action=registerUpload', $register_image );
		$upload_url = $response['value']['uploadMechanism']['com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest']['uploadUrl'];
		$asset      = $response['value']['asset'];

		// If this is an attachment post we need to make sure we pass the URL to get_path_by_url() correctly
		if ( get_post_type( $post_details['post_id'] ) === 'attachment' ) {
			$img = $this->get_path_by_url( wp_get_attachment_url( $post_details['post_id'] ), $post_details['mimetype'] );
		} else {
			$img = $this->get_path_by_url( $post_details['post_image'], $post_details['mimetype'] );
		}

		$img_mime_type = image_type_to_mime_type( exif_imagetype( $img ) );

		$img_data   = file_get_contents( $img );
		$img_length = strlen( $img_data );

		$wp_img_put = wp_remote_request(
			$upload_url,
			array(
				'method'  => 'PUT',
				'headers' => array( 'Authorization' => 'Bearer ' . $token, 'Content-type' => $img_mime_type, 'Content-Length' => $img_length ),
				'body'    => $img_data,
			)
		);

		if ( ! empty( $wp_img_put['body'] ) ) {
			$response_code    = $wp_img_put['response']['code'];
			$response_message = $wp_img_put['response']['message'];
			$this->logger->alert_error( 'Cannot share to linkedin. Error:  ' . $response_code . ' ' . $response_message );
			exit( 1 );
		}

		$new_post = array(
			'author'          => 'urn:li:person:' . $args['id'],
			'lifecycleState'  => 'PUBLISHED',
			'specificContent' =>
				array(
					'com.linkedin.ugc.ShareContent' =>
						array(
							'shareCommentary'    =>
								array(
									'text' => $this->strip_excess_blank_lines( $post_details['content'] ) . $this->get_url( $post_details ) . $post_details['hashtags'],
								),
							'shareMediaCategory' => 'IMAGE',
							'media'              =>
								array(
									0 =>
										array(
											'status'      => 'READY',
											'description' =>
												array(
													'text' => html_entity_decode( get_the_title( $post_details['post_id'] ) ),
												),
											'media'       => $asset,
											'title'       =>
												array(
													'text' => html_entity_decode( get_the_title( $post_details['post_id'] ) ),
												),
										),
								),
						),
				),
			'visibility'      =>
				array(
					'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
				),
		);

		return $new_post;
	}

}
