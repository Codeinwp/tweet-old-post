<?php
/**
 * Minimal stand-in for another plugin's abraham/twitteroauth 4.0.1, the
 * contract Autopost for X bundles. Only the argument order matters here:
 * 4.0.1 takes the token second and the HTTP method third.
 *
 * @package     ROP
 * @subpackage  Tests
 */

namespace Abraham\TwitterOAuth;

class Consumer {
	public $key;
	public $secret;
	public function __construct( $key, $secret ) {
		$this->key    = $key;
		$this->secret = $secret;
	}
}

class Token {
	public $key;
	public $secret;
	public function __construct( $key, $secret ) {
		$this->key    = $key;
		$this->secret = $secret;
	}
}

class Request {
	public $method;
	public $url;
	public static function fromConsumerAndToken( Consumer $consumer, ?Token $token, string $httpMethod, string $httpUrl, array $parameters = array(), $json = false ) {
		$request         = new self();
		$request->method = $httpMethod;
		$request->url    = $httpUrl;
		return $request;
	}
}

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
