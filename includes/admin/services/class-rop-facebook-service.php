<?php
class Rop_Facebook_Service extends Rop_Services_Abstract {

    protected $service_name = 'facebook';

    private $permissions = array( 'email', 'manage_pages', 'publish_pages' );

    private $app_id;

    private $secret;

    private $token;

    private $fb;

    public function init() {
        $this->display_name = 'Facebook';
        $this->credentials = $this->model->get_option( 'credentials' );

        $this->set_defaults( 'app_id' );
        $this->set_defaults( 'secret' );
        $this->set_defaults( 'token' );

        $this->register_endpoint( 'login', 'req_login' );
        $this->register_endpoint( 'auth', 'auth' );
    }

    private function set_defaults( $key ) {
        $this->$key = '';
        if( isset( $this->credentials[$key] ) && $this->credentials[$key] != '' && $this->credentials[$key] != null ) {
            $this->$key = $this->credentials[$key];
        }
    }

    public function get_token() {
        return $this->token;
    }

    public function set_token( $value ) {
        $this->token = $value;
        $this->credentials['token'] = $this->token;
        $this->model->set_option( 'credentials', $this->credentials );
    }

    public function credentials( $args ) {
        foreach ( $args as $key => $value ) {
            if( in_array( $key, array( 'app_id' , 'secret' ) ) ) {
                $this->$key = $value;
                $this->credentials[$key] = $this->$key;
            }
        }
        $this->model->set_option( 'credentials', $this->credentials );
    }

    public function auth() {
        if( ! session_id() ) {
            session_start();
        }

        $error = new Rop_Exception_Handler();

        $this->fb = new \Facebook\Facebook([
            'app_id' => $this->app_id,
            'app_secret' => $this->secret,
            'default_graph_version' => 'v2.10',
        ]);

        $fb = $this->fb;

        $helper = $fb->getRedirectLoginHelper();

        if( isset( $this->token ) && $this->token != '' && $this->token != null ) {
            $longAccessToken = new \Facebook\Authentication\AccessToken( $this->token );
        } else {
            try {
                $accessToken = $helper->getAccessToken();
                if ( ! isset( $accessToken ) ) {
                    if ( $helper->getError() ) {
                        $error->throw_exception( '401 Unauthorized', $error->get_fb_exeption_message( $helper ) );
                    } else {
                        $error->throw_exception( '400 Bad Request', 'Bad request' );
                    }
                }
                $expires = time() + ( 120 * 24 * 60 * 60 ); // 120 days; 24 hours; 60 minutes; 60 seconds.
                $longAccessToken = new \Facebook\Authentication\AccessToken( $accessToken, $expires );
            } catch( Facebook\Exceptions\FacebookResponseException $e ) {
                $error->throw_exception( '400 Bad Request', 'Graph returned an error: ' . $e->getMessage() );
            } catch( Facebook\Exceptions\FacebookSDKException $e ) {
                $error->throw_exception( '400 Bad Request', 'Facebook SDK returned an error: ' . $e->getMessage() );
            }
        }
        
        $this->set_token( $longAccessToken->getValue() );
        $fb->setDefaultAccessToken( $this->token );
        
        try {
            // Returns a `Facebook\FacebookResponse` object
            $response = $fb->get( '/me?fields=id,name', $this->token );
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            $error->throw_exception( '400 Bad Request', 'Graph returned an error: ' . $e->getMessage() );
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            $error->throw_exception( '400 Bad Request', 'Facebook SDK returned an error: ' . $e->getMessage() );
        }

        $user = $response->getGraphUser();
        if( $user->getId() ) {
            $this->is_auth = true;
        }
    }

    public function get_user( $page ) {
        $user = new Rop_User_Model( array(
            'user_id' => $page['id'],
            'user_name' => $page['name'],
            'user_picture' => $page['img'],
            'user_service' => $this->service_name,
            'user_credentials' => array(
                'token' => $page['access_token']
            )
        ) );
        return $user;
    }

    public function get_pages() {
        $pages_array = array();
        $fb = $this->fb;
        $pages = $fb->get('/me/accounts');
        $pages = $pages->getGraphEdge()->asArray();
        foreach ($pages as $key) {

            $img = $fb->sendRequest( 'GET','/'.$key['id'].'/picture', array( 'redirect' => false ) );
            $img = $img->getGraphObject()->asArray();

            $pages_array[] = array(
              'id' => $key['id'],
              'name' => $key['name'],
              'img' => $img['url'],
              'access_token' => $key['access_token'],
            );
        }
        return $pages_array;
    }

    public function share( $post_details ) {
        $error = new Rop_Exception_Handler();
        $id = '1168461009964049';
        $page_token = 'EAAGrutRBO0ABAHa5ZCq2OWBsZC3o2y6lZA5TQPBNUzBkLZBZCdg28EymWSvJG8yh4H2a5n2ZCP4YibXd5i5YGiS29sltqStlwNvCnxTUV9tUwPyfd1wZBQ3RZC7hp3YZAuVBjYgXdUgZBY3MeqU5IlvKnZBOPHyo5g4ilO2FZC2q5CpkCBiJ3Nk849ZBNDjAIcZBPmadEZD';
        $fb = $this->fb;
        try {
            $post = $fb->post( '/' . $id . '/feed', array('message' => $post_details['message'], 'link' => 'https://themeisle.com', 'picture' => 'https://cdn.pixabay.com/photo/2016/01/19/18/00/city-1150026_960_720.jpg', ), $page_token );
            $post = $post->getGraphNode()->asArray();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            $error->throw_exception( '400 Bad Request', 'Graph returned an error: ' . $e->getMessage() );
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            $error->throw_exception( '400 Bad Request', 'Facebook SDK returned an error: ' . $e->getMessage() );
        }

        var_dump( $post );
    }

}