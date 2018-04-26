<?php
/**
 * ROP Test Content manipulation actions for PHPUnit.
 *
 * @package     ROP
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */

/**
 * Test content related actions. class.
 */
class Test_RopContent extends WP_UnitTestCase {

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

		$text_long                         = <<<EOD
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
		$expected_text_long                = <<<EOD
Lorem ipsum dolor sit amet, consectetur adipiscing elit.
Cras dolor enim, maximus ut risus sed, faucibus ullamcorper nisi.
Proin venenatis dui ornare, accumsan sem id, laoreet diam.
Aenean massa purus, tristique eget ipsum ac, mollis feugiat urna. Phasellus
EOD;
		$expected_text_long_ellipse        = <<<EOD
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

		$text_short          = "Nullam sit amet ex vel nibh tristique sollicitudin non vitae risus.";
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

}
