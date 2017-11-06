<?php
class Rop_Rvivly extends Rop_Url_Shortner_Abstract {

    /**
     * Method to inject functionality into constructor.
     *
     * @since   8.0.0
     * @access  public
     * @return mixed
     */
    public function init() {
        $this->service_name = 'rviv,ly';
        $this->credentials = false;
    }

    /**
     * Method to return the needed credentials for this service.
     *
     * @since   8.0.0
     * @access  public
     * @return array
     */
    public function get_required_credentials() {
        return $this->credentials;
    }

    /**
     * Returns the stored credentials from DB.
     *
     * @since   8.0.0
     * @access  public
     * @return mixed
     */
    public function get_credentials() {
        return $this->credentials;
    }

    /**
     * Updates the credentials in DB.
     *
     * @since   8.0.0
     * @access  public
     * @return mixed
     */
    public function set_credentials() {
        return $this->credentials;
    }

    /**
     * Method to retrieve the shorten url from the API call.
     *
     * @since   8.0.0
     * @access  public
     * @param   string $url The url to shorten.
     * @return string
     */
    public function shorten_url($url) {
        $website = get_bloginfo( "url" );
        $response = $this->callAPI(
            ROP_YOURLS_SITE,
            array( "method" => "post" ),
            array( "action" => "shorturl", "format" => "simple", "signature" => substr( md5($website . md5(ROP_YOURLS_SALT)), 0, 10 ), "url" => $url, "website" => base64_encode( $website ) ),
            null
        );
        if ( intval($response["error"]) == 200 ) {
            $shortURL = $response["response"];
        }
        return $shortURL;
    }
}