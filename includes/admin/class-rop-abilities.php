<?php
/**
 * Registers the plugin abilities with the WordPress Abilities API.
 *
 * @link       https://themeisle.com
 *
 * @package    Rop
 * @subpackage Rop/admin
 */

/**
 * Class Rop_Abilities
 *
 * Thin wrappers over the existing models, exposed through `wp_register_ability()`.
 * It is a no-op on WordPress versions that do not ship the Abilities API.
 *
 * @package    Rop
 * @subpackage Rop/admin
 * @author     Themeisle <friends@themeisle.com>
 */
class Rop_Abilities {

	/**
	 * The ability category slug.
	 */
	const CATEGORY = 'revive';

	/**
	 * Maximum number of queue items returned in one call.
	 */
	const MAX_QUEUE_ITEMS = 100;

	/**
	 * Maximum number of post IDs accepted in one exclusion change.
	 */
	const MAX_EXCLUDE_BATCH = 100;

	/**
	 * Number of excluded posts allowed without a Pro license (mirrors the dashboard).
	 */
	const FREE_EXCLUDE_LIMIT = 30;

	/**
	 * Number of taxonomy terms allowed without a Pro license (mirrors the dashboard).
	 */
	const FREE_TAXONOMY_LIMIT = 3;

	/**
	 * Register the ability category.
	 *
	 * @return void
	 */
	public function register_category() {
		if ( ! function_exists( 'wp_register_ability_category' ) ) {
			return;
		}

		wp_register_ability_category(
			self::CATEGORY,
			array(
				'label'       => __( 'Revive Social', 'tweet-old-post' ),
				'description' => __( 'Abilities for managing the Revive Social sharing queue, schedules and connected accounts.', 'tweet-old-post' ),
			)
		);
	}

