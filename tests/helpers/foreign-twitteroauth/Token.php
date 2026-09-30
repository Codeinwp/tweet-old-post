<?php
/**
 * Stand-in for another plugin's abraham/twitteroauth 4.0.1 Token.
 *
 * @package ROP
 */

namespace Abraham\TwitterOAuth;

class Token {
	public $key;
	public $secret;
	public function __construct( $key, $secret ) {
		$this->key    = $key;
		$this->secret = $secret;
	}
}
