<?php
class Rop_Db_Upgrade {
	public function __construct() {

	}


	/**
	 * Method to check if upgrade is required.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return bool
	 */
	public function is_upgrade_required() {
		return true;
	}


	/**
	 * Method to do the required upgrade.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return bool
	 */
	public function do_upgrade() {

		$this->migrate_accounts();

		$this->migrate_settings();

	}

	public function migrate_settings() {
		$general_settings = new Rop_Settings_Model();
		$setting = $general_settings->get_settings();

		if( get_option( 'top_opt_age_limit', null ) !== null ) {
			$setting['minimum_post_age'] = (int) get_option( 'top_opt_age_limit' );
		}

		if( get_option( 'top_opt_max_age_limit', null ) !== null ) {
			$setting['maximum_post_age'] = (int) get_option( 'top_opt_max_age_limit' );
		}

		if( get_option( 'top_opt_no_of_tweet', null ) !== null ) {
			$setting['number_of_posts'] = (int) get_option( 'top_opt_no_of_tweet' );
		}

		if( get_option( 'top_opt_tweet_multiple_times', null ) !== null ) {
			$setting['more_than_once'] = ( get_option( 'top_opt_tweet_multiple_times' ) === 'on' ) ? true : false;
		}

		if( get_option( 'top_opt_post_type', null ) !== null ) {
			$args = array( 'exclude_from_search' => false );
			$post_types = get_post_types( $args, 'objects' );
			$post_types_array = array();
			foreach ( $post_types as $type ) {
				if ( ! in_array( $type->name, array( 'attachment' ) ) ) {
					array_push( $post_types_array, array( 'name' => $type->label, 'value' => $type->name, 'selected' => true ) );
				}
			}

			$migrated_post_types = array();
			foreach ( get_option( 'top_opt_post_type' ) as $post_type ) {
				$key = array_search( $post_type, array_column( $post_types_array, 'value' ) );
				$post_types_array[$key]['selected'] = true;
				array_push( $migrated_post_types, $post_types_array[$key] );
			}

			$setting['selected_post_types'] = $migrated_post_types;
		}

		if( get_option( 'top_opt_omit_cats', null ) !== null ) {
			$migrated_taxonomies = array();
			foreach ( get_option( 'top_opt_post_type' ) as $post_type_name ) {
				$post_type_taxonomies = get_object_taxonomies( $post_type_name, 'objects' );
				foreach ( $post_type_taxonomies as $post_type_taxonomy ) {
					$taxonomy = get_taxonomy( $post_type_taxonomy->name );
					$terms = get_terms( $post_type_taxonomy->name );
					if ( ! empty( $terms ) ) {
						foreach ( $terms as $term ) {
							foreach ( get_option( 'top_opt_omit_cats' ) as $old_taxonomy ) {
								$tax_object = get_term_by( 'term_taxonomy_id', $old_taxonomy );
								if ( $tax_object->name === $term->name ) {
									$to_push =  array( 'name' => $taxonomy->label . ': ' . $term->name, 'value' => $taxonomy->name . '_' . $term->slug, 'selected' => true, 'parent' => $taxonomy->name . '_all' );
									if ( ! in_array( $to_push, $migrated_taxonomies ) ) {
										array_push( $migrated_taxonomies, $to_push );
									}
								}
							}
						}
					}
				}
			}

			$setting['selected_taxonomies'] = $migrated_taxonomies;
		}

		if( get_option( 'top_opt_cat_filter', null ) !== null ) {
			$setting['exclude_taxonomies'] = ( get_option( 'top_opt_cat_filter' ) === 'include' ) ? false : true ;
		}

		$general_settings->save_settings( $setting );

	}