	/**
	 * Register the abilities.
	 *
	 * @return void
	 */
	public function register_abilities() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		foreach ( $this->get_definitions() as $name => $args ) {
			$args['category'] = self::CATEGORY;
			if ( ! isset( $args['permission_callback'] ) ) {
				$args['permission_callback'] = array( $this, 'can_manage' );
			}

			wp_register_ability( $name, $args );
		}
	}

	/**
	 * Permission check shared by the abilities.
	 *
	 * Mirrors the capability required by the `tweet-old-post/v8/api` REST route
	 * and by the plugin dashboard pages.
	 *
	 * @return bool
	 */
	public function can_manage() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Permission check for sharing a post.
	 *
	 * @param mixed $input The ability input.
	 *
	 * @return bool
	 */
	public function can_publish_share( $input = array() ) {
		if ( ! $this->can_manage() ) {
			return false;
		}

		$post_id = is_array( $input ) && isset( $input['post_id'] ) ? absint( $input['post_id'] ) : 0;
		if ( empty( $post_id ) ) {
			return true;
		}

		return current_user_can( 'edit_post', $post_id );
	}

	/**
	 * The ability definitions.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function get_definitions() {
		$account_schema = array(
			'type'       => 'object',
			'properties' => array(
				'account_id' => array( 'type' => 'string' ),
				'service'    => array( 'type' => 'string' ),
				'user'       => array( 'type' => 'string' ),
				'account'    => array( 'type' => 'string' ),
				'link'       => array( 'type' => 'string' ),
				'image'      => array( 'type' => 'string' ),
				'is_company' => array( 'type' => 'boolean' ),
				'active'     => array( 'type' => 'boolean' ),
			),
		);

		$schedule_schema = array(
			'type'       => 'object',
			'properties' => array(
				'account_id' => array( 'type' => 'string' ),
				'type'       => array(
					'type' => 'string',
					'enum' => array( 'recurring', 'fixed' ),
				),
				'interval_r' => array(
					'type'        => 'number',
					'description' => __( 'Hours between shares for a recurring schedule.', 'tweet-old-post' ),
				),
				'week_days'  => array(
					'type'  => 'array',
					'items' => array( 'type' => 'integer' ),
				),
				'times'      => array(
					'type'  => 'array',
					'items' => array( 'type' => 'string' ),
				),
			),
		);

		$filter_schema = array(
			'type'       => 'object',
			'properties' => array(
				'post_types'         => array(
					'type'  => 'array',
					'items' => array( 'type' => 'string' ),
				),
				'taxonomy_terms'     => array(
					'type'  => 'array',
					'items' => array(
						'type'       => 'object',
						'properties' => array(
							'taxonomy' => array( 'type' => 'string' ),
							'term_id'  => array( 'type' => 'integer' ),
							'name'     => array( 'type' => 'string' ),
						),
					),
				),
				'exclude_taxonomies' => array( 'type' => 'boolean' ),
				'minimum_post_age'   => array( 'type' => 'integer' ),
				'maximum_post_age'   => array( 'type' => 'integer' ),
				'more_than_once'     => array( 'type' => 'boolean' ),
				'keyword_filter'     => array( 'type' => 'string' ),
				'exclude_keywords'   => array( 'type' => 'boolean' ),
				'excluded_post_ids'  => array(
					'type'  => 'array',
					'items' => array( 'type' => 'integer' ),
				),
			),
		);

		$queue_item_schema = array(
			'type'       => 'object',
			'properties' => array(
				'account_id' => array( 'type' => 'string' ),
				'post_id'    => array( 'type' => 'integer' ),
				'service'    => array( 'type' => 'string' ),
				'time'       => array(
					'type'        => 'integer',
					'description' => __( 'Timestamp of the share, in the site timezone.', 'tweet-old-post' ),
				),
				'date'       => array( 'type' => 'string' ),
				'title'      => array( 'type' => 'string' ),
				'text'       => array( 'type' => 'string' ),
				'link'       => array( 'type' => 'string' ),
				'media'      => array( 'type' => 'string' ),
			),
		);

		return array(
			'revive/list-connected-accounts' => array(
				'label'            => __( 'List connected accounts', 'tweet-old-post' ),
				'description'      => __( 'Lists the social accounts connected to Revive Social. Credentials are never returned.', 'tweet-old-post' ),
				'input_schema'     => array(
					'type'                 => 'object',
					'default'              => array(),
					'properties'           => array(
						'active_only' => array(
							'type'        => 'boolean',
							'description' => __( 'Return only the accounts that are active for sharing.', 'tweet-old-post' ),
							'default'     => true,
						),
						'service'     => array(
							'type'        => 'string',
							'description' => __( 'Limit the list to one service, e.g. twitter or facebook.', 'tweet-old-post' ),
						),
					),
					'additionalProperties' => false,
				),
				'output_schema'    => array(
					'type'       => 'object',
					'properties' => array(
						'accounts' => array(
							'type'  => 'array',
							'items' => $account_schema,
						),
						'total'    => array( 'type' => 'integer' ),
					),
				),
				'execute_callback' => array( $this, 'list_connected_accounts' ),
				'meta'             => $this->meta( true, false, true ),
			),
			'revive/get-schedule'            => array(
				'label'            => __( 'Get sharing schedule', 'tweet-old-post' ),
				'description'      => __( 'Returns the sharing schedule of each active account, the site timezone, the sharing status and the current content filter.', 'tweet-old-post' ),
				'input_schema'     => array(
					'type'                 => 'object',
					'default'              => array(),
					'properties'           => array(
						'account_id' => array(
							'type'        => 'string',
							'description' => __( 'Limit the schedules to one account.', 'tweet-old-post' ),
						),
					),
					'additionalProperties' => false,
				),
				'output_schema'    => array(
					'type'       => 'object',
					'properties' => array(
						'timezone'                   => array( 'type' => 'string' ),
						'current_time'               => array( 'type' => 'integer' ),
						'sharing_active'             => array( 'type' => 'boolean' ),
						'next_event_on'              => array( 'type' => 'integer' ),
						'default_interval'           => array( 'type' => 'number' ),
						'custom_schedules_available' => array( 'type' => 'boolean' ),
						'schedules'                  => array(
							'type'  => 'array',
							'items' => $schedule_schema,
						),
						'content_filter'             => $filter_schema,
					),
				),
				'execute_callback' => array( $this, 'get_schedule' ),
				'meta'             => $this->meta( true, false, true ),
			),
			'revive/update-schedule'         => array(
				'label'            => __( 'Update sharing schedule', 'tweet-old-post' ),
				'description'      => __( 'Changes the sharing schedule of one active account. Custom schedules require a Revive Social Pro business plan.', 'tweet-old-post' ),
				'input_schema'     => array(
					'type'                 => 'object',
					'properties'           => array(
						'account_id' => array( 'type' => 'string' ),
						'type'       => array(
							'type' => 'string',
							'enum' => array( 'recurring', 'fixed' ),
						),
						'interval_r' => array(
							'type'        => 'number',
							'description' => __( 'Hours between shares, used by the recurring type.', 'tweet-old-post' ),
						),
						'week_days'  => array(
							'type'        => 'array',
							'description' => __( 'Days of the week (1 = Monday … 7 = Sunday), used by the fixed type.', 'tweet-old-post' ),
							'items'       => array(
								'type'    => 'integer',
								'minimum' => 1,
								'maximum' => 7,
							),
						),
						'times'      => array(
							'type'        => 'array',
							'description' => __( 'Times of the day in HH:MM format (site timezone), used by the fixed type.', 'tweet-old-post' ),
							'items'       => array( 'type' => 'string' ),
						),
					),
					'required'             => array( 'account_id' ),
					'additionalProperties' => false,
				),
				'output_schema'    => $schedule_schema,
				'execute_callback' => array( $this, 'update_schedule' ),
				'meta'             => $this->meta( false, false, true ),
			),
			'revive/list-queue'              => array(
				'label'            => __( 'List sharing queue', 'tweet-old-post' ),
				'description'      => __( 'Lists the upcoming shares with the content that will be posted to each account. The queue is empty while sharing is stopped.', 'tweet-old-post' ),
				'input_schema'     => array(
					'type'                 => 'object',
					'default'              => array(),
					'properties'           => array(
						'account_id' => array(
							'type'        => 'string',
							'description' => __( 'Limit the queue to one account.', 'tweet-old-post' ),
						),
						'limit'      => array(
							'type'    => 'integer',
							'minimum' => 1,
							'maximum' => self::MAX_QUEUE_ITEMS,
							'default' => 20,
						),
					),
					'additionalProperties' => false,
				),
				'output_schema'    => array(
					'type'       => 'object',
					'properties' => array(
						'sharing_active' => array( 'type' => 'boolean' ),
						'total'          => array( 'type' => 'integer' ),
						'items'          => array(
							'type'  => 'array',
							'items' => $queue_item_schema,
						),
					),
				),
				'execute_callback' => array( $this, 'list_queue' ),
				'meta'             => $this->meta( true, false, true ),
			),
			'revive/update-queue-item'       => array(
				'label'            => __( 'Update queue item', 'tweet-old-post' ),
				'description'      => __( 'Edits the text or image of a queued share, or skips it. Requires a Revive Social Pro business plan.', 'tweet-old-post' ),
				'input_schema'     => array(
					'type'                 => 'object',
					'properties'           => array(
						'account_id'   => array( 'type' => 'string' ),
						'post_id'      => array( 'type' => 'integer' ),
						'action'       => array(
							'type'    => 'string',
							'enum'    => array( 'update', 'skip' ),
							'default' => 'update',
						),
						'content'      => array(
							'type'        => 'string',
							'description' => __( 'Custom share text for this post and account.', 'tweet-old-post' ),
						),
						'media_id'     => array(
							'type'        => 'integer',
							'description' => __( 'Attachment ID of the image to share.', 'tweet-old-post' ),
						),
						'remove_media' => array(
							'type'        => 'boolean',
							'description' => __( 'Remove the custom image and fall back to the default one.', 'tweet-old-post' ),
						),
					),
					'required'             => array( 'account_id', 'post_id' ),
					'additionalProperties' => false,
				),
				'output_schema'    => array(
					'type'       => 'object',
					'properties' => array(
						'skipped' => array( 'type' => 'boolean' ),
						'item'    => $queue_item_schema,
					),
				),
				'execute_callback' => array( $this, 'update_queue_item' ),
				'meta'             => $this->meta( false, false, true ),
			),
			'revive/set-content-filter'      => array(
				'label'            => __( 'Set content filter', 'tweet-old-post' ),
				'description'      => __( 'Configures which content is eligible for sharing. Only the provided fields change. Saving clears the current queue, as it does in the dashboard.', 'tweet-old-post' ),
				'input_schema'     => array(
					'type'                 => 'object',
					'properties'           => array(
						'post_types'          => array(
							'type'        => 'array',
							'description' => __( 'Post type slugs to share. Requires Pro.', 'tweet-old-post' ),
							'items'       => array( 'type' => 'string' ),
						),
						'taxonomy_terms'      => array(
							'type'        => 'array',
							'description' => __( 'Terms used to filter the posts. An empty list removes the filter.', 'tweet-old-post' ),
							'items'       => array(
								'type'       => 'object',
								'properties' => array(
									'taxonomy' => array( 'type' => 'string' ),
									'term_id'  => array( 'type' => 'integer' ),
								),
								'required'   => array( 'taxonomy', 'term_id' ),
							),
						),
						'exclude_taxonomies'  => array(
							'type'        => 'boolean',
							'description' => __( 'True to exclude the posts having the terms, false to share only those posts.', 'tweet-old-post' ),
						),
						'minimum_post_age'    => array(
							'type'        => 'integer',
							'description' => __( 'Minimum post age, in days.', 'tweet-old-post' ),
							'minimum'     => 0,
						),
						'maximum_post_age'    => array(
							'type'        => 'integer',
							'description' => __( 'Maximum post age, in days. Requires Pro.', 'tweet-old-post' ),
							'minimum'     => 0,
						),
						'more_than_once'      => array(
							'type'        => 'boolean',
							'description' => __( 'Share posts again once all of them were shared.', 'tweet-old-post' ),
						),
						'keyword_filter'      => array(
							'type'        => 'string',
							'description' => __( 'Comma separated keywords matched against the post title.', 'tweet-old-post' ),
						),
						'exclude_keywords'    => array(
							'type'        => 'boolean',
							'description' => __( 'True to exclude the posts matching the keywords, false to share only those posts.', 'tweet-old-post' ),
						),
						'exclude_post_ids'    => array(
							'type'        => 'array',
							'description' => __( 'Post IDs to add to the excluded posts list.', 'tweet-old-post' ),
							'maxItems'    => self::MAX_EXCLUDE_BATCH,
							'items'       => array( 'type' => 'integer' ),
						),
						'unexclude_post_ids'  => array(
							'type'        => 'array',
							'description' => __( 'Post IDs to remove from the excluded posts list.', 'tweet-old-post' ),
							'maxItems'    => self::MAX_EXCLUDE_BATCH,
							'items'       => array( 'type' => 'integer' ),
						),
					),
					'additionalProperties' => false,
				),
				'output_schema'    => $filter_schema,
				'execute_callback' => array( $this, 'set_content_filter' ),
				'meta'             => $this->meta( false, false, true ),
			),
			'revive/publish-share'           => array(
				'label'               => __( 'Publish share', 'tweet-old-post' ),
				'description'         => __( 'Shares one published post to one active account using the instant sharing flow. The share is handed to the sharing cron, so the result is "queued". With dry_run it only returns the account specific preview and posts nothing.', 'tweet-old-post' ),
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(
						'post_id'    => array( 'type' => 'integer' ),
						'account_id' => array( 'type' => 'string' ),
						'message'    => array(
							'type'        => 'string',
							'description' => __( 'Optional custom share message that replaces the generated text.', 'tweet-old-post' ),
						),
						'dry_run'    => array(
							'type'    => 'boolean',
							'default' => false,
						),
					),
					'required'             => array( 'post_id', 'account_id' ),
					'additionalProperties' => false,
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'dry_run'    => array( 'type' => 'boolean' ),
						'status'     => array( 'type' => 'string' ),
						'post_id'    => array( 'type' => 'integer' ),
						'account_id' => array( 'type' => 'string' ),
						'service'    => array( 'type' => 'string' ),
						'preview'    => array(
							'type'       => 'object',
							'properties' => array(
								'text'     => array( 'type' => 'string' ),
								'link'     => array( 'type' => 'string' ),
								'media'    => array( 'type' => 'string' ),
								'hashtags' => array( 'type' => 'string' ),
							),
						),
					),
				),
				'execute_callback'    => array( $this, 'publish_share' ),
				'permission_callback' => array( $this, 'can_publish_share' ),
				'meta'                => $this->meta( false, true, false ),
			),
		);
	}

	/**
	 * Build the ability meta.
	 *
	 * @param bool $readonly    Whether the ability only reads data.
	 * @param bool $destructive Whether the ability is destructive.
	 * @param bool $idempotent  Whether the ability is idempotent.
	 *
	 * @return array<string, mixed>
	 */
	private function meta( $readonly, $destructive, $idempotent ) {
		return array(
			'annotations'  => array(
				'readonly'    => $readonly,
				'destructive' => $destructive,
				'idempotent'  => $idempotent,
			),
			'show_in_rest' => true,
		);
	}

	/**
	 * Whether the custom schedule and queue editing features are available.
	 *
	 * Mirrors the dashboard check (`license > 1 && license !== 7`) and makes sure
	 * the Pro API class used by the REST handlers is loaded.
	 *
	 * @return bool
	 */
	private function has_business_features() {
		$global_settings = new Rop_Global_Settings();
		$license         = $global_settings->license_type();

		return class_exists( 'Rop_Pro_Api' ) && $license > 1 && 7 !== $license;
	}

	/**
	 * Get the Pro API, which the REST handlers also use for the schedule and queue changes.
	 *
	 * The class ships with the Pro plugin, callers must check has_business_features() first.
	 *
	 * @return mixed
	 */
	private function get_pro_api() {
		return new Rop_Pro_Api(); // @phpstan-ignore class.notFound
	}

	/**
	 * Whether a Pro license is active.
	 *
	 * @return bool
	 */
	private function is_pro() {
		$global_settings = new Rop_Global_Settings();

		return $global_settings->license_type() >= 1;
	}

	/**
	 * Whether the plugin was first installed at the given version or later.
	 *
	 * Same check as Rop_Admin::limit_tax_dropdown_list() and Rop_Admin::limit_exclude_list(),
	 * which decide if the free limits apply to this site.
	 *
	 * @param string $version The version that introduced the limit.
	 *
	 * @return bool
	 */
	private function installed_since( $version ) {
		$installed_at_version = get_option( 'rop_first_install_version' );

		return ! empty( $installed_at_version ) && version_compare( $installed_at_version, $version, '>=' );
	}

	/**
	 * Error returned for the features that need a higher plan.
	 *
	 * @param string $message The error message.
	 *
	 * @return WP_Error
	 */
	private function upgrade_error( $message ) {
		return new WP_Error( 'rop_pro_required', $message, array( 'status' => 403 ) );
	}

	/**
	 * Get the accounts that are available, keyed by account ID.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function get_available_accounts() {
		$model    = new Rop_Services_Model();
		$active   = $model->get_active_accounts();
		$accounts = array();

		foreach ( $model->get_authenticated_services() as $service ) {
			if ( empty( $service['available_accounts'] ) || ! is_array( $service['available_accounts'] ) ) {
				continue;
			}

			foreach ( $service['available_accounts'] as $account_id => $account ) {
				if ( ! is_array( $account ) ) {
					continue;
				}

				$account_id = (string) $account_id;

				// Whitelist the fields, the stored accounts can hold access tokens.
				$accounts[ $account_id ] = array(
					'account_id' => $account_id,
					'service'    => isset( $account['service'] ) ? (string) $account['service'] : '',
					'user'       => isset( $account['user'] ) ? (string) $account['user'] : '',
					'account'    => isset( $account['account'] ) ? (string) $account['account'] : '',
					'link'       => isset( $account['link'] ) ? (string) $account['link'] : '',
					'image'      => isset( $account['img'] ) ? (string) $account['img'] : '',
					'is_company' => ! empty( $account['is_company'] ),
					'active'     => isset( $active[ $account_id ] ),
				);
			}
		}

		return $accounts;
	}

	/**
	 * Validate that an account is active and return it.
	 *
	 * @param string $account_id The account ID.
	 *
	 * @return array<string, mixed>|WP_Error
	 */
	private function get_active_account( $account_id ) {
		$accounts = $this->get_available_accounts();

		if ( empty( $account_id ) || ! isset( $accounts[ $account_id ] ) ) {
			return new WP_Error( 'rop_account_not_found', __( 'The account was not found.', 'tweet-old-post' ), array( 'status' => 404 ) );
		}

		if ( ! $accounts[ $account_id ]['active'] ) {
			return new WP_Error( 'rop_account_inactive', __( 'The account is not active for sharing.', 'tweet-old-post' ), array( 'status' => 400 ) );
		}

		return $accounts[ $account_id ];
	}

	/**
	 * Execute: revive/list-connected-accounts.
	 *
	 * @param mixed $input The ability input.
	 *
	 * @return array<string, mixed>
	 */
	public function list_connected_accounts( $input = array() ) {
		$input       = is_array( $input ) ? $input : array();
		$active_only = isset( $input['active_only'] ) ? (bool) $input['active_only'] : true;
		$service     = isset( $input['service'] ) ? sanitize_key( $input['service'] ) : '';
		$accounts    = array();

		foreach ( $this->get_available_accounts() as $account ) {
			if ( $active_only && ! $account['active'] ) {
				continue;
			}
			if ( ! empty( $service ) && $service !== $account['service'] ) {
				continue;
			}
			$accounts[] = $account;
		}

		return array(
			'accounts' => $accounts,
			'total'    => count( $accounts ),
		);
	}

	/**
	 * Format a stored schedule for output.
	 *
	 * @param string               $account_id The account ID.
	 * @param array<string, mixed> $schedule   The stored schedule.
	 *
	 * @return array<string, mixed>
	 */
	private function format_schedule( $account_id, $schedule ) {
		$week_days = isset( $schedule['interval_f']['week_days'] ) && is_array( $schedule['interval_f']['week_days'] ) ? $schedule['interval_f']['week_days'] : array();
		$times     = isset( $schedule['interval_f']['time'] ) && is_array( $schedule['interval_f']['time'] ) ? $schedule['interval_f']['time'] : array();

		return array(
			'account_id' => (string) $account_id,
			'type'       => isset( $schedule['type'] ) ? (string) $schedule['type'] : 'recurring',
			'interval_r' => isset( $schedule['interval_r'] ) ? (float) $schedule['interval_r'] : 0.0,
			'week_days'  => array_values( array_map( 'intval', $week_days ) ),
			'times'      => array_values( array_map( 'strval', $times ) ),
		);
	}

	/**
	 * Get the current content filter.
	 *
	 * @return array<string, mixed>
	 */
	private function get_content_filter() {
		$settings_model = new Rop_Settings_Model();
		$settings       = $settings_model->get_settings();
		$terms          = array();

		$selected_taxonomies = isset( $settings['selected_taxonomies'] ) && is_array( $settings['selected_taxonomies'] ) ? $settings['selected_taxonomies'] : array();
		foreach ( $selected_taxonomies as $term ) {
			if ( ! is_array( $term ) || empty( $term['tax'] ) || empty( $term['value'] ) ) {
				continue;
			}
			$terms[] = array(
				'taxonomy' => (string) $term['tax'],
				'term_id'  => (int) $term['value'],
				'name'     => isset( $term['name'] ) ? (string) $term['name'] : '',
			);
		}

		$selected_posts = isset( $settings['selected_posts'] ) && is_array( $settings['selected_posts'] ) ? $settings['selected_posts'] : array();
		$excluded       = isset( $selected_posts[0]['value'] ) ? wp_list_pluck( $selected_posts, 'value' ) : $selected_posts;

		return array(
			'post_types'         => array_values( array_map( 'strval', wp_list_pluck( $settings_model->get_selected_post_types(), 'value' ) ) ),
			'taxonomy_terms'     => $terms,
			'exclude_taxonomies' => filter_var( $settings_model->get_exclude_taxonomies(), FILTER_VALIDATE_BOOLEAN ),
			'minimum_post_age'   => (int) $settings_model->get_minimum_post_age(),
			'maximum_post_age'   => (int) $settings_model->get_maximum_post_age(),
			'more_than_once'     => filter_var( $settings_model->get_more_than_once(), FILTER_VALIDATE_BOOLEAN ),
			'keyword_filter'     => isset( $settings['keyword_filter'] ) ? (string) $settings['keyword_filter'] : '',
			'exclude_keywords'   => filter_var( $settings_model->get_exclude_keywords(), FILTER_VALIDATE_BOOLEAN ),
			'excluded_post_ids'  => array_values( array_map( 'intval', $excluded ) ),
		);
	}

	/**
	 * Execute: revive/get-schedule.
	 *
	 * @param mixed $input The ability input.
	 *
	 * @return array<string, mixed>|WP_Error
	 */
	public function get_schedule( $input = array() ) {
		$input      = is_array( $input ) ? $input : array();
		$account_id = isset( $input['account_id'] ) ? sanitize_text_field( $input['account_id'] ) : '';

		$scheduler = new Rop_Scheduler_Model();
		$stored    = $scheduler->get_schedule();
		$stored    = is_array( $stored ) ? $stored : array();
		$schedules = array();

		if ( ! empty( $account_id ) && ! isset( $stored[ $account_id ] ) ) {
			return new WP_Error( 'rop_account_not_found', __( 'The account was not found or is not active.', 'tweet-old-post' ), array( 'status' => 404 ) );
		}

		foreach ( $stored as $id => $schedule ) {
			if ( ! empty( $account_id ) && $account_id !== (string) $id ) {
				continue;
			}
			$schedules[] = $this->format_schedule( $id, $schedule );
		}

		$cron_helper    = new Rop_Cron_Helper();
		$settings_model = new Rop_Settings_Model();

		return array(
			'timezone'                   => wp_timezone_string(),
			'current_time'               => (int) Rop_Scheduler_Model::get_current_time(),
			'sharing_active'             => (bool) $cron_helper->get_status(),
			'next_event_on'              => (int) $cron_helper->next_event(),
			'default_interval'           => (float) $settings_model->get_interval(),
			'custom_schedules_available' => $this->has_business_features(),
			'schedules'                  => $schedules,
			'content_filter'             => $this->get_content_filter(),
		);
	}

	/**
	 * Execute: revive/update-schedule.
	 *
	 * @param mixed $input The ability input.
	 *
	 * @return array<string, mixed>|WP_Error
	 */
	public function update_schedule( $input = array() ) {
		$input = is_array( $input ) ? $input : array();

		if ( ! $this->has_business_features() ) {
			return $this->upgrade_error( __( 'Custom schedules require an active Revive Social Pro business plan.', 'tweet-old-post' ) );
		}

		$account_id = isset( $input['account_id'] ) ? sanitize_text_field( $input['account_id'] ) : '';
		$account    = $this->get_active_account( $account_id );
		if ( is_wp_error( $account ) ) {
			return $account;
		}

		$scheduler = new Rop_Scheduler_Model();
		$current   = $scheduler->get_schedule( $account_id );
		$data      = array(
			'type'       => isset( $current['type'] ) ? $current['type'] : 'recurring',
			'interval_r' => isset( $current['interval_r'] ) ? $current['interval_r'] : 2.5,
			'interval_f' => array(
				'week_days' => isset( $current['interval_f']['week_days'] ) ? $current['interval_f']['week_days'] : array(),
				'time'      => isset( $current['interval_f']['time'] ) ? $current['interval_f']['time'] : array(),
			),
		);

		if ( isset( $input['type'] ) ) {
			if ( ! in_array( $input['type'], array( 'recurring', 'fixed' ), true ) ) {
				return new WP_Error( 'rop_invalid_schedule_type', __( 'The schedule type must be "recurring" or "fixed".', 'tweet-old-post' ), array( 'status' => 400 ) );
			}
			$data['type'] = $input['type'];
		}

		if ( isset( $input['interval_r'] ) ) {
			if ( ! is_numeric( $input['interval_r'] ) || (float) $input['interval_r'] <= 0 ) {
				return new WP_Error( 'rop_invalid_interval', __( 'The recurring interval must be a positive number of hours.', 'tweet-old-post' ), array( 'status' => 400 ) );
			}
			$data['interval_r'] = (float) $input['interval_r'];
		}

		if ( isset( $input['week_days'] ) ) {
			if ( ! is_array( $input['week_days'] ) ) {
				return new WP_Error( 'rop_invalid_week_days', __( 'The week days must be a list of numbers between 1 and 7.', 'tweet-old-post' ), array( 'status' => 400 ) );
			}
			$week_days = array();
			foreach ( $input['week_days'] as $day ) {
				$day = (int) $day;
				if ( $day < 1 || $day > 7 ) {
					return new WP_Error( 'rop_invalid_week_days', __( 'The week days must be a list of numbers between 1 and 7.', 'tweet-old-post' ), array( 'status' => 400 ) );
				}
				$week_days[] = (string) $day;
			}
			$data['interval_f']['week_days'] = array_values( array_unique( $week_days ) );
		}

		if ( isset( $input['times'] ) ) {
			if ( ! is_array( $input['times'] ) ) {
				return new WP_Error( 'rop_invalid_times', __( 'The times must be a list of HH:MM values.', 'tweet-old-post' ), array( 'status' => 400 ) );
			}
			$times = array();
			foreach ( $input['times'] as $time ) {
				if ( ! is_string( $time ) || ! preg_match( '/^([01]?\d|2[0-3]):([0-5]\d)$/', $time ) ) {
					return new WP_Error( 'rop_invalid_times', __( 'The times must be a list of HH:MM values.', 'tweet-old-post' ), array( 'status' => 400 ) );
				}
				$times[] = $time;
			}
			$data['interval_f']['time'] = array_values( array_unique( $times ) );
		}

		if ( 'fixed' === $data['type'] && ( empty( $data['interval_f']['week_days'] ) || empty( $data['interval_f']['time'] ) ) ) {
			return new WP_Error( 'rop_incomplete_schedule', __( 'A fixed schedule needs at least one week day and one time.', 'tweet-old-post' ), array( 'status' => 400 ) );
		}

		$this->ping_remote_cron();

		$pro_api = $this->get_pro_api();
		$pro_api->save_schedule(
			array(
				'account_id' => $account_id,
				'data'       => $data,
			)
		);

		$scheduler = new Rop_Scheduler_Model();

		return $this->format_schedule( $account_id, $scheduler->get_schedule( $account_id ) );
	}

	/**
	 * Inform the remote cron service that the share times changed.
	 *
	 * Same request the `save_schedule` REST handler sends.
	 *
	 * @return void
	 */
	private function ping_remote_cron() {
		$cron_status = filter_var( get_option( 'rop_is_sharing_cron_active', 'no' ), FILTER_VALIDATE_BOOLEAN );

		if ( true !== $cron_status || ! defined( 'ROP_CRON_ALTERNATIVE' ) ) {
			return;
		}

		if ( true !== ROP_CRON_ALTERNATIVE ) { // @phpstan-ignore notIdentical.alwaysFalse
			return;
		}

		if ( ! defined( 'ROP_CRON_DOMAIN' ) || ! class_exists( 'RopCronSystem\ROP_Helpers\Rop_Helpers' ) ) {
			return;
		}

		$server_url    = ROP_CRON_DOMAIN . '/wp-json/update-cron-ping/v1/update-time-to-share/';
		$time_to_share = array(
			'next_ping' => current_time( 'mysql' ), // phpcs:ignore
		);

		RopCronSystem\ROP_Helpers\Rop_Helpers::custom_curl_post_request( $server_url, $time_to_share );
	}

	/**
	 * Format an item of the ordered queue for output.
	 *
	 * @param array<string, mixed> $event The queue event, as returned by Rop_Queue_Model::get_ordered_queue().
	 *
	 * @return array<string, mixed>
	 */
	private function format_queue_item( $event ) {
		$data    = isset( $event['post_data'] ) && is_array( $event['post_data'] ) ? $event['post_data'] : array();
		$content = isset( $data['content'] ) && is_array( $data['content'] ) ? $data['content'] : array();

		// Whitelist the fields, the formatted object also carries the shortener credentials.
		return array(
			'account_id' => isset( $data['account_id'] ) ? (string) $data['account_id'] : '',
			'post_id'    => isset( $data['post_id'] ) ? (int) $data['post_id'] : 0,
			'service'    => isset( $content['service'] ) ? (string) $content['service'] : '',
			'time'       => isset( $data['time'] ) ? (int) $data['time'] : 0,
			'date'       => isset( $data['date'] ) ? (string) $data['date'] : '',
			'title'      => isset( $content['title'] ) ? (string) $content['title'] : '',
			'text'       => isset( $content['content'] ) ? (string) $content['content'] : '',
			'link'       => isset( $content['post_url'] ) ? (string) $content['post_url'] : '',
			'media'      => isset( $content['post_image'] ) ? (string) $content['post_image'] : '',
		);
	}

	/**
	 * Execute: revive/list-queue.
	 *
	 * @param mixed $input The ability input.
	 *
	 * @return array<string, mixed>
	 */
	public function list_queue( $input = array() ) {
		$input      = is_array( $input ) ? $input : array();
		$account_id = isset( $input['account_id'] ) ? sanitize_text_field( $input['account_id'] ) : '';
		$limit      = isset( $input['limit'] ) ? absint( $input['limit'] ) : 20;
		$limit      = max( 1, min( self::MAX_QUEUE_ITEMS, $limit ) );

		$cron_helper = new Rop_Cron_Helper();
		$queue       = new Rop_Queue_Model();
		$items       = array();

		foreach ( $queue->get_ordered_queue() as $event ) {
			$item = $this->format_queue_item( $event );
			if ( ! empty( $account_id ) && $account_id !== $item['account_id'] ) {
				continue;
			}
			$items[] = $item;
		}

		return array(
			'sharing_active' => (bool) $cron_helper->get_status(),
			'total'          => count( $items ),
			'items'          => array_slice( $items, 0, $limit ),
		);
	}

	/**
	 * Find a queued share.
	 *
	 * @param Rop_Queue_Model $queue      The queue model.
	 * @param string          $account_id The account ID.
	 * @param int             $post_id    The post ID.
	 *
	 * @return array<string, mixed>|null
	 */
	private function find_queue_item( $queue, $account_id, $post_id ) {
		foreach ( $queue->get_ordered_queue() as $event ) {
			$item = $this->format_queue_item( $event );
			if ( $account_id === $item['account_id'] && $post_id === $item['post_id'] ) {
				return $item;
			}
		}

		return null;
	}

	/**
	 * Execute: revive/update-queue-item.
	 *
	 * @param mixed $input The ability input.
	 *
	 * @return array<string, mixed>|WP_Error
	 */
	public function update_queue_item( $input = array() ) {
		$input = is_array( $input ) ? $input : array();

		if ( ! $this->has_business_features() ) {
			return $this->upgrade_error( __( 'Editing the sharing queue requires an active Revive Social Pro business plan.', 'tweet-old-post' ) );
		}

		$account_id = isset( $input['account_id'] ) ? sanitize_text_field( $input['account_id'] ) : '';
		$post_id    = isset( $input['post_id'] ) ? absint( $input['post_id'] ) : 0;
		$action     = isset( $input['action'] ) ? $input['action'] : 'update';

		if ( ! in_array( $action, array( 'update', 'skip' ), true ) ) {
			return new WP_Error( 'rop_invalid_action', __( 'The action must be "update" or "skip".', 'tweet-old-post' ), array( 'status' => 400 ) );
		}

		$queue = new Rop_Queue_Model();
		if ( empty( $account_id ) || empty( $post_id ) || null === $this->find_queue_item( $queue, $account_id, $post_id ) ) {
			return new WP_Error( 'rop_queue_item_not_found', __( 'The share was not found in the queue.', 'tweet-old-post' ), array( 'status' => 404 ) );
		}

		$pro_api = $this->get_pro_api();

		if ( 'skip' === $action ) {
			$response = $pro_api->skip_queue_event(
				array(
					'account_id' => $account_id,
					'post_id'    => $post_id,
				)
			);

			if ( ! isset( $response['code'] ) || '201' !== (string) $response['code'] ) {
				return new WP_Error( 'rop_queue_skip_failed', __( 'The share could not be skipped.', 'tweet-old-post' ), array( 'status' => 500 ) );
			}

			return array( 'skipped' => true );
		}

		$custom_data = array();

		if ( isset( $input['content'] ) ) {
			$custom_data['text'] = sanitize_textarea_field( $input['content'] );
		}

		if ( ! empty( $input['remove_media'] ) ) {
			$custom_data['image'] = null;
		} elseif ( ! empty( $input['media_id'] ) ) {
			$media_id = absint( $input['media_id'] );
			$url      = 'attachment' === get_post_type( $media_id ) ? wp_get_attachment_url( $media_id ) : false;
			if ( empty( $url ) ) {
				return new WP_Error( 'rop_media_not_found', __( 'The attachment was not found.', 'tweet-old-post' ), array( 'status' => 404 ) );
			}
			$custom_data['image'] = $url;
		}

		if ( empty( $custom_data ) ) {
			return new WP_Error( 'rop_nothing_to_update', __( 'Provide content, media_id or remove_media to update the share.', 'tweet-old-post' ), array( 'status' => 400 ) );
		}

		$response = $pro_api->update_queue_event(
			array(
				'account_id'  => $account_id,
				'post_id'     => $post_id,
				'custom_data' => $custom_data,
			)
		);

		if ( ! isset( $response['code'] ) || '201' !== (string) $response['code'] ) {
			return new WP_Error( 'rop_queue_update_failed', __( 'The share could not be updated.', 'tweet-old-post' ), array( 'status' => 500 ) );
		}

		$item = $this->find_queue_item( new Rop_Queue_Model(), $account_id, $post_id );

		return array(
			'skipped' => false,
			'item'    => null === $item ? array() : $item,
		);
	}

	/**
	 * Execute: revive/set-content-filter.
	 *
	 * @param mixed $input The ability input.
	 *
	 * @return array<string, mixed>|WP_Error
	 */
	public function set_content_filter( $input = array() ) {
		$input          = is_array( $input ) ? $input : array();
		$settings_model = new Rop_Settings_Model();
		$is_pro         = $this->is_pro();
		$data           = array();

		// Post types.
		$post_types = wp_list_pluck( $settings_model->get_selected_post_types(), 'value' );
		if ( isset( $input['post_types'] ) ) {
			if ( ! $is_pro ) {
				return $this->upgrade_error( __( 'Changing the shared post types requires Revive Social Pro.', 'tweet-old-post' ) );
			}
			if ( ! is_array( $input['post_types'] ) || empty( $input['post_types'] ) ) {
				return new WP_Error( 'rop_invalid_post_types', __( 'Provide at least one post type.', 'tweet-old-post' ), array( 'status' => 400 ) );
			}

			$available = array();
			foreach ( $settings_model->get_available_post_types() as $post_type ) {
				$available[ $post_type['value'] ] = $post_type;
			}

			$selected = array();
			foreach ( array_unique( array_map( 'sanitize_key', $input['post_types'] ) ) as $slug ) {
				if ( ! isset( $available[ $slug ] ) ) {
					/* translators: %s: post type slug. */
					return new WP_Error( 'rop_invalid_post_types', sprintf( __( 'The post type "%s" is not available for sharing.', 'tweet-old-post' ), $slug ), array( 'status' => 400 ) );
				}
				$selected[] = array(
					'name'     => $available[ $slug ]['name'],
					'value'    => $slug,
					'selected' => true,
				);
			}

			$data['selected_post_types'] = $selected;
			$post_types                  = wp_list_pluck( $selected, 'value' );
		}

		// Taxonomy terms.
		if ( isset( $input['taxonomy_terms'] ) ) {
			if ( ! is_array( $input['taxonomy_terms'] ) ) {
				return new WP_Error( 'rop_invalid_taxonomy_terms', __( 'The taxonomy terms must be a list.', 'tweet-old-post' ), array( 'status' => 400 ) );
			}
			if ( ! $is_pro && $this->installed_since( '8.5.3' ) && count( $input['taxonomy_terms'] ) > self::FREE_TAXONOMY_LIMIT ) {
				return $this->upgrade_error(
					/* translators: %d: number of terms. */
					sprintf( __( 'Selecting more than %d taxonomy terms requires Revive Social Pro.', 'tweet-old-post' ), self::FREE_TAXONOMY_LIMIT )
				);
			}

			$available = array();
			foreach ( $settings_model->get_available_taxonomies( $post_types ) as $term ) {
				$available[ $term['tax'] . ':' . $term['value'] ] = $term;
			}

			$selected = array();
			foreach ( $input['taxonomy_terms'] as $term ) {
				$key = is_array( $term ) && isset( $term['taxonomy'], $term['term_id'] ) ? sanitize_key( $term['taxonomy'] ) . ':' . absint( $term['term_id'] ) : '';
				if ( empty( $key ) || ! isset( $available[ $key ] ) ) {
					return new WP_Error( 'rop_invalid_taxonomy_terms', __( 'One of the taxonomy terms does not exist for the shared post types.', 'tweet-old-post' ), array( 'status' => 400 ) );
				}
				$selected[ $key ] = $available[ $key ];
			}

			$data['selected_taxonomies'] = array_values( $selected );
		}

		if ( isset( $input['exclude_taxonomies'] ) ) {
			$data['exclude_taxonomies'] = (bool) $input['exclude_taxonomies'];
		}

		if ( isset( $input['minimum_post_age'] ) ) {
			$data['minimum_post_age'] = absint( $input['minimum_post_age'] );
		}

		if ( isset( $input['maximum_post_age'] ) ) {
			if ( ! $is_pro ) {
				return $this->upgrade_error( __( 'Changing the maximum post age requires Revive Social Pro.', 'tweet-old-post' ) );
			}
			$data['maximum_post_age'] = absint( $input['maximum_post_age'] );
		}

		if ( isset( $input['more_than_once'] ) ) {
			$data['more_than_once'] = (bool) $input['more_than_once'];
		}

		if ( isset( $input['keyword_filter'] ) ) {
			$data['keyword_filter'] = sanitize_text_field( $input['keyword_filter'] );
		}

		if ( isset( $input['exclude_keywords'] ) ) {
			$data['exclude_keywords'] = (bool) $input['exclude_keywords'];
		}

		// Excluded posts.
		$exclude   = $this->sanitize_post_ids( isset( $input['exclude_post_ids'] ) ? $input['exclude_post_ids'] : array() );
		$unexclude = $this->sanitize_post_ids( isset( $input['unexclude_post_ids'] ) ? $input['unexclude_post_ids'] : array() );

		if ( is_wp_error( $exclude ) ) {
			return $exclude;
		}
		if ( is_wp_error( $unexclude ) ) {
			return $unexclude;
		}

		foreach ( $exclude as $post_id ) {
			if ( empty( get_post_status( $post_id ) ) ) {
				/* translators: %d: post ID. */
				return new WP_Error( 'rop_post_not_found', sprintf( __( 'The post %d does not exist.', 'tweet-old-post' ), $post_id ), array( 'status' => 404 ) );
			}
		}

		if ( ! empty( $exclude ) && ! $is_pro && $this->installed_since( '8.5.4' ) ) {
			$current = $this->get_content_filter();
			$future  = array_diff( array_unique( array_merge( $current['excluded_post_ids'], $exclude ) ), $unexclude );
			if ( count( $future ) > self::FREE_EXCLUDE_LIMIT ) {
				return $this->upgrade_error(
					/* translators: %d: number of posts. */
					sprintf( __( 'Excluding more than %d posts requires Revive Social Pro.', 'tweet-old-post' ), self::FREE_EXCLUDE_LIMIT )
				);
			}
		}

		if ( empty( $data ) && empty( $exclude ) && empty( $unexclude ) ) {
			return new WP_Error( 'rop_nothing_to_update', __( 'Provide at least one filter field to change.', 'tweet-old-post' ), array( 'status' => 400 ) );
		}

		if ( ! empty( $data ) ) {
			// The settings validation resets the post types when they are missing from the payload.
			if ( ! isset( $data['selected_post_types'] ) ) {
				$data['selected_post_types'] = $settings_model->get_selected_post_types();
			}
			$settings_model->save_settings( $data );
		}

		foreach ( $unexclude as $post_id ) {
			$settings_model->remove_excluded_posts( $post_id );
		}

		if ( ! empty( $exclude ) ) {
			$settings_model->add_excluded_posts( $exclude );
		}

		return $this->get_content_filter();
	}

	/**
	 * Sanitize a list of post IDs.
	 *
	 * @param mixed $ids The list of IDs.
	 *
	 * @return int[]|WP_Error
	 */
	private function sanitize_post_ids( $ids ) {
		if ( ! is_array( $ids ) ) {
			return new WP_Error( 'rop_invalid_post_ids', __( 'The post IDs must be a list of numbers.', 'tweet-old-post' ), array( 'status' => 400 ) );
		}

		$ids = array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );

		if ( count( $ids ) > self::MAX_EXCLUDE_BATCH ) {
			return new WP_Error(
				'rop_batch_too_large',
				/* translators: %d: number of posts. */
				sprintf( __( 'No more than %d post IDs can be changed in one call.', 'tweet-old-post' ), self::MAX_EXCLUDE_BATCH ),
				array( 'status' => 400 )
			);
		}

		return $ids;
	}

	/**
	 * Execute: revive/publish-share.
	 *
	 * @param mixed $input The ability input.
	 *
	 * @return array<string, mixed>|WP_Error
	 */
	public function publish_share( $input = array() ) {
		$input      = is_array( $input ) ? $input : array();
		$post_id    = isset( $input['post_id'] ) ? absint( $input['post_id'] ) : 0;
		$account_id = isset( $input['account_id'] ) ? sanitize_text_field( $input['account_id'] ) : '';
		$message    = isset( $input['message'] ) ? sanitize_textarea_field( $input['message'] ) : '';
		$dry_run    = ! empty( $input['dry_run'] );

		if ( empty( $post_id ) || empty( get_post_status( $post_id ) ) ) {
			return new WP_Error( 'rop_post_not_found', __( 'The post was not found.', 'tweet-old-post' ), array( 'status' => 404 ) );
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return new WP_Error( 'rop_forbidden', __( 'You are not allowed to share this post.', 'tweet-old-post' ), array( 'status' => 403 ) );
		}

		if ( 'publish' !== get_post_status( $post_id ) ) {
			return new WP_Error( 'rop_post_not_published', __( 'Only published posts can be shared.', 'tweet-old-post' ), array( 'status' => 400 ) );
		}

		$account = $this->get_active_account( $account_id );
		if ( is_wp_error( $account ) ) {
			return $account;
		}

		$result = array(
			'dry_run'    => $dry_run,
			'post_id'    => $post_id,
			'account_id' => $account_id,
			'service'    => $account['service'],
		);

		if ( $dry_run ) {
			$queue   = new Rop_Queue_Model();
			$preview = $queue->prepare_post_object( $post_id, $account_id );

			$result['status']  = 'preview';
			$result['preview'] = array(
				'text'     => ! empty( $message ) ? $message : ( isset( $preview['content'] ) ? (string) $preview['content'] : '' ),
				'link'     => isset( $preview['post_url'] ) ? (string) $preview['post_url'] : '',
				'media'    => isset( $preview['post_image'] ) ? (string) $preview['post_image'] : '',
				'hashtags' => isset( $preview['hashtags'] ) && is_string( $preview['hashtags'] ) ? $preview['hashtags'] : '',
			);

			return $result;
		}

		// Same flow as the `tweet-old-post/v8/share/<id>` REST route.
		update_post_meta( $post_id, 'rop_publish_now', 'yes' );
		update_post_meta( $post_id, 'rop_publish_now_accounts', array( $account_id => $message ) );

		do_action( 'rop_publish_now_instant_share', $post_id, true );

		if ( 'queued' !== get_post_meta( $post_id, 'rop_publish_now_status', true ) ) {
			return new WP_Error( 'rop_share_not_queued', __( 'The share could not be queued.', 'tweet-old-post' ), array( 'status' => 500 ) );
		}

		$result['status'] = 'queued';

		return $result;
	}
}
