<?php
/**
 * The file that defines the abstract class inherited by all shortners
 *
 * A class that is used to define the shortners class and utility methods.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/helpers
 */

/**
 * Class Rop_Db_Upgrade
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
class Rop_Db_Upgrade {
	/**
	 * Database version used for upgrading purposes.
	 *
	 * @var string $db_version Database version.
	 */
	private $db_version = '1.0.0';

	/**
	 * The database option key.
	 *
	 * @var string $db_namespace Database namespace.
	 */
	private $db_namespace = 'rop_db_version';

	/**
	 * Method to check if upgrade is required.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return bool
	 */
	public function is_upgrade_required() {
		$upgrade_check = get_option( 'cwp_top_logged_in_users', '' );

		if ( empty( $upgrade_check ) ) {
			return false;
		} else {

			$db_version = $this->get_db_version();

			if ( empty( $db_version ) ) {
				return true;
			}
			if ( version_compare( $db_version, $this->db_version ) < 0 ) {
				return true;
			}

			return false;
		}

	}

	/**
	 * Get database version.
	 *
	 * @return string Database version string.
	 */
	private function get_db_version() {
		$db_version_cache = wp_cache_get( $this->db_namespace, 'rop' );
		if ( ! empty( $db_version_cache ) ) {
			return $db_version_cache;
		}

		$db_version = get_option( $this->db_namespace, '' );

		return $db_version;
	}

	/**
	 * Method to do the required upgrade.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function do_upgrade() {
		$this->migrate_accounts();
		$this->migrate_settings();
		update_option( $this->db_namespace, $this->db_version, 'no' );
		wp_cache_delete( $this->db_namespace, 'rop' );
	}

	/**
	 * Method to upgrade the accounts.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function migrate_accounts() {

		$previous_logged_users = get_option( 'cwp_top_logged_in_users' );

		if ( $previous_logged_users ) {

			$model           = new Rop_Services_Model();
			$services        = array();
			$active_accounts = array();
			foreach ( $previous_logged_users as $user ) {
				switch ( $user['service'] ) {
					case 'twitter':
						$twitter_service = new Rop_Twitter_Service();
						$twitter_service->authenticate(
							array(
								'oauth_token'        => $user['oauth_token'],
								'oauth_token_secret' => $user['oauth_token_secret'],
							)
						);
						$services[ $twitter_service->get_service_id() ] = $twitter_service->get_service();
						$active_accounts                                = array_merge( $active_accounts, $twitter_service->get_service_active_accounts() );
						break;
					case 'facebook':
						$facebook_service = new Rop_Facebook_Service();
						$app_id           = get_option( 'cwp_top_app_id' );
						$secret           = get_option( 'cwp_top_app_secret' );
						$token            = get_option( 'top_fb_token' );
						$facebook_service->authenticate(
							array(
								'app_id' => $app_id,
								'secret' => $secret,
								'token'  => $token,
							)
						);
						$services[ $facebook_service->get_service_id() ] = $facebook_service->get_service();
						$active_accounts                                 = array_merge( $active_accounts, $facebook_service->get_service_active_accounts() );
						break;
					case 'linkedin' && defined( 'ROP_PRO_DIR_URL' ):
						$linkedin_service = new Rop_Linkedin_Service();
						$app_id           = get_option( 'cwp_top_lk_app_id' );
						$secret           = get_option( 'cwp_top_lk_app_secret' );
						$token            = get_option( 'top_linkedin_token' );
						$linkedin_service->authenticate(
							array(
								'client_id' => $app_id,
								'secret'    => $secret,
								'token'     => $token,
							)
						);

						$services[ $linkedin_service->get_service_id() ] = $linkedin_service->get_service();
						$active_accounts                                 = array_merge( $active_accounts, $linkedin_service->get_service_active_accounts() );
						break;
					case 'tumblr' && defined( 'ROP_PRO_DIR_URL' ):
						$tumblr_service     = new Rop_Tumblr_Service();
						$consumer_key       = get_option( 'cwp_top_consumer_key_tumblr' );
						$consumer_secret    = get_option( 'cwp_top_consumer_secret_tumblr' );
						$oauth_token        = $user['oauth_token'];
						$oauth_token_secret = $user['oauth_token_secret'];
						$tumblr_service->authenticate(
							array(
								'consumer_key'       => $consumer_key,
								'consumer_secret'    => $consumer_secret,
								'oauth_token'        => $oauth_token,
								'oauth_token_secret' => $oauth_token_secret,
							)
						);

						$services[ $tumblr_service->get_service_id() ] = $tumblr_service->get_service();
						$active_accounts                               = array_merge( $active_accounts, $tumblr_service->get_service_active_accounts() );
						break;
				}// End switch().
			}// End foreach().

			if ( ! empty( $services ) ) {
				$model->add_authenticated_service( $services );
			}
			if ( ! empty( $active_accounts ) ) {
				$this->migrate_schedule( $active_accounts );
				$this->migrate_post_formats( $active_accounts );
			}
		}// End if().

	}

	/**
	 * Method to migrate the schedule.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   array $active_accounts The array of accounts to use.
	 */
	public function migrate_schedule( $active_accounts ) {
		$old_schedule = get_option( 'cwp_top_global_schedule' );

		$global_settings   = new Rop_Global_Settings();
		$schedule_defaults = $global_settings::instance()->get_default_schedule();

		$scheduler_model = new Rop_Scheduler_Model();

		foreach ( $active_accounts as $account_id => $account ) {
			if ( isset( $old_schedule[ $account['service'] . '_schedule_type_selected' ] ) && isset( $old_schedule[ $account['service'] . '_top_opt_interval' ] ) ) {
				$schedule = $schedule_defaults;
				if ( $old_schedule[ $account['service'] . '_schedule_type_selected' ] == 'each' ) {
					$schedule['type']       = 'recurring';
					$schedule['interval_r'] = $old_schedule[ $account['service'] . '_top_opt_interval' ];
				} else {
					$schedule['type']                    = 'fixed';
					$schedule['interval_f']['week_days'] = explode( ',', $old_schedule[ $account['service'] . '_top_opt_interval' ]['days'] );
					$times                               = array();
					foreach ( $old_schedule[ $account['service'] . '_top_opt_interval' ]['times'] as $time ) {
						array_push( $times, $time['hour'] . ':' . $time['minute'] );
					}
					$schedule['interval_f']['time'] = $times;
				}

				$scheduler_model->add_update_schedule( $account_id, $schedule );
			}
		}
	}

	/**
	 * Method to migrate post format options.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   array $active_accounts The array of accounts to use.
	 */
	public function migrate_post_formats( $active_accounts ) {
		foreach ( $active_accounts as $account_id => $account ) {
			if ( get_option( $account['service'] . '_top_opt_tweet_type' ) !== false ) {
				$post_format_model = new Rop_Post_Format_Model( $account['service'] );
				$post_format       = $post_format_model->get_post_format( $account_id );

				$tweet_content               = get_option( $account['service'] . '_top_opt_tweet_type' );
				$tweet_content_custom_field  = get_option( $account['service'] . '_top_opt_tweet_type_custom_field' );
				$additional_text             = get_option( $account['service'] . '_top_opt_add_text' );
				$additional_text_at          = get_option( $account['service'] . '_top_opt_add_text_at' );
				$max_length                  = get_option( $account['service'] . '_top_opt_tweet_length' );
				$include_link                = get_option( $account['service'] . '_top_opt_include_link' );
				$fetch_url_from_custom_field = get_option( $account['service'] . '_top_opt_custom_url_option' );
				$custom_field_url            = get_option( $account['service'] . '_top_opt_custom_url_field' );
				$use_url_shortner            = get_option( $account['service'] . '_top_opt_use_url_shortner' );
				$url_shortner_service        = get_option( $account['service'] . '_top_opt_url_shortner' );
				$hashtags                    = get_option( $account['service'] . '_top_opt_custom_hashtag_option' );
				$common_hashtags             = get_option( $account['service'] . '_top_opt_hashtags' );
				$maximum_hashtag_length      = get_option( $account['service'] . '_top_opt_hashtag_length' );
				$hashtag_custom_field        = get_option( $account['service'] . '_top_opt_custom_hashtag_field' );
				$post_with_image             = get_option( $account['service'] . '_top_opt_post_with_image' );

				if ( $tweet_content == 'title' ) {
					$tweet_content = 'post_title';
				}
				if ( $tweet_content == 'body' ) {
					$tweet_content = 'post_content';
				}
				if ( $tweet_content == 'titlenbody' ) {
					$tweet_content = 'post_title_content';
				}
				if ( $tweet_content == 'custom-field' ) {
					$tweet_content = 'custom_field';
				}
				$post_format['post_content']      = $tweet_content;
				$post_format['custom_meta_field'] = $tweet_content_custom_field;
				$post_format['custom_text_pos']   = $additional_text_at;
				$post_format['custom_text']       = $additional_text;
				$post_format['maximum_length']    = $max_length;
				$post_format['include_link']      = ( $include_link == 'on' || $include_link == true ) ? true : false;
				$post_format['url_from_meta']     = ( $fetch_url_from_custom_field == 'on' || $fetch_url_from_custom_field == true ) ? true : false;
				$post_format['url_meta_key']      = $custom_field_url;
				$post_format['short_url']         = ( $use_url_shortner == 'on' || $use_url_shortner == true ) ? true : false;
				$post_format['short_url_service'] = $url_shortner_service;
				if ( $hashtags == 'nohashtag' ) {
					$hashtags = 'no-hashtags';
				}
				if ( $hashtags == 'common' ) {
					$hashtags = 'common-hashtags';
				}
				if ( $hashtags == 'categories' ) {
					$hashtags = 'categories-hashtags';
				}
				if ( $hashtags == 'tags' ) {
					$hashtags = 'tags-hashtags';
				}
				if ( $hashtags == 'custom' ) {
					$hashtags = 'custom-hashtags';
				}
				$post_format['hashtags']        = $hashtags;
				$post_format['hashtags_length'] = $maximum_hashtag_length;
				$post_format['hashtags_common'] = $common_hashtags;
				$post_format['hashtags_custom'] = $hashtag_custom_field;
				$post_format['image']           = ( $post_with_image == 'on' || $post_with_image == true ) ? true : false;

				$post_format_model->add_update_post_format( $account_id, $post_format );
			}// End if().
		}// End foreach().
	}

	/**
	 * Method to upgrade the general settings.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function migrate_settings() {

		// for php < 5.5
		if ( ! function_exists( 'array_column' ) ) {
			/**
			 * Unimplemented method for PHP < 5.5
			 *
			 * @param array  $input The input array.
			 * @param string $column_key A key to select.
			 * @param null   $index_key Index key.
			 *
			 * @return array
			 */
			function array_column( $input, $column_key, $index_key = null ) {
				$arr = array_map(
					function ( $d ) use ( $column_key, $index_key ) {
						if ( ! isset( $d[ $column_key ] ) ) {
							return null;
						}
						if ( $index_key !== null ) {
							return array( $d[ $index_key ] => $d[ $column_key ] );
						}

						return $d[ $column_key ];
					},
					$input
				);

				if ( $index_key !== null ) {
					$tmp = array();
					foreach ( $arr as $ar ) {
						$tmp[ key( $ar ) ] = current( $ar );
					}
					$arr = $tmp;
				}

				return $arr;
			}
		}

		$general_settings = new Rop_Settings_Model();

		$old_settings = get_option( 'top_opt_post_formats', null );

		if ( $old_settings !== null && isset( $old_settings['top_opt_interval'] ) ) {
			$setting['default_interval'] = (int) $old_settings['top_opt_interval'];
		}

		if ( $old_settings !== null && isset( $old_settings['top_opt_age_limit'] ) ) {
			$setting['minimum_post_age'] = (int) $old_settings['top_opt_age_limit'];
		}

		if ( $old_settings !== null && isset( $old_settings['top_opt_max_age_limit'] ) ) {
			$setting['maximum_post_age'] = (int) $old_settings['top_opt_max_age_limit'];
		}

		if ( $old_settings !== null && isset( $old_settings['top_opt_no_of_tweet'] ) ) {
			$setting['number_of_posts'] = (int) $old_settings['top_opt_no_of_tweet'];
		}

		if ( $old_settings !== null && isset( $old_settings['top_opt_tweet_multiple_times'] ) ) {
			$setting['more_than_once'] = ( $old_settings['top_opt_tweet_multiple_times'] === 'on' ) ? true : false;
		}

		if ( $old_settings !== null && isset( $old_settings['top_opt_ga_tracking'] ) ) {
			$setting['ga_tracking'] = ( $old_settings['top_opt_ga_tracking'] === 'on' ) ? true : false;
		}

		$top_opt_post_type = null;
		if ( $old_settings !== null && isset( $old_settings['top_opt_post_type'] ) ) {
			$top_opt_post_type = $old_settings['top_opt_post_type'];
		}
		if ( ! is_array( $top_opt_post_type ) ) {
			$top_opt_post_type = array( $top_opt_post_type );
		}
		if ( $top_opt_post_type !== null && ! empty( $top_opt_post_type ) ) {

			$args             = array( 'exclude_from_search' => false );
			$post_types       = get_post_types( $args, 'objects' );
			$post_types_array = array();
			foreach ( $post_types as $type ) {
				if ( ! in_array( $type->name, array( 'attachment' ) ) && in_array( $type->name, $top_opt_post_type ) ) {
					array_push(
						$post_types_array,
						array(
							'name'  => $type->label,
							'value' => $type->name,
						)
					);
				}
			}
			$setting['selected_post_types'] = $post_types_array;
		}
		$top_opt_omit_cats = null;
		if ( $old_settings !== null && isset( $old_settings['top_opt_omit_cats'] ) ) {
			$top_opt_omit_cats = $old_settings['top_opt_omit_cats'];
		}
		if ( ! is_array( $top_opt_omit_cats ) ) {
			$top_opt_omit_cats = array( $top_opt_omit_cats );
		}
		$top_opt_omit_cats = array_values( $top_opt_omit_cats );
		$top_opt_omit_cats = array_unique( $top_opt_omit_cats );
		if ( $top_opt_omit_cats !== null ) {
			$migrated_taxonomies = array();
			foreach ( $top_opt_omit_cats as $term_id ) {
				$term = get_term( $term_id );
				if ( ! isset( $term->taxonomy ) ) {
					continue;
				}
				$tax = get_taxonomy( $term->taxonomy );
				if ( ! isset( $tax->name ) ) {
					continue;
				}
				$tax_label = isset( $tax->labels->singular_name ) ? $tax->labels->singular_name : $tax->label;
				$to_push   = array(
					'name'     => $tax_label . ': ' . $term->name,
					'value'    => $term_id,
					'tax'      => $tax->name,
					'selected' => true,
				);
				if ( ! in_array( $to_push, $migrated_taxonomies ) ) {
					array_push( $migrated_taxonomies, $to_push );
				}
			}
			if ( ! empty( $migrated_taxonomies ) ) {
				$setting['selected_taxonomies'] = $migrated_taxonomies;
			}
		}

		if ( $old_settings !== null && isset( $old_settings['top_opt_cat_filter'] ) ) {
			$setting['exclude_taxonomies'] = ( $old_settings['top_opt_cat_filter'] === 'include' ) ? false : true;
		}

		$excluded_posts = get_option( 'top_opt_excluded_post', '' );
		$excluded_posts = explode( ',', $excluded_posts );
		if ( is_array( $excluded_posts ) && ! empty( $excluded_posts ) ) {
			$formatted_posts = array();

			$query = new WP_Query(
				array(
					'post__in'               => $excluded_posts,
					'no_found_rows'          => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
				)
			);
			foreach ( $query->posts as $post ) {
				array_push(
					$formatted_posts,
					array(
						'name'     => $post->post_title,
						'value'    => $post->ID,
						'selected' => true,
					)
				);
			}
			wp_reset_postdata();
			if ( ! empty( $formatted_posts ) ) {
				$setting['selected_posts'] = $formatted_posts;
				$setting['exclude_posts']  = true;
			}
		}
		$general_settings->save_settings( $setting );

		$is_started = get_option( 'cwp_topnew_active_status', 'no' );
		if ( $is_started === 'yes' ) {
			$cron = new Rop_Cron_Helper();
			$cron->create_cron( true );
		}
	}

}
