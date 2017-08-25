<?php
class Rop_Exception_Handler {
	private $exception = array();

	public function get_fb_exeption_message( $helper ) {
		$message = 'Error: ' . $helper->getError() . PHP_EOL;
		$message .= 'Error Code: ' . $helper->getErrorCode() . PHP_EOL;
		$message .= 'Error Reason: ' . $helper->getErrorReason() . PHP_EOL;
		$message .= 'Error Description: ' . $helper->getErrorDescription() . PHP_EOL;

		return $message;
	}

	public function register_exception( $message ) {
		$this->exception[] = $message;
	}

	public function throw_exception( $header, $message ) {
		header( 'HTTP/1.0 ' . $header );
		echo $message;
		exit;
	}

}
