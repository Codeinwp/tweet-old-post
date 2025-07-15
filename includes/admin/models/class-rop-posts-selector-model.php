<?php
/**
 * The model for the post selection of the plugin.
 *
 * @link       https://themeisle.com
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/admin/models
 */

/**
 * Class Rop_Posts_Selector_Model
 */
class Rop_Posts_Selector_Model extends Rop_Model_Abstract {

	/**
	 * Holds the buffer which filters the results.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $buffer The buffer to filter the results by.
	 */
	private $buffer = array();

	/**
	 * Holds the block post ID's.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $blocked The blocked post ID's to filter the results by.
	 */
	private $blocked = array();

	/**
	 * Stores the active selection.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $selection The active selection.
	 */
	private $selection = array();

	/**
	 * Stores the Rop_Settings_Model instance.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array|Rop_Settings_Model $settings The model instance.
	 */
	private $settings = array();

	/**
	 * Rop_Posts_Selector_Model constructor.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function __construct() {
		parent::__construct();
		$this->settings = new Rop_Settings_Model();
		$this->buffer   = wp_parse_args( $this->get( 'posts_buffer' ), $this->buffer );
		$this->blocked  = wp_parse_args( $this->get( 'posts_blocked' ), $this->blocked );
	}

	/**
	 * Method to retrieve taxonomies.
	 *
	 * @param array $data Contains an array of post types to get the taxonomies for. Can also contain a language code passed by the langauge selector option on the Post Format settings for an account.
	 *
	 * @return array|bool
	 * @since   8.0.0
	 * @access  public
	 */
	public function get_taxonomies( $data = array() ) {

		$post_types = array();

		if ( empty( $data['language_code'] ) ) {
			$post_types = $data;
		} else {
			$post_types = $data['post_types'];
			$language_code = $data['language_code'];
		}

		if ( empty( $post_types ) ) {
			return array();
		}

		$taxonomies = array();

		$wpml_current_lang = $this->get_current_language();

		if ( ( function_exists( 'icl_object_id' ) || class_exists( 'TRP_Translate_Press' ) ) && ! empty( $language_code ) ) {
			// changes the language of global query to use the specfied language
			$this->switch_language( $language_code );
		}

		// Here We are refreshing the taxonomies "on page load"
		// This method fires whenever the post format page is brought into view.
		// We're refreshing the taxonomies based on whether that first account has a language assigned or not
		if ( ( function_exists( 'icl_object_id' ) || class_exists( 'TRP_Translate_Press' ) ) && empty( $language_code ) ) {
			// check the first active account and it's post format and see if it has a language code.
			$first_account_id = array_keys( $this->data['active_accounts'] )[0];
			$post_format_model = new Rop_Post_Format_Model;
			$post_format = $post_format_model->get_post_format( $first_account_id );
			$first_account_lang = ! empty( $post_format['wpml_language'] ) ? $post_format['wpml_language'] : '';

			if ( ! empty( $first_account_lang ) ) {
				// changes the language of global query to use the specfied language
				$this->switch_language( $first_account_lang );
			}
		}

		foreach ( $post_types as $post_type_name ) {

			$post_type_taxonomies = get_object_taxonomies( $post_type_name, 'objects' );

			$post_type_taxonomies = $this->ignore_taxonomies( $post_type_taxonomies );

			foreach ( $post_type_taxonomies as $post_type_taxonomy ) {

				$taxonomy = get_taxonomy( $post_type_taxonomy->name );

				if ( empty( $taxonomy ) ) {
					continue;
				}

				$terms = get_terms( $post_type_taxonomy->name );
				if ( empty( $terms ) ) {
					continue;
				}

				$tax_name = $taxonomy->labels->singular_name;

				foreach ( $terms as $term ) {
					/*
					$translated_term_id = apply_filters( 'wpml_object_id', $term->term_id, $taxonomy->name, FALSE, $lang );
					$args = array('element_id' => $translated_term_id, 'element_type' => $taxonomy->name );
					$lang_details = apply_filters( 'wpml_element_language_details', null, $args );
					$translated_name =  apply_filters( 'wpml_translated_language_name', NULL, $lang_details->language_code, $lang_details->language_code );
					*/

					array_push(
						$taxonomies,
						array(
							'name'     => $tax_name . ': ' . $term->name,
							'value'    => $term->term_id,
							'tax'      => $taxonomy->name,
							'selected' => false,
						)
					);
				}
			}
		}

		if ( ( function_exists( 'icl_object_id' ) || class_exists( 'TRP_Translate_Press' ) ) && ! ( empty( $language_code ) && empty( $first_account_lang ) ) ) {
			// set language back to original
			$this->switch_language( $wpml_current_lang );
		}

		if ( empty( $taxonomies ) ) {
			return array();
		}

		return $taxonomies;
	}

