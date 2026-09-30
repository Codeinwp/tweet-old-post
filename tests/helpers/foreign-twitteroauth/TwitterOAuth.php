<?php
/**
 * Stand-in for another plugin's abraham/twitteroauth 4.0.1 client.
 *
 * @package ROP
 */

namespace Abraham\TwitterOAuth;

class TwitterOAuth {
	private $consumer;
	private $token;
	public function __construct( $key, $secret, $oauth_token = null, $oauth_token_secret = null ) {
		$this->consumer = new Consumer( $key, $secret );
		$this->token    = null === $oauth_token ? null : new Token( $oauth_token, $oauth_token_secret );
	}
	public function oauth( string $path ) {
		return Request::fromConsumerAndToken( $this->consumer, $this->token, 'POST', 'https://api.twitter.com/' . $path );
	}
}
