<?php
/**
 * ROP Test class for PHPUnit.
 *
 * @package     ROP
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */

/**
 * Test_ROP class.
 */
class Test_ROP extends WP_UnitTestCase {

    private $services = array(
        'facebook' => null,
        'twitter' => null,
        'linkedin' => null,
        'tumblr' => null,
    );

    private $baseApiClasses = array(
        'Facebook' => array(
            'class' => 'Facebook\Facebook',
            'credentials' => array(
                '470293890022208',
                'bf3ee9335692fee071c1a41fbe52fdf5'
            ),
            'credentials_name' => array(
                'app_id',
                'secret'
            )
        ),
        'Twitter' => array(
            'class' => 'Abraham\TwitterOAuth\TwitterOAuth',
            'credentials' => array(
                '',
                ''
            ),
            'credentials_name' => array(
                'oauth_token',
                'oauth_token_secret'
            )
        ),
        'LinkedIn' => array(
            'class' => 'LinkedIn\Client',
            'credentials' => array(
                '781biqyg6fhkam',
                'K1o5S03jnSDt11w8'
            ),
            'credentials_name' => array(
                'client_id',
                'secret'
            )
        ),
        'Tumblr' => array(
            'class' => 'Tumblr\API\Client',
            'credentials' => array(
                'oN3jqKF0VLW0BdpAMbbkL2PYtkpnePYxaRYf8rbX4R5SEnBbGW',
                'fQxyywKZJMc474SxVfZCYbrIXARnJS6DTYJoyiQ6sbWFvuM4Di'
            ),
            'credentials_name' => array(
                'consumer_key',
                'consumer_secret'
            )
        ),
    );

	/**
	 * Testing SDK loading.
	 *
	 * @access public
	 */
	public function test_sdk() {
		$this->assertTrue( class_exists( 'ThemeIsle_SDK_Loader' ) );
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
	public function test_services() {
        $service_factory = new Rop_Services_Factory();

        $this->services['facebook'] = $service_factory->build( 'facebook' );
        $this->services['twitter'] = $service_factory->build( 'twitter' );
        $this->services['linkedin'] = $service_factory->build( 'linkedin' );
        $this->services['tumblr'] = $service_factory->build( 'tumblr' );

        foreach ( $this->services as $service ) {
            $this->assertInstanceOf( 'Rop_Services_Abstract', $service );
            $service->get_api( $this->baseApiClasses[$service->display_name]['credentials'][0], $this->baseApiClasses[$service->display_name]['credentials'][1] );
            $service->expose_endpoints();
            $service->get_endpoint_url( 'authorize' );
            $service->set_api( $this->baseApiClasses[$service->display_name]['credentials'][0], $this->baseApiClasses[$service->display_name]['credentials'][1] );
            $api = $service->get_api();
            $this->assertInstanceOf( $this->baseApiClasses[$service->display_name]['class'], $api );
            $data['credentials'] = array(
                $this->baseApiClasses[$service->display_name]['credentials_name'][0] => $this->baseApiClasses[$service->display_name]['credentials'][0],
                $this->baseApiClasses[$service->display_name]['credentials_name'][1] => $this->baseApiClasses[$service->display_name]['credentials'][1],
            );
            $service->set_credentials( $data['credentials'] );

            $singin_url = @$service->sign_in_url( $data );
            $this->assertUriIsCorrect( $singin_url );

            $this->assertTrue( $service->share( array() ) );

            $this->assertTrue( is_array( $service->get_service() ) );
        }

    }

    /**
     * Testing post format
     *
     * @since   8.0.0
     * @access  public
     *
     * @covers Rop_Post_Format_Model
     */
    public function test_post_format() {
        $service = 'facebook';
        $account_id = 'test_id_facebook';
        $post_format_data = array(
            'post_content' => 'post_title',
            'maximum_length' => '190',
            'custom_text' => 'Custom text',
            'custom_text_pos' => 'end',
            'include_link' => true,
            'url_from_meta' => false,
            'short_url' => true,
            'hashtags' => 'no-hastags',
            'hashtags_length' => '',
            'hashtags_custom' => '',
            'image' => false,
        );
        $global_settings = new Rop_Global_Settings();
        $defaults = $global_settings->get_default_post_format( $service );

	    $post_format = new Rop_Post_Format_Model( $service );

	    $this->assertEquals( $post_format->get_post_format( $account_id ), $defaults );

	    $post_format->add_update_post_format( $account_id, $post_format_data );


        $this->assertEquals( $post_format->get_post_format( $account_id ), $post_format_data );

        $post_format->remove_post_format( $account_id );

        $this->assertEquals( $post_format->get_post_format( $account_id ), $defaults );

    }

    private function assertUriIsCorrect( $uri ) {
        // https://stackoverflow.com/questions/30847/regex-to-validate-uris
        // http://snipplr.com/view/6889/regular-expressions-for-uri-validationparsing/
        if( ! preg_match( "/^([a-z][a-z0-9+.-]*):(?:\\/\\/((?:(?=((?:[a-z0-9-._~!$&'()*+,;=:]|%[0-9A-F]{2})*))(\\3)@)?(?=(\\[[0-9A-F:.]{2,}\\]|(?:[a-z0-9-._~!$&'()*+,;=]|%[0-9A-F]{2})*))\\5(?::(?=(\\d*))\\6)?)(\\/(?=((?:[a-z0-9-._~!$&'()*+,;=:@\\/]|%[0-9A-F]{2})*))\\8)?|(\\/?(?!\\/)(?=((?:[a-z0-9-._~!$&'()*+,;=:@\\/]|%[0-9A-F]{2})*))\\10)?)(?:\\?(?=((?:[a-z0-9-._~!$&'()*+,;=:@\\/?]|%[0-9A-F]{2})*))\\11)?(?:#(?=((?:[a-z0-9-._~!$&'()*+,;=:@\\/?]|%[0-9A-F]{2})*))\\12)?$/i", $uri ) )
        {
            throw new \RuntimeException( "URI has not a valid format." );
        }
    }


}