	/**
	 * Utility method to ignore certain taxonomies.
	 *
	 * @param array $taxes Taxonomies to filter.
	 *
	 * @return array Filtered taxonomy list.
	 */
	public function ignore_taxonomies( $taxes ) {
		if ( isset( $taxes['post_format'] ) ) {
			unset( $taxes['post_format'] );
		}

		return apply_filters( 'rop_ignore_taxonmies', $taxes );
	}

	/**
	 * Utility method to retrieve posts.
	 *
	 * @param array  $selected_post_types The selected post types.
	 * @param array  $taxonomies The selected taxonomies.
	 * @param bool   $exclude The exclude taxonomies flag.
	 * @param string $search A search query.
	 *
	 * @return array
	 * @since   8.0.0
	 * @access  public
	 */
	public function get_posts( $selected_post_types, $taxonomies, $exclude, $search = '', $show_excluded_posts = false, $page = 1 ) {
		$search = strval( $search );

		$args = array(
			'posts_per_page'         => 100,
			'update_post_meta_cache' => false,
		);
		if ( $page === false ) {
			$args['no_found_rows']  = false;
			$args['posts_per_page'] = 500;
		} else {
			$args['paged'] = $page;
		}
		if ( ! empty( $search ) ) {
			$args['s'] = $search;
		}
		$excluded = $this->get_excluded_posts();
		/**
		 * Return empty if the excluded list is empty and we want to show excluded posts.
		 */
		if ( empty( $excluded ) && $show_excluded_posts ) {
			return array();
		}
		if ( $show_excluded_posts && ! empty( $excluded ) ) {
			$args['post__in'] = $excluded;
		} else {
			$post_types        = $this->build_post_types( $selected_post_types );
			$tax_queries       = $this->build_tax_query( array( 'taxonomies' => $taxonomies, 'exclude' => $exclude ) );
			$args['post_type'] = $post_types;
			$args['tax_query'] = $tax_queries;
		}
		$posts_array     = new WP_Query( $args );
		$formatted_posts = array();

		foreach ( $posts_array->posts as $post ) {
			array_push(
				$formatted_posts,
				array(
					'name'     => $post->post_title,
					'value'    => $post->ID,
					'selected' => $show_excluded_posts ? true : in_array( $post->ID, $excluded ),
				)
			);
		}
		wp_reset_postdata();

		return $formatted_posts;
	}

	/**
	 * Get excluded posts ids.
	 *
	 * @return array Excluded posts ids.
	 */
	private function get_excluded_posts() {
		$excluded_posts = $this->settings->get_selected_posts();
		if ( empty( $excluded_posts ) ) {
			return array();
		}
		if ( ! isset( $excluded_posts[0]['value'] ) ) {
			return $excluded_posts;
		}

		return wp_list_pluck( $excluded_posts, 'value' );
	}

	/**
	 * Utility method to build the post types from settings.
	 *
	 * @param array $selected_post_types [optional] Pass post_type data to use instead of settings.
	 *
	 * @return array
	 * @since   8.0.0
	 * @access  private
	 */
	private function build_post_types( $selected_post_types = array() ) {
		$post_types = array();

		$post_type_to_use = $this->settings->get_selected_post_types();
		if ( ! empty( $selected_post_types ) ) {
			$post_type_to_use = $selected_post_types;
		}

		foreach ( $post_type_to_use as $post_type ) {
			array_push( $post_types, $post_type['value'] );
		}

		return $post_types;
	}

