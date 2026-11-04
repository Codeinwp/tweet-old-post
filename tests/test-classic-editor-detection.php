<?php
/**
 * Classic editor detection tests.
 *
 * @package     ROP
 * @subpackage  Tests
 */

/**
 * Test Rop_Admin::is_classic_editor() without the official Classic Editor plugin.
 */
class Test_RopClassicEditorDetection extends WP_UnitTestCase {

	public function tearDown(): void {
		unset( $_GET['post'], $_GET['post_type'], $_GET['meta-box-loader'], $GLOBALS['post'] );
		unregister_post_type( 'rop_no_rest' );
		set_current_screen( 'front' );
		parent::tearDown();
	}

	public function test_existing_post_is_classic_when_post_filter_disables_block_editor(): void {
		add_filter( 'use_block_editor_for_post', '__return_false' );
		$_GET['post'] = (string) self::factory()->post->create();

		$this->assertTrue( Rop_Admin::is_classic_editor() );
	}

	public function test_new_post_is_classic_when_post_filter_disables_block_editor(): void {
		add_filter( 'use_block_editor_for_post', '__return_false' );

		$this->assertTrue( Rop_Admin::is_classic_editor() );
	}

	public function test_new_post_is_checked_as_the_requested_post_type(): void {
		add_filter(
			'use_block_editor_for_post',
			static function ( bool $use_block_editor, WP_Post $post ): bool {
				return 'page' === $post->post_type ? false : $use_block_editor;
			},
			10,
			2
		);

		$_GET['post_type'] = 'page';
		$this->assertTrue( Rop_Admin::is_classic_editor() );

		$_GET['post_type'] = 'post';
		$this->assertFalse( Rop_Admin::is_classic_editor() );
	}

	public function test_post_type_without_block_editor_support_is_classic(): void {
		register_post_type(
			'rop_no_rest',
			array(
				'public'       => true,
				'show_in_rest' => false,
			)
		);
		$_GET['post'] = (string) self::factory()->post->create( array( 'post_type' => 'rop_no_rest' ) );

		$this->assertTrue( Rop_Admin::is_classic_editor() );
	}

	public function test_block_editor_meta_box_save_is_not_classic_and_skips_nonce_check(): void {
		add_filter( 'use_block_editor_for_post', '__return_false' );
		set_current_screen( 'post' );
		$_GET['post']            = (string) self::factory()->post->create();
		$_GET['meta-box-loader'] = '1';

		$this->assertFalse( Rop_Admin::is_classic_editor() );
	}

	public function test_block_editor_posts_are_not_classic(): void {
		$this->assertFalse( Rop_Admin::is_classic_editor() );

		$_GET['post'] = (string) self::factory()->post->create();
		$this->assertFalse( Rop_Admin::is_classic_editor() );
	}
}
