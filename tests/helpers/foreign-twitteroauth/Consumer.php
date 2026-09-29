<?php
/**
 * Stand-in for another plugin's abraham/twitteroauth 4.0.1 Consumer.
 *
 * @package ROP
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