	/**
	 * Utility method to build the taxonomies query.
	 *
	 * @param array $custom_data [optional] Pass an associative array with taxonomies and exclude options to use.
	 *
	 * @return array
	 * @since   8.0.0
	 * @access  private
	 */
	private function build_tax_query( $custom_data = array() ) {

		$exclude    = $this->settings->get_exclude_taxonomies();
		$taxonomies = $this->settings->get_selected_taxonomies();

		if ( ! empty( $custom_data ) && isset( $custom_data['taxonomies'] ) && isset( $custom_data['exclude'] ) ) {
			$exclude    = $custom_data['exclude'];
			$taxonomies = $custom_data['taxonomies'];
		}
		$operator = ( $exclude === true ) ? 'NOT IN' : 'IN';

		$tax_queries = array( 'relation' => $exclude ? 'AND' : 'OR' );
		if ( ! empty( $taxonomies ) ) {
			foreach ( $taxonomies as $taxonomy ) {
				$tmp_query = array();
				$term_id   = $taxonomy['value'];
				if ( empty( $term_id ) ) {
					continue;
				}
				if ( $term_id === 'all' ) {
					continue;
				}
				$tmp_query['relation'] = ( $exclude ) ? 'AND' : 'OR';
				$tmp_query['taxonomy'] = $taxonomy['tax'];

				$tmp_query['terms']            = $term_id;
				$tmp_query['include_children'] = true;
				$tmp_query['operator']         = $operator;
				array_push( $tax_queries, $tmp_query );
			}
		} else {
			$tax_queries = array();
		}

		return $tax_queries;
	}

	/**
	 * Method to retrieve the posts based on general settings and filtered by the buffer.
	 *
	 * @param bool|string $account_id The account ID to filter by. Default false, don't filter by account.
	 *
	 * @return mixed
	 * @since   8.0.0
	 * @access  public
	 *
	 * @see Rop_Queue_Model::get_queue For how this function is getting used
	 */
	public function select( $account_id = false ) {
		$post_types      = $this->build_post_types();
		$global_settings = new Rop_Global_Settings();

		// Taxonomy: Post Format new option
		if ( $global_settings->license_type() > 0 && $global_settings->license_type() !== 7 && ! empty( $account_id ) ) {
			$parts             = explode( '_', $account_id );
			$service           = $parts[0];
			$post_format_model = new Rop_Post_Format_Model( $service );
			$post_format       = $post_format_model->get_post_format( $account_id );

			if ( ( function_exists( 'icl_object_id' ) || class_exists( 'TRP_Translate_Press' ) ) && ! empty( $post_format['wpml_language'] ) ) {
				$wpml_current_lang = $this->get_current_language();
				// changes the language of global query to use the specfied language for the account.
				$this->switch_language( $post_format['wpml_language'] );
			}

			$custom_data = array();
			if ( isset( $post_format['taxonomy_filter'] ) && ! empty( $post_format['taxonomy_filter'] ) ) {

				$custom_data['taxonomies'] = $post_format['taxonomy_filter'];
				if ( isset( $post_format['exclude_taxonomies'] ) ) {
					$custom_data['exclude'] = filter_var( $post_format['exclude_taxonomies'], FILTER_VALIDATE_BOOLEAN );
				} else {
					$custom_data['exclude'] = false;
				}
			}

			$tax_queries = $this->build_tax_query( $custom_data );
		} else {
			$tax_queries = $this->build_tax_query();
		}

		$excluded_by_user = $this->get_excluded_posts();
		$results          = $this->query_results( $account_id, $post_types, $tax_queries, $excluded_by_user );

		/**
		 * If share more than once is active, we have no more posts and the buffer is filled
		 * reset the buffer and query again.
		 */
		if ( empty( $results ) && $this->has_buffer_items( $account_id ) && $this->settings->get_more_than_once() ) {

			$this->clear_buffer( $account_id );

			$results = $this->query_results( $account_id, $post_types, $tax_queries, $excluded_by_user );

		} elseif ( empty( $results ) && $this->has_buffer_items( $account_id ) && ! $this->settings->get_more_than_once() ) {

			$service  = new Rop_Services_Model;
			$log      = new Rop_Logger();
			$accounts = get_option( 'rop_one_time_share_accounts' );

			if ( ! is_array( $accounts ) ) {
				$accounts = array();
			}

			if ( in_array( $account_id, $accounts ) ) {
				return;
			}

			$admin_email = get_option( 'admin_email' );
			$subject     = Rop_I18n::get_labels( 'emails.share_once_sharing_done_subject' );
			$message     = Rop_I18n::get_labels( 'emails.share_once_sharing_done_message' );

			array_push( $accounts, $account_id );
			update_option( 'rop_one_time_share_accounts', $accounts );

			$count                 = count( array_keys( get_option( 'rop_one_time_share_accounts' ) ) );
			$active_accounts_count = count( array_keys( $service->get_active_accounts() ) );

			if ( $count === $active_accounts_count ) {
				if ( wp_mail( $admin_email, $subject, $message ) ) {
					$log->alert_error( $message );
				}
			}
		}

		// Clear entire buffer if only one post id remains in the list of available ones that can be added to queue
		// Helps in preventing the same post from being added to the queue over and over again
		$results_count = count( $results );

		if ( $results_count <= 1 && $this->settings->get_more_than_once() ) {

			$this->clear_buffer( $account_id );
			$results = $this->query_results( $account_id, $post_types, $tax_queries, $excluded_by_user );

		}

		$this->selection = $results;

		if ( ( function_exists( 'icl_object_id' ) || class_exists( 'TRP_Translate_Press' ) ) && ! empty( $post_format['wpml_language'] ) ) {
			// Sets WP language back to what user set it.
			$this->switch_language( $wpml_current_lang );
		}

		return $results;
	}

