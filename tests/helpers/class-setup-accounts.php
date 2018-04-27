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

/**
 * Utility method to populate accounts.
 */
class Rop_InitAccounts {
	/**
	 * Account test id.
	 */
	const ROP_TEST_ACCOUNT_ID = '289389218391';
	/**
	 * Test service id.
	 */
	const ROP_TEST_SERVICE_ID = '18u23891';
	/**
	 * Test account service.
	 */
	const ROP_TEST_SERVICE_NAME = 'twitter';
	/**
	 * @var array Base api config.
	 */
	public static $baseApiClasses = array(
		'Facebook' => array(
			'class'            => 'Facebook\Facebook',
			'credentials'      => array(
				'470293890022208',
				'bf3ee9335692fee071c1a41fbe52fdf5'
			),
			'credentials_name' => array(
				'app_id',
				'secret'
			)
		),
		'Twitter'  => array(
			'class'            => 'Abraham\TwitterOAuth\TwitterOAuth',
			'credentials'      => array(
				'',
				''
			),
			'credentials_name' => array(
				'oauth_token',
				'oauth_token_secret'
			)
		),
	);
	private static $factory;

	/**
	 * Testing services avilability.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public static function init() {

		$services_model = new Rop_Services_Model();
		$factory        = new Rop_Services_Factory();
		try {
			$test_twitter_service = $factory->build( 'twitter' );
		} catch ( Exception $exception ) {

		}
		$user_test                                                           = wp_parse_args( array(
			'account' => 'test account',
			'user'    => '@roptest',
			'id'      => self::ROP_TEST_ACCOUNT_ID,
			'service' => 'twitter'
		), $test_twitter_service->user_default );
		$service_test                                                        = array(
			'id'                 => self::ROP_TEST_SERVICE_ID,
			'service'            => 'twitter',
			'credentials'        => array(),
			'public_credentials' => false,
			'available_accounts' => array( $user_test ),
		);
		$new_service[ $service_test['service'] . '_' . $service_test['id'] ] = $service_test;
		$services_model->add_authenticated_service( $new_service );
		self::$factory = new WP_UnitTest_Factory();
	}

	public static function generatePosts( $count = 1, $type = 'post', $time_shift = '- 1 day' ) {
		$post_ids = array();
		$date     = date( 'Y-m-d H:i:s' );
		if ( $time_shift ) {
			$date = date( 'Y-m-d H:i:s', strtotime( $time_shift ) );
		}
		$tags = array();
		$cats = array();
		// Setup terms.
		for ( $i = 0; $i < 5; $i ++ ) {
			$tag_name = sprintf( 'Tag %s', $i );
			$tag      = wp_insert_term( $tag_name, 'post_tag' );
			$tags[]   = $tag;
		}
		for ( $i = 0; $i < 5; $i ++ ) {
			$cat_name = sprintf( 'Category %s', $i );
			$cat      = wp_insert_term( $cat_name, 'category' );
			$cats[]   = $cat;
		}
		//var_dump( $date );
		for ( $i = 0; $i < $count; $i ++ ) {
			$content   = file_get_contents( 'https://loripsum.net/api/5/medium/plaintext' );
			$id        = self::$factory->post->create( array(
				'post_title'   => 'Test Post ' . str_pad( $i + 1, 2, "0", STR_PAD_LEFT ),
				'post_content' => $content,
				'post_type'    => $type,
				'post_date'    => $date,
				'post_status'  => 'publish'
			) );
			$tag_value = $tags[ rand( 0, count( $tags ) - 1 ) ];
			$cat_value = $cats[ rand( 0, count( $cats ) -1  )  ];

			wp_set_post_terms( $id, $tag_value['term_id'], 'post_tag' );
			wp_set_post_terms( $id, $cat_value['term_id'], 'category' );
			array_push( $post_ids, $id );
		}

		return $post_ids;
	}

	public static function get_account_id() {
		return self::ROP_TEST_SERVICE_NAME . '_' . self::ROP_TEST_SERVICE_ID . '_' . self::ROP_TEST_ACCOUNT_ID;
	}
}

