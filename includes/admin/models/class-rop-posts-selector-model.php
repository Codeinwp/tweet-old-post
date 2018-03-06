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
	 * @since   8.0.0
	 * @access  public
	 * @param   array $post_formats The post formats to use.
	 *
	 * @return array|bool
	 */
	public function get_taxonomies( $post_formats = array() ) {
		$taxonomies = array();
		if ( empty( $post_formats ) ) {
			return false;
		}
		foreach ( $post_formats as $post_type_name ) {
			$post_type_taxonomies = get_object_taxonomies( $post_type_name, 'objects' );
			foreach ( $post_type_taxonomies as $post_type_taxonomy ) {
				$taxonomy = get_taxonomy( $post_type_taxonomy->name );
				$terms    = get_terms( $post_type_taxonomy->name );
				if ( ! empty( $terms ) ) {
					array_push( $taxonomies, array( 'name' => $taxonomy->label, 'value' => $taxonomy->name . '_all', 'selected' => false ) );
					foreach ( $terms as $term ) {
						array_push( $taxonomies, array( 'name' => $taxonomy->label . ': ' . $term->name, 'value' => $taxonomy->name . '_' . $term->slug, 'selected' => false, 'parent' => $taxonomy->name . '_all' ) );
					}
				}
			}
		}
		if ( empty( $taxonomies ) ) {
			return false;
		}

		return $taxonomies;
	}

	/**
	 * Utility method to retrieve posts.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   array  $selected_post_types The selected post types.
	 * @param   array  $taxonomies The taxonomies.
	 * @param   string $search A search query.
	 * @param   bool   $exclude The exclude flag.
	 *
	 * @return array
	 */
	public function get_posts( $selected_post_types, $taxonomies, $search, $exclude ) {
		$search_query = '';
		if ( isset( $search ) && $search != '' ) {
			$search_query = $search;
		}
		$post_types  = $this->build_post_types( $selected_post_types );
		$tax_queries = $this->build_tax_query( array( 'taxonomies' => $taxonomies, 'exclude' => $exclude ) );

		$posts_array = get_posts(
			array(
				'posts_per_page' => 5,
				'post_type'      => $post_types,
				's'              => $search_query,
				'tax_query'      => $tax_queries,
			)
		);

		$formatted_posts = array();
		foreach ( $posts_array as $post ) {
			array_push( $formatted_posts, array( 'name' => $post->post_title, 'value' => $post->ID, 'selected' => false ) );
		}

		return $formatted_posts;
	}

	/**
	 * Utility method to build the post types from settings.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   array $selected_post_types [optional] Pass post_type data to use instead of settings.
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
	 * @param   array $custom_data [optional] Pass an associative array with taxonomies and exclude options to use.
	 * @return array
	 */
	private function build_tax_query( $custom_data = array() ) {
		$tax_queries = array( 'relation' => 'OR' );

		$exclude    = $this->settings->get_exclude_taxonomies();
		$taxonomies = $this->settings->get_selected_taxonomies();

		if ( ! empty( $custom_data ) && isset( $custom_data['taxonomies'] ) && isset( $custom_data['exclude'] ) ) {
			$exclude    = $custom_data['exclude'];
			$taxonomies = $custom_data['taxonomies'];
		}

		$operator = ( $exclude == true ) ? 'NOT IN' : 'IN';

		if ( ! empty( $taxonomies ) ) {
			foreach ( $taxonomies as $taxonomy ) {
				$tmp_query             = array();
				list( $tax, $term )    = explode( '_', $taxonomy['value'] );
				$tmp_query['relation'] = 'OR';
				$tmp_query['taxonomy'] = $tax;
				if ( isset( $term ) && $term != 'all' && $term != '' ) {
					$tmp_query['field'] = 'slug';
					$tmp_query['terms'] = $term;
				} else {
					$all_terms = get_terms( $tax );
					$terms     = array();
					foreach ( $all_terms as $custom_term ) {
						array_push( $terms, $custom_term->slug );
					}
					$tmp_query['field'] = 'slug';
					$tmp_query['terms'] = $terms;
				}
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
	 * Utility method to build the args array for the get post method.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   array $post_types The post types array.
	 * @param   array $tax_queries The taxonomies query array.
	 * @param   array $exclude The excluded posts array.
	 * @return array
	 */
	private function build_query_args( $post_types, $tax_queries, $exclude ) {
		$args = array(
			'no_found_rows' => true,
			'numberposts'   => '20',
			'post_type'     => $post_types,
			'tax_query'     => $tax_queries,
			'exclude'       => $exclude,
			'date_query'    => array(
				'relation' => 'AND',
				array(
					'before' => date( 'Y-m-d', strtotime( '-' . $this->settings->get_minimum_post_age() . ' days' ) ),
				),
				array(
					'after' => date( 'Y-m-d', strtotime( '-' . $this->settings->get_maximum_post_age() . ' days' ) ),
				),
			),
		);
		if ( empty( $tax_queries ) ) {
			unset( $args['tax_query'] );
		}
		if ( empty( $exclude ) ) {
			unset( $args['exclude'] );
		}
		return $args;
	}

	/**
	 * Utility method to build an exclusion list.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   string $account_id The account ID.
	 * @param   array  $excluded_by_user Excluded post ID's by the user.
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
		return $exclude;
	}


	/**
	 * Utility method to query the DB for posts.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   string $account_id The account ID.
	 * @param   array  $post_types The post types array.
	 * @param   array  $tax_queries The taxonomies query array.
	 * @param   array  $excluded_by_user Excluded post ID's by the user.
	 * @return mixed
	 */
	private function query_results( $account_id, $post_types, $tax_queries, $excluded_by_user ) {
		$exclude = $this->build_exclude( $account_id, $excluded_by_user );
		$args    = $this->build_query_args( $post_types, $tax_queries, $exclude );
		// print_r( $args );
		// print_r( get_posts( $args ) );
		return get_posts( $args );
	}

	/**
	 * Method to retrieve the posts based on general settings and filtered by the buffer.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   bool|string $account_id The account ID to filter by. Default false, don't filter by account.
	 * @return mixed
	 */
	public function select( $account_id = false ) {
		$post_types  = $this->build_post_types();
		$tax_queries = $this->build_tax_query();

		$include          = array();
		$excluded_by_user = array();
		$required         = array();
		if ( $this->settings->get_selected_posts() ) {
			foreach ( $this->settings->get_selected_posts() as $post ) {
				if ( $this->settings->get_exclude_posts() == true ) {
					array_push( $excluded_by_user, $post['value'] );
				} else {
					array_push( $include, $post['value'] );
				}
			}
			if ( $this->settings->get_exclude_posts() != true ) {
				$required = get_posts( array( 'numberposts' => -1, 'include' => $include, 'no_found_rows' => true ) );
			}
		}

		$results = $this->query_results( $account_id, $post_types, $tax_queries, $excluded_by_user );

		if ( empty( $results ) && $this->has_buffer_items( $account_id ) ) {
			$this->clear_buffer( $account_id );

			$results = $this->query_results( $account_id, $post_types, $tax_queries, $excluded_by_user );

		}

		$results = wp_parse_args( $results, $required );

		$this->selection = $results;
		// print_r( $results );
		return $results;
	}

	/**
	 * Utility method to mark a post ID as blocked.
	 *
	 * @since   8.0.0
	 * @access  public
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
	 * Method to determine if the buffer is empty or not.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $account_id The account ID for witch to check.
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
}