	/**
	 * Utility method to query the DB for posts.
	 *
	 * @param string $account_id The account ID.
	 * @param array  $post_types The post types array.
	 * @param array  $tax_queries The taxonomies query array.
	 * @param array  $excluded_by_user Excluded post ID's by the user.
	 *
	 * @return array
	 * @since   8.0.0
	 * @since   9.0.6 Added code to prevent posts from being scheduled if they are already in the queue when there's more than enough unique posts available to schedule.
	 * @access  private
	 */
	private function query_results( $account_id, $post_types, $tax_queries, $excluded_by_user ) {

		$exclude = $this->build_exclude( $account_id, $excluded_by_user );

		if ( ! is_array( $exclude ) ) {
			$exclude = array();
		}

		$args  = $this->build_query_args( $post_types, $tax_queries, $exclude );
		$query = new WP_Query( $args );
		// echo $query->request;
		$posts = $query->posts;

		/**
		 * Exclude the ids from the excluded array.
		 */
		$posts = array_diff( $posts, $exclude );

		$number_of_posts_to_share = ( new Rop_Settings_Model )->get_number_of_posts();
		$events_per_account = Rop_Scheduler_Model::EVENTS_PER_ACCOUNT;

		/**
		 * If the number of available posts(post pool) is greater than whats allowed per account(10),
		 * Then drop posts that are already scheduled in favor of having a posts pool comprising of posts that are not scheduled yet.
		 */
		if ( count( $posts ) > ( $number_of_posts_to_share * $events_per_account ) ) {
			$queue = get_option( 'rop_queue', array() );
			$account_queue = $queue['queue'][ $account_id ] ?? '';
			if ( ! empty( $account_queue ) ) {
				$current_account_queue = array_unique( array_merge( ...$account_queue ) );
				$posts = array_diff( $posts, $current_account_queue );
			}
		}

		/**
		 * Reset indexes to avoid missing ones.
		 */
		$posts = array_values( $posts );

		if ( function_exists( 'icl_object_id' ) || class_exists( 'TRP_Translate_Press' ) ) {
			$posts = $this->rop_wpml_id( $posts, $account_id );
		}

		if ( ! empty( $posts ) ) {
			wp_reset_postdata();
		}

		return $posts;
	}

