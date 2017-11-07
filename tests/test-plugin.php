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
     * Testing posts selector
     *
     * @since   8.0.0
     * @access  public
     *
     * @covers Rop_Model_Abstract
     * @covers Rop_Posts_Selector_Model::<public>
     * @covers Rop_Settings_Model::<public>
     */
    public function test_posts_selector() {
        $page_ids_min5 = $this->generatePosts( 3, 'page', '-5 day' );
        $page_ids_now = $this->generatePosts( 2, 'page', false );
        $page_ids_min65 = $this->generatePosts( 5, 'page', '-65 day' );


        $post_ids_min5 = $this->generatePosts( 3, 'post', '-5 day' );
        $post_ids_now = $this->generatePosts( 2, 'post', false );
        $post_ids_min65 = $this->generatePosts( 5, 'post', '-65 day' );

        $settings = new Rop_Settings_Model();
        $global_settings = new Rop_Global_Settings();

        $this->assertEquals( $settings->get_settings(), $global_settings->get_default_settings() );

        $new_settings = $settings->get_settings();

        $new_settings['minimum_post_age'] = 1;
        $new_settings['maximum_post_age'] = 365;
        $new_settings['selected_post_types'] = array( array( 'name' => 'Posts', 'selected' => true, 'value' => 'page' ) );

        $settings->save_settings( $new_settings );

        $this->assertEquals( $settings->get_settings(), $new_settings );

	    $posts_selector = new Rop_Posts_Selector_Model();

	    $this->assertEquals( sizeof( $posts_selector->select( 'test_id_facebook' ) ), $settings->get_number_of_posts() );
    }

    /**
     * Testing post format
     *
     * @since   8.0.0
     * @access  public
     *
     * @covers Rop_Model_Abstract
     * @covers Rop_Post_Format_Model::<public>
     */
    public function test_post_format() {
        $service = 'facebook';
        $account_id = 'test_id_facebook';
        $post_format_data = array(
            'post_content' => 'post_title',
            'custom_meta_field' => '',
            'maximum_length' => '190',
            'custom_text' => 'I am the King of Random!',
            'custom_text_pos' => 'beginning',
            'include_link' => true,
            'url_from_meta' => false,
            'url_meta_key' => '',
            'short_url' => true,
            'short_url_service' => 'rviv.ly',
            'hashtags' => 'common-hashtags',
            'hashtags_length' => '15',
            'hashtags_common' => '#testLikeABoss, #themeIsle',
            'hashtags_custom' => '',
            'image' => true,
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

    /**
     * Testing URL shortners
     *
     * @since   8.0.0
     * @access  public
     *
     * @covers Rop_Url_Shortner_Abstract
     * @covers Rop_Rvivly_Shortner
     * @covers Rop_Bitly_Shortner
     * @covers Rop_Shortest_Shortner
     * @covers Rop_Googl_Shortner
     * @covers Rop_Isgd_Shortner
     */
    public function test_url_shortners() {
        $url = 'http://google.com/';

        // rviv.ly Test
//        $rvivly = new Rop_Rvivly();
//        $rvivly->set_website( $url );
//        $short_url = $rvivly->shorten_url( $url );
//        var_dump( $short_url );
//        $this->assertNotEquals( $url, $short_url );
//        $this->assertUriIsCorrect( $short_url );
//        $this->assertNotEquals( $short_url, '' );

        // bit.ly Test
        $bitly = new Rop_Bitly_Shortner();
        $user = 'o_57qgimegp1';
        $key = 'R_9a63d988de77438aaa6b3cd8e0830b6b';
        $bitly->set_credentials( array( 'user' => $user, 'key' => $key ) );
        $short_url = $bitly->shorten_url( $url );

        $this->assertNotEquals( $url, $short_url );
        $this->assertUriIsCorrect( $short_url );
        $this->assertNotEquals( $short_url, '' );

        // shorte.st Test
        $shortest = new Rop_Shortest_Shortner();
        $key = 'e3b65f77eddddc7c0bf1f3a2f5a13f59';
        $shortest->set_credentials( array( 'key' => $key ) );
        $short_url = $shortest->shorten_url( $url );

        $this->assertNotEquals( $url, $short_url );
        $this->assertUriIsCorrect( $short_url );
        $this->assertNotEquals( $short_url, '' );

        // goo.gl Test
        $googl = new Rop_Googl_Shortner();
        $key = 'AIzaSyAqNtuEu-xXurkpV-p57r5oAqQgcAyMSN4';
        $googl->set_credentials( array( 'key' => $key ) );
        $short_url = $shortest->shorten_url( $url );

        $this->assertNotEquals( $url, $short_url );
        $this->assertUriIsCorrect( $short_url );
        $this->assertNotEquals( $short_url, '' );

        // is.gd Test
        $isgd = new Rop_Isgd_Shortner();
        $isgd = $bitly->shorten_url( $url );

        $this->assertNotEquals( $url, $short_url );
        $this->assertUriIsCorrect( $short_url );
        $this->assertNotEquals( $short_url, '' );

    }

    /**
     * Testing publish content manipulations
     *
     * @since   8.0.0
     * @access  public
     *
     * @covers  Rop_Content_Helper<extended>
     */
    public function test_content_manipulations() {
        $ch = new Rop_Content_Helper();

        $text_long = <<<EOD
Lorem ipsum dolor sit amet, consectetur adipiscing elit.
Cras dolor enim, maximus ut risus sed, faucibus ullamcorper nisi.
Proin venenatis dui ornare, accumsan sem id, laoreet diam.
Aenean massa purus, tristique eget ipsum ac, mollis feugiat urna. Phasellus accelerata eleifend felis, nec tristique nulla. 
Nunc interdum velit nibh, eu mollis dolor imperdiet sed. Sed non cursus dolor. Sed a tortor mi. Ut ut nunc congue, 
blandit augue at, volutpat est. Nullam nec lectus sit amet justo feugiat auctor vitae id elit.

Nullam sit amet ex vel nibh tristique sollicitudin non vitae risus. Donec vitae tempus eros. Proin et nulla placerat, 
dictum metus quis, aliquam elit. Donec tempor erat ut mi sollicitudin gravida. Nullam ut maximus lacus, vitae convallis 
eros. Nulla tortor ipsum, hendrerit non lectus eget, congue bibendum purus. Proin varius tincidunt neque, eu commodo 
enim gravida eget. Nulla eu tristique dolor. Integer quis ante risus. Quisque bibendum tristique odio sit amet mollis. 

Duis posuere gravida mauris, quis finibus velit maximus et. Pellentesque at sodales tortor, ut dignissim velit. 
Morbi sit amet efficitur dui.
EOD;
        $expected_text_long = <<<EOD
Lorem ipsum dolor sit amet, consectetur adipiscing elit.
Cras dolor enim, maximus ut risus sed, faucibus ullamcorper nisi.
Proin venenatis dui ornare, accumsan sem id, laoreet diam.
Aenean massa purus, tristique eget ipsum ac, mollis feugiat urna. Phasellus
EOD;
        $expected_text_long_ellipse = <<<EOD
Lorem ipsum dolor sit amet, consectetur adipiscing elit.
Cras dolor enim, maximus ut risus sed, faucibus ullamcorper nisi.
Proin venenatis dui ornare, accumsan sem id, laoreet diam.
Aenean massa purus, tristique eget ipsum ac, mollis feugiat urna. ...
EOD;
        $expected_text_long_custom_ellipse = <<<EOD
Lorem ipsum dolor sit amet, consectetur adipiscing elit.
Cras dolor enim, maximus ut risus sed, faucibus ullamcorper nisi.
Proin venenatis dui ornare, accumsan sem id, laoreet diam.
Aenean massa purus, tristique eget ipsum ac, mollis feugiat urna. <-/-
EOD;

        $text_short = "Nullam sit amet ex vel nibh tristique sollicitudin non vitae risus.";
        $expected_text_short = "Nullam sit amet ex vel nibh tristique sollicitudin non vitae risus.";

        // Test Normal truncate at max 260 without breaking words.
        $this->assertEquals( $expected_text_long, $ch->token_truncate( $text_long, '260' ) );

        // Test Normal truncate on empty string
        $this->assertEquals( '', $ch->token_truncate( '', '260' ) );

        // Test Normal truncate on very short string
        $this->assertEquals( $expected_text_short, $ch->token_truncate( $text_short, '260' ) );

        // Test Normal truncate long w. ellipse added
        $ch->use_ellipse();
        $this->assertEquals( $expected_text_long_ellipse, $ch->token_truncate( $text_long, '260' ) );

        // Test Normal truncate short w. ellipse added should be same as w/o ellipse
        $ch->use_ellipse();
        $this->assertEquals( $expected_text_short, $ch->token_truncate( $text_short, '260' ) );

        // Test Normal truncate long w. custom ellipse added
        $ch->use_ellipse( true, '<-/-' );
        $this->assertEquals( $expected_text_long_custom_ellipse, $ch->token_truncate( $text_long, '260' ) );

    }

    private function assertUriIsCorrect( $uri ) {
        // https://stackoverflow.com/questions/30847/regex-to-validate-uris
        // http://snipplr.com/view/6889/regular-expressions-for-uri-validationparsing/
        if( ! preg_match( "/^([a-z][a-z0-9+.-]*):(?:\\/\\/((?:(?=((?:[a-z0-9-._~!$&'()*+,;=:]|%[0-9A-F]{2})*))(\\3)@)?(?=(\\[[0-9A-F:.]{2,}\\]|(?:[a-z0-9-._~!$&'()*+,;=]|%[0-9A-F]{2})*))\\5(?::(?=(\\d*))\\6)?)(\\/(?=((?:[a-z0-9-._~!$&'()*+,;=:@\\/]|%[0-9A-F]{2})*))\\8)?|(\\/?(?!\\/)(?=((?:[a-z0-9-._~!$&'()*+,;=:@\\/]|%[0-9A-F]{2})*))\\10)?)(?:\\?(?=((?:[a-z0-9-._~!$&'()*+,;=:@\\/?]|%[0-9A-F]{2})*))\\11)?(?:#(?=((?:[a-z0-9-._~!$&'()*+,;=:@\\/?]|%[0-9A-F]{2})*))\\12)?$/i", $uri ) )
        {
            throw new \RuntimeException( "URI has not a valid format." );
        }
    }


    private function generatePosts( $count = 1, $type = 'post', $time_shift = '- 1 day' ) {
        $post_ids = array();
        $date = date( 'Y-m-d H:i:s');
        if ( $time_shift ) {
            $date = date( 'Y-m-d H:i:s', strtotime( $time_shift ) );
        }

        //var_dump( $date );
        for ( $i = 0; $i < $count; $i++ ) {
            $content = file_get_contents('http://loripsum.net/api/5/medium/plaintext');
            $id = $this->factory->post->create( array(
                'post_title' => 'Test Post ' . str_pad( $i+1, 2, "0", STR_PAD_LEFT ),
                'post_content' => $content,
                'post_type' => $type,
                'post_date' => $date,
                'post_status' => 'publish'
            ) );
            array_push( $post_ids, $id );
        }

        return $post_ids;
    }

}
