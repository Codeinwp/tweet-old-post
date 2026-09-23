<?php
/**
 * Stand-in for another plugin's abraham/twitteroauth 4.0.1 Request.
 * Only the argument order matters: 4.0.1 takes the token second and the
 * HTTP method third, the opposite of the Codeinwp fork.
 *
 * @package ROP
 */

namespace Abraham\TwitterOAuth;

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