	/**
	 * Utility method to build an exclusion list.
	 *
	 * @param string $account_id The account ID.
	 * @param array  $excluded_by_user Excluded post ID's by the user.
	 *
	 * @return array|mixed
	 * @uses $blocked buffer ( banned posts ).
	 * @uses $buffer ( skipped or already shared posts ).
	 *
	 * @since   8.0.0
	 * @access  private
	 */
	private function build_exclude( $account_id, $excluded_by_user = array() ) {
		$exclude = array();
		if ( isset( $account_id ) && $account_id ) {
			$exclude = ( isset( $this->buffer[ $account_id ] ) ) ? $this->buffer[ $account_id ] : array();
			$blocked = ( isset( $this->blocked[ $account_id ] ) ) ? $this->blocked[ $account_id ] : array();
			$exclude = array_merge( $exclude, $blocked );
		}
		$exclude = array_merge( $exclude, $excluded_by_user );
		$exclude = array_unique( $exclude );

		return $exclude;
	}

	/**
	 * Utility method to build the args array for the get post method.
	 *
	 * @param array $post_types The post types array.
	 * @param array $tax_queries The taxonomies query array.
	 * @param array $exclude The excluded posts array.
	 *
	 * @return array
	 * @since   8.0.0
	 * @access  private
	 */
	private function build_query_args( $post_types, $tax_queries, $exclude ) {

		$rop_quantity_of_posts = apply_filters( 'rop_quantity_of_posts', 1000 );

		$admin = new Rop_Admin();
		$args  = array(
			'no_found_rows'          => true,
			'posts_per_page'         => ( $rop_quantity_of_posts + count( $exclude ) ),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'post_status'            => array( 'publish' ),
			'fields'                 => 'ids',
			'post_type'              => $post_types,
			'tax_query'              => $tax_queries,
		);
		// Special arguments for attachment post type.
		if ( in_array( 'attachment', $post_types ) ) {
			$args['post_mime_type'] = $admin->rop_supported_mime_types()['all'];
			$args['post_status'][]  = 'inherit';
			$args['meta_query']     = array(
				'relation' => 'OR',
				array(
					'relation' => 'AND',
					array(
						'key'   => '_rop_media_share',
						'value' => 'on',
					),
					array(
						'key'     => '_wp_attached_file',
						'compare' => 'EXISTS',
					),
				),
				array(
					'key'     => '_wp_attached_file',
					'compare' => 'NOT EXISTS',
				),
			);
		}
		$min_age = $this->settings->get_minimum_post_age();
		if ( ! empty( $min_age ) ) {
			$args['date_query'][]['before'] = date( 'Y-m-d', strtotime( '-' . $this->settings->get_minimum_post_age() . ' days' ) );
		}
		$max_age = $this->settings->get_maximum_post_age();
		if ( ! empty( $max_age ) ) {
			$args['date_query'][]['after'] = date( 'Y-m-d', strtotime( '-' . $this->settings->get_maximum_post_age() . ' days' ) );
		}
		if ( ! empty( $args['date_query'] ) ) {
			$args['date_query']['relation'] = 'AND';
		}
		if ( empty( $tax_queries ) ) {
			unset( $args['tax_query'] );
		}

		return $args;
	}

	/**
	 * Method to determine if the buffer is empty or not.
	 *
	 * @param string $account_id The account ID for which to check.
	 *
	 * @return bool
	 * @since   8.0.0
	 * @access  public
	 */
	public function has_buffer_items( $account_id ) {
		$this->buffer = wp_parse_args( $this->get( 'posts_buffer' ), $this->buffer );
		return ( isset( $this->buffer[ $account_id ] ) ) ? true : false;
	}

