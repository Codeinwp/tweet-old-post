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
		add_filter( 'rop_raw_post_url', [ $this, 'alter_attachment_url' ], 10, 2 );
	}

	/**
	 * Alter attachment post url and send attached post urls.
	 *
	 * @param string $post_url Original post url.
	 * @param int    $post_id Post id.
	 *
	 * @return string New post url.
	 */
	public function alter_attachment_url( $post_url, $post_id ) {
		$post_types = $this->settings->get_selected_post_types();
		$post_types = wp_list_pluck( $post_types, 'value' );
		if ( ! in_array( 'attachment', $post_types ) ) {
			return $post_url;
		}
		if ( get_post_type( $post_id ) !== 'attachment' ) {
			return $post_url;
		}

		$post_parent_id = wp_get_post_parent_id( $post_id );

		return get_permalink( $post_parent_id );
	}

	/**
	 * Method to retrieve taxonomies.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   array $post_formats The post formats to use.
	 *
	 * @return array|bool
	 */
	public function get_taxonomies( $post_formats = array() ) {

		if ( empty( $post_formats ) ) {
			return array();
		}
		$taxonomies = array();
		foreach ( $post_formats as $post_type_name ) {

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
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   array  $selected_post_types The selected post types.
	 * @param   array  $taxonomies The selected taxonomies.
	 * @param   string $search A search query.
	 * @param   bool   $exclude The exclude taxonomies flag.
	 *
	 * @return array
	 */
	public function get_posts( $selected_post_types, $taxonomies, $search = '', $exclude, $show_excluded_posts = false, $page = 1 ) {
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
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   array $selected_post_types [optional] Pass post_type data to use instead of settings.
	 *
	 * @return array
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
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   array $custom_data [optional] Pass an associative array with taxonomies and exclude options to use.
	 *
	 * @return array
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
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   bool|string $account_id The account ID to filter by. Default false, don't filter by account.
	 *
	 * @return mixed
	 */
	public function select( $account_id = false ) {
		$post_types       = $this->build_post_types();
		$tax_queries      = $this->build_tax_query();
		$excluded_by_user = $this->get_excluded_posts();
		$results          = $this->query_results( $account_id, $post_types, $tax_queries, $excluded_by_user );
		/**
		 * If share more than once is active, we have no more posts and the buffer is filled
		 * reset the buffer and query again.
		 */
		if ( empty( $results ) && $this->has_buffer_items( $account_id ) && $this->settings->get_more_than_once() ) {
			$this->clear_buffer( $account_id );

			$results = $this->query_results( $account_id, $post_types, $tax_queries, $excluded_by_user );

		}

		$this->selection = $results;

		return $results;
	}

	/**
	 * Utility method to query the DB for posts.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   string $account_id The account ID.
	 * @param   array  $post_types The post types array.
	 * @param   array  $tax_queries The taxonomies query array.
	 * @param   array  $excluded_by_user Excluded post ID's by the user.
	 *
	 * @return mixed
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

		/**
		 * Reset indexes to avoid missing ones.
		 */
		$posts = array_values( $posts );

		wp_reset_postdata();

		return $posts;
	}

	/**
	 * Utility method to build an exclusion list.
	 *
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   string $account_id The account ID.
	 * @param   array  $excluded_by_user Excluded post ID's by the user.
	 *
	 * @uses $blocked buffer ( banned posts ).
	 * @uses $buffer ( skipped or already shared posts ).
	 *
	 * @return array|mixed
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
	 * @since   8.0.0
	 * @access  private
	 *
	 * @param   array $post_types The post types array.
	 * @param   array $tax_queries The taxonomies query array.
	 * @param   array $exclude The excluded posts array.
	 *
	 * @return array
	 */
	private function build_query_args( $post_types, $tax_queries, $exclude ) {
		$admin = new Rop_Admin();
		$args = array(
			'no_found_rows'          => true,
			'posts_per_page'         => ( 1000 + count( $exclude ) ),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'post_status'            => [ 'publish' ],
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
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $account_id The account ID for witch to check.
	 *
	 * @return bool
	 */
	public function has_buffer_items( $account_id ) {
		$this->buffer = wp_parse_args( $this->get( 'posts_buffer' ), $this->buffer );

		return ( isset( $this->buffer[ $account_id ] ) ) ? true : false;
	}

	/**
	 * Method to clear buffer.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   bool|string $account_id The account ID to clear buffer filter. Default false, clear all.
	 */
	public function clear_buffer( $account_id = false ) {
		if ( isset( $account_id ) && $account_id ) {
			unset( $this->buffer[ $account_id ] );
		} else {
			$this->buffer = array();
		}
		$this->set( 'posts_buffer', $this->buffer );
	}


	/**
	 * Utility method to mark a post ID as blocked.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   string $account_id The account ID.
	 * @param   int    $post_id The post ID.
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
	 * Method to update the buffer.
	 *
	 * @since   8.0.0
	 * @acess   public
	 *
	 * @param   string $account_id The account ID.
	 * @param   int    $post_id The post ID.
	 */
	public function update_buffer( $account_id, $post_id ) {
		if ( ! isset( $this->buffer[ $account_id ] ) ) {
			$this->buffer[ $account_id ] = array();
		}
		if ( ! in_array( $post_id, $this->buffer[ $account_id ] ) ) {
			array_push( $this->buffer[ $account_id ], $post_id );
		}

		$this->set( 'posts_buffer', $this->buffer );
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
				// delete the meta so that when the post loads again after publishing, the checkboxes are cleared.
				delete_post_meta( $query->post, 'rop_publish_now' );
			}
		}

		return $posts;
	}
}