	public function migrate_accounts() {

		$previous_logged_users = get_option('cwp_top_logged_in_users');

		if( $previous_logged_users ) {

			$model = new Rop_Services_Model();

			// TODO Remove this 2 lines after finishing testing
			$model->reset_active_accounts();
			$model->reset_authenticated_services();
			// TODO End


			$service = array();
			$active_accounts = array();
			foreach ( $previous_logged_users as $user ) {
				switch( $user['service'] ) {
					case 'twitter':
						$index =  $user['service'] . '_' .  $user['user_id'];
						$service[$index] = array(
							'id' =>  $user['user_id'],
							'service' => $user['service'],
							'credentials' => array(
								'oauth_token' => $user['oauth_token'],
								'oauth_token_secret' => $user['oauth_token_secret']
							),
							'public_credentials' => false,
							'available_accounts' => array(
								array(
									'id' => $user['user_id'],
									'name' => $user['oauth_user_details']->name,
									'account' => '@' . $user['oauth_user_details']->screen_name,
									'img' => $user['oauth_user_details']->profile_image_url_https,
									'active' => true
								)
							)
						);


						$index_account = $index . '_' . $user['user_id'];
						$active_accounts[$index_account] = array(
							'service' => $user['service'],
							'user' => $user['oauth_user_details']->name,
							'img' => $user['oauth_user_details']->profile_image_url_https,
							'account' => '@' . $user['oauth_user_details']->screen_name,
							'created' => date( 'd/m/Y H:i' ),
						);
						break;
					case 'facebook':
						$facebook_service = new Rop_Facebook_Service();
						$app_id = get_option('cwp_top_app_id');
						$secret = get_option('cwp_top_app_secret');
						$token = get_option('top_fb_token');

						if ( $facebook_service->re_authenticate( $app_id, $secret, $token ) ) {
							$index =  $user['service'] . '_' .  $facebook_service->user['id'];
							$service[$index] = $facebook_service->get_service();

							$img = $user['oauth_user_details']->profile_image_url;
							$key = array_search( $user['user_id'], array_column( $service[$index]['available_accounts'], 'id' ) );
							if( $key ) {
								$img = $service[$index]['available_accounts'][$key]['img'];
							}

							$index_account = $index . '_' . $user['user_id'];
							$active_accounts[$index_account] = array(
								'service' => $user['service'],
								'user' => $user['oauth_user_details']->name,
								'img' => $img,
								'account' => $facebook_service->user['email'],
								'created' => date( 'd/m/Y H:i' ),
							);
						}
						break;
					case 'linkedin':
						$linkedin_service = new Rop_Linkedin_Service();
						$app_id = get_option('cwp_top_lk_app_id');
						$secret = get_option('cwp_top_lk_app_secret');
						$token = get_option('top_linkedin_token');

						if ( $linkedin_service->re_authenticate( $app_id, $secret, $token ) ) {
							$index =  $user['service'] . '_' .  $user['user_id'];
							$service[$index] = $linkedin_service->get_service();

							$index_account = $index . '_' . $user['user_id'];
							$active_accounts[$index_account] = array(
								'service' => $user['service'],
								'user' => $linkedin_service->user['formattedName'],
								'img' => $linkedin_service->user['pictureUrl'],
								'account' => $linkedin_service->user['formattedName'],
								'created' => date( 'd/m/Y H:i' ),
							);
						}

						break;
					case 'tumblr':
						$tumblr_service = new Rop_Tumblr_Service();
						$consumer_key = get_option('cwp_top_consumer_key_tumblr');
						$consumer_secret = get_option('cwp_top_consumer_secret_tumblr');
						$oauth_token = $user['oauth_token'];
						$oauth_token_secret = $user['oauth_token_secret'];

						if ( $tumblr_service->re_authenticate( $consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret ) ) {
							$api = $tumblr_service->get_api();
							try {
								$info = $api->getBlogInfo( get_option("cwp_top_consumer_url_tumblr") );
							} catch ( Exception $exception ) {

							}
							$id = $user['user_id'];
							if( isset( $info->response->blog->name ) ) {
								$id = $info->response->blog->name;
							}

							$index =  $user['service'] . '_' .  $id;
							$service[$index] = $tumblr_service->get_service();

							$key = array_search( $id, array_column( $service[$index]['available_accounts'], 'id' ) );
							$name = $user['oauth_user_details']->name;
							$account = $id;
							$img = $user['oauth_user_details']->profile_image_url;
							if( $key ) {
								$name = $service[$index]['available_accounts'][$key]['name'];
								$account = $service[$index]['available_accounts'][$key]['account'];
								$img = $service[$index]['available_accounts'][$key]['img'];
							}

							$index_account = $index . '_' . $id;
							$active_accounts[$index_account] = array(
								'service' => $user['service'],
								'user' => $name,
								'img' => $img,
								'account' => $account,
								'created' => date( 'd/m/Y H:i' ),
							);
						}

						break;
				}
			}
			$model->add_authenticated_service( $service );
			$model->add_active_accounts( $active_accounts );
		}

	}

	/**
	 * Method to do a revert for an upgrade action.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return bool
	 */
	public function revert() {

	}
}