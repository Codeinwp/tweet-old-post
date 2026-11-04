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
		unset( $_GET['post'], $_GET['post_type'], $_GET['meta-box-loader'], $_GET['classic-editor__forget'], $GLOBALS['post'] );
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

	/**
	 * With users allowed to switch, Classic Editor picks the editor through the post filter.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_classic_editor_plugin_choice_is_followed_for_posts_without_blocks(): void {
		if ( ! class_exists( 'Classic_Editor' ) ) {
			eval( 'class Classic_Editor {}' );
		}
		update_option( 'classic-editor-allow-users', 'allow' );
		update_option( 'classic-editor-replace', 'block' );
		add_filter(
			'use_block_editor_for_post',
			static function ( bool $use_block_editor, WP_Post $post ): bool {
				return has_blocks( $post ) ? $use_block_editor : false;
			},
			10,
			2
		);

		$_GET['post'] = (string) self::factory()->post->create( array( 'post_content' => 'Plain text.' ) );
		$this->assertTrue( Rop_Admin::is_classic_editor() );

		$_GET['post'] = (string) self::factory()->post->create( array( 'post_content' => '<!-- wp:paragraph --><p>Blocks.</p><!-- /wp:paragraph -->' ) );
		$this->assertFalse( Rop_Admin::is_classic_editor() );
	}

	/**
	 * Switching a post to the block editor overrides a classic profile setting.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_switch_to_block_editor_is_followed_over_classic_user_setting(): void {
		if ( ! class_exists( 'Classic_Editor' ) ) {
			eval( 'class Classic_Editor {}' );
		}
		update_option( 'classic-editor-allow-users', 'allow' );
		update_option( 'classic-editor-replace', 'classic' );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		update_user_meta( get_current_user_id(), 'wp_classic-editor-settings', 'classic' );
		$post_id = self::factory()->post->create( array( 'post_content' => 'Plain text.' ) );
		update_post_meta( $post_id, 'classic-editor-remember', 'classic-editor' );
		// Classic Editor keeps the block editor for its "Switch to block editor" link.
		add_filter( 'use_block_editor_for_post', '__return_true', 100 );

		$_GET['post']                   = (string) $post_id;
		$_GET['classic-editor__forget'] = '';

		$this->assertFalse( Rop_Admin::is_classic_editor() );
	}

	public function test_block_editor_posts_are_not_classic(): void {
		$this->assertFalse( Rop_Admin::is_classic_editor() );

		$_GET['post'] = (string) self::factory()->post->create();
		$this->assertFalse( Rop_Admin::is_classic_editor() );
	}
}
