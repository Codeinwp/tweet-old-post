<?php
/**
 * ROP Test docs links for PHPUnit.
 *
 * The docs links must point at the current docs.revive.social pages: the old
 * /article/ URLs redirect, and the redirect drops the UTM parameters.
 *
 * @package     ROP
 * @subpackage  Tests
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Test docs links class.
 */
class Test_RopDocsLinks extends WP_UnitTestCase {

	/**
	 * The helper adds the UTMs to the docs page URL.
	 *
	 * @covers Rop_I18n::docs_url
	 */
	public function test_docs_url_adds_utms() {
		$this->assertSame(
			'https://docs.revive.social/general-guides/fix-revive-old-post-not-posting?utm_source=wpadmin&utm_medium=admin-notice&utm_campaign=wp-cron-disabled&utm_content=docsrevivesocialgeneral-guidesfix-revive-old-post-not-posting',
			Rop_I18n::docs_url( 'general-guides/fix-revive-old-post-not-posting', 'wp-cron-disabled', 'admin-notice' )
		);
	}

	/**
	 * A settings description links to its current docs page.
	 *
	 * @covers Rop_I18n::get_labels
	 */
	public function test_settings_label_links_to_current_page() {
		$this->assertStringContainsString(
			'href="https://docs.revive.social/general-guides/how-to-share-posts-immediately-with-revive-old-posts?utm_source=wpadmin&#038;utm_medium=general-settings&#038;utm_campaign=instant-sharing&#038;utm_content=docsrevivesocialgeneral-guideshow-to-share-posts-immediately-with-revive-old-posts"',
			Rop_I18n::get_labels( 'settings.instant_share_desc' )
		);
	}

	/**
	 * The LinkedIn token email shows the plain docs URL and links to it with UTMs.
	 *
	 * @covers Rop_I18n::get_labels
	 */
	public function test_linkedin_email_links_to_current_page() {
		$message = Rop_I18n::get_labels( 'emails.refresh_linkedin_token_message' );

		$this->assertStringContainsString( 'utm_medium=email&#038;utm_campaign=linkedin-token', $message );
		$this->assertStringContainsString( '">https://docs.revive.social/general-guides/how-to-refresh-linkedin-access-token</a>', $message );
	}

	/**
	 * No label links to an old /article/ URL.
	 *
	 * @covers Rop_I18n::get_labels
	 */
	public function test_no_label_uses_old_article_urls() {
		$this->assertStringNotContainsString( 'docs.revive.social/article/', wp_json_encode( Rop_I18n::get_labels(), JSON_UNESCAPED_SLASHES ) );
	}
}