	/**
	 * Get the current buffer in the Database.
	 *
	 * @since 9.0.6
	 * @return array An array of Accounts and the post IDs that have been shared.
	 */
	public function get_buffer() {
		return $this->get( 'posts_buffer' );
	}

	/**
	 * Check if a post has already been shared to an account.
	 *
	 * Checks the account buffer for a given Post ID.
	 *
	 * @since 9.0.6
	 * @param mixed $account_id The account ID to check.
	 * @param mixed $post_id The Post ID to look for.
	 * @return bool Wher or not the post ID exists in the buffer(meaning if it has already been shared).
	 */
	public function buffer_has_post_id( $account_id, $post_id ) {

		$buffer = $this->get_buffer();
		$account_posts = $buffer[ $account_id ] ?? array();

		$post_ids = array_values( $account_posts );

		if ( in_array( $post_id, $post_ids ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Method to clear buffer.
	 *
	 * @param bool|string $account_id The account ID to clear buffer filter. Default false, clear all.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function clear_buffer( $account_id = false ) {
		if ( isset( $account_id ) && $account_id ) {
			unset( $this->buffer[ $account_id ] );
		} else {
			$admin = new Rop_Admin();
			$admin->rop_clear_one_time_share_accounts();
			$this->buffer = array();
		}
		$this->set( 'posts_buffer', $this->buffer );
	}


	/**
	 * Utility method to mark a post ID as blocked.
	 *
	 * @param string $account_id The account ID.
	 * @param int    $post_id The post ID.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function mark_as_blocked( $account_id, $post_id ) {
		if ( ! isset( $this->blocked[ $account_id ] ) ) {
			$this->blocked[ $account_id ] = array();
		}
		if ( ! in_array( $post_id, $this->blocked[ $account_id ] ) ) {
			array_push( $this->blocked[ $account_id ], $post_id );
		}

		$this->set( 'posts_blocked', $this->blocked );
	}

	/**
	 * Utility method to clear blocked posts.
	 *
	 * @since   9.0.0
	 * @access  public
	 */
	public function clear_blocked_posts() {
		$this->blocked = array();
		$this->set( 'posts_blocked', $this->blocked );
	}

	/**
	 * Method to update the buffer.
	 *
	 * @param string $account_id The account ID.
	 * @param int    $post_id The post ID.
	 * @param   bool   $refresh Whether to refresh the rop_data property in parent abstract class with new rop_data option value.
	 *
	 * @since   8.0.0
	 * @acess   public
	 */
	public function update_buffer( $account_id, $post_id, $refresh = false ) {
		if ( ! isset( $this->buffer[ $account_id ] ) ) {
			$this->buffer[ $account_id ] = array();
		}
		if ( ! in_array( $post_id, $this->buffer[ $account_id ] ) ) {
			array_push( $this->buffer[ $account_id ], $post_id );
		}

		return $this->set( 'posts_buffer', $this->buffer, $refresh );
	}

	/**
	 * Get posts to be published now.
	 *
	 * @access public
	 * @return array
	 */
	public function get_publish_now_posts() {
		$settings_model = new Rop_Settings_Model();
		$post_types     = wp_list_pluck( $settings_model->get_selected_post_types(), 'value' );

		// fetch all post_types that need to be published now.
		$query = new WP_Query(
			array(
				'post_type'   => $post_types,
				'meta_query'  => array(
					array(
						'key'   => 'rop_publish_now',
						'value' => 'yes',
					),
					array( // we didn't want to add this check, it's redundant but it seems in previous version delete_post_meta was not working properly.
						'key'   => 'rop_publish_now_status',
						'value' => 'queued',
					),
				),
				'numberposts' => 300,
				'orderby'     => 'modified',
				'order'       => 'ASC',
				'fields'      => 'ids',
			)
		);

		$posts = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$posts[] = $query->post;
				// update the meta so that when the post loads again after publishing, the checkboxes are cleared.
				update_post_meta( $query->post, 'rop_publish_now', 'no' );
			}
		}

		return $posts;
	}

	/**
	 * Method to get WPML post id
	 *
	 * @param mixed $post_id The post ID.
	 *
	 * @return  mixed
	 * @since   8.1.7
	 * @access   public
	 */
	public function rop_wpml_id( $post_id, $account_id = '' ) {

		$default_lang = $this->get_default_language();
		$lang_code    = '';

		$post = $post_id;

		if ( ! empty( $account_id ) ) {

			$post_format_model = new Rop_Post_Format_Model();
			$rop_account_post_format = $post_format_model->get_post_format( $account_id );
			// If no language set, use default WPML language
			$lang_code = ! empty( $rop_account_post_format['wpml_language'] ) ? $rop_account_post_format['wpml_language'] : $default_lang;

		}

		if ( is_array( $post_id ) ) {

			$post = array();

			foreach ( $post_id as $id ) {

				$post_type = get_post_type( $id );
				$wpml_post = apply_filters( 'wpml_object_id', $id, $post_type, false, $lang_code );

				if ( ! empty( $wpml_post ) ) {

					if ( get_post_status( $wpml_post ) !== 'publish' ) {
						continue;
					}

					$post[] = $wpml_post;
				}
			}
		} else {

			$post = '';
			$post_type = get_post_type( $post_id );
			$wpml_post = apply_filters( 'wpml_object_id', $post_id, $post_type, false, $lang_code );

			if ( get_post_status( $wpml_post ) === 'publish' ) {
				$post = $wpml_post;
			}
		}

		if ( ! empty( $post ) ) {
			return $post;
		} else {
			// Return original passed in post id if none of the conditions are met.
			return $post_id;
		}

	}

	/**
	 * Method to get WPML modified URL for appropriate language
	 *
	 * @param string $url The post URL.
	 *
	 * @return  string
	 * @since   8.1.7
	 * @access   public
	 */
	public function rop_wpml_link( $url, $account_id ) {

		$default_lang = $this->get_default_language();
		$lang_code    = '';

		if ( ! empty( $account_id ) ) {

			$post_format_model = new Rop_Post_Format_Model();
			$rop_account_post_format = $post_format_model->get_post_format( $account_id );
			// If no language set, use default WPML language
			$lang_code = ! empty( $rop_account_post_format['wpml_language'] ) ? $rop_account_post_format['wpml_language'] : $default_lang;

		}

		$wpml_url = $this->converter_permalink( $url, $lang_code );

		return $wpml_url;
	}

	/**
	 * Get default language.
	 *
	 * @return string
	 */
	public function get_default_language() {
		if ( class_exists( 'TRP_Translate_Press' ) ) {
			$trp_settings     = TRP_Translate_Press::get_trp_instance()->get_component( 'settings' )->get_settings();
			$default_language = isset( $trp_settings['default-language'] ) ? $trp_settings['default-language'] : '';
			return $default_language;
		}
		return apply_filters( 'wpml_default_language', null );
	}

	/**
	 * Converter post permalink using language code.
	 *
	 * @param string $url Post URL.
	 * @param string $lang_code Language code.
	 *
	 * @return string
	 */
	public function converter_permalink( $url, $lang_code ) {
		if ( class_exists( 'TRP_Translate_Press' ) ) {
			$url_converter = TRP_Translate_Press::get_trp_instance()->get_component( 'url_converter' );
			return $url_converter->get_url_for_language( $lang_code, $url );
		}
		return apply_filters( 'wpml_permalink', $url, $lang_code );
	}

	/**
	 * Get current language code.
	 *
	 * @return string
	 */
	public function get_current_language() {
		if ( class_exists( 'TRP_Translate_Press' ) ) {
			global $TRP_LANGUAGE;
			return $TRP_LANGUAGE;
		}
		return apply_filters( 'wpml_current_language', null );
	}

	/**
	 * Language Switch.
	 *
	 * @param string $language_code Language code.
	 *
	 * @return void
	 */
	public function switch_language( $language_code ) {
		if ( class_exists( 'TRP_Translate_Press' ) ) {
			trp_switch_language( $language_code );
		} else {
			do_action( 'wpml_switch_language', $language_code );
		}
	}
}
