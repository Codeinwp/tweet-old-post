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

	/**
	 * Known social network errors log a docs link with UTMs, not an is.gd short link.
	 *
	 * @covers Rop_Services_Abstract::rop_get_error_docs
	 */
	public function test_error_help_links_carry_utms() {
		$logger = new Rop_Logger();
		$logger->clear_user_logs();

		$service = new Rop_Twitter_Service();
		$method  = new ReflectionMethod( $service, 'rop_get_error_docs' );
		$method->setAccessible( true );
		$method->invoke( $service, 'Error: User is over daily status update limit.' );

		$logs = wp_json_encode( $logger->get_logs(), JSON_UNESCAPED_SLASHES );
		$this->assertStringContainsString(
			'https://docs.revive.social/faqs/fix-twitter-error-185-user-is-over-daily-status-update-limit?utm_source=wpadmin&utm_medium=sharing-logs&utm_campaign=twitter-daily-limit',
			$logs
		);
		$this->assertStringNotContainsString( 'is.gd', $logs );
	}

	/**
	 * The Bit.ly missing token error logs the Bit.ly docs with UTMs.
	 *
	 * @covers Rop_Bitly_Shortner::shorten_url
	 */
	public function test_bitly_missing_token_links_to_docs() {
		$logger = new Rop_Logger();
		$logger->clear_user_logs();

		// Sites that connected Bit.ly with the old login keys have no generic access token saved.
		$shortner = new Rop_Bitly_Shortner();
		$shortner->set_credentials( array( 'user' => 'legacy-user', 'key' => 'legacy-key' ) );
		$shortner->shorten_url( 'https://example.org/' );

		$logs = wp_json_encode( $logger->get_logs(), JSON_UNESCAPED_SLASHES );
		$this->assertStringContainsString(
			'https://docs.revive.social/getting-started/how-to-connect-bit-ly-to-revive-old-posts?utm_source=wpadmin&utm_medium=sharing-logs&utm_campaign=bitly',
			$logs
		);
	}

	/**
	 * The LinkedIn token log message links to the docs with UTMs.
	 *
	 * @covers Rop_I18n::get_labels
	 */
	public function test_linkedin_token_log_links_to_docs() {
		$this->assertStringContainsString(
			'https://docs.revive.social/general-guides/how-to-refresh-linkedin-access-token?utm_source=wpadmin&utm_medium=sharing-logs&utm_campaign=linkedin-token',
			Rop_I18n::get_labels( 'general.rop_linkedin_refresh_token' )
		);
	}

	/**
	 * The PHP version notice links to the docs with UTMs written by hand, since it shows before the SDK loads.
	 *
	 * @covers ::rop_php_notice
	 */
	public function test_php_notice_links_to_docs_with_utms() {
		ob_start();
		rop_php_notice();
		$notice = ob_get_clean();

		$this->assertStringContainsString(
			'href="https://docs.revive.social/general-guides/how-to-update-your-php-version?utm_source=wpadmin&amp;utm_medium=admin-notice&amp;utm_campaign=php-version',
			$notice
		);
	}
}
