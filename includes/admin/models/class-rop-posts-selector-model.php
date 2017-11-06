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
		$this->buffer = wp_parse_args( $this->get( 'posts_buffer' ), $this->buffer );
	}

	/**
	 * Method to retrieve the posts based on general settings and filtered by the buffer.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   bool|string $account The account id to filter by. Default false, don't filter by account.
	 * @return mixed
	 */
	public function select( $account = false ) {
		$post_types = array();
		$tax_queries = array( 'relation' => 'OR' );
		$operator = ( $this->settings->get_exclude_taxonomies() == true ) ? 'NOT IN' : 'IN';

		foreach ( $this->settings->get_selected_post_types() as $post_type ) {
			array_push( $post_types, $post_type['value'] );
		}

		if ( ! empty( $this->settings->get_selected_taxonomies() ) ) {
			foreach ( $this->settings->get_selected_taxonomies() as $taxonomy ) {
				$tmp_query = array();
				list( $tax, $term ) = explode( '_', $taxonomy['value'] );
				$tmp_query['relation'] = 'OR';
				$tmp_query['taxonomy'] = $tax;
				if ( isset( $term ) && $term != 'all' && $term != '' ) {
					$tmp_query['field'] = 'slug';
					$tmp_query['terms'] = $term;
				} else {
					$all_terms = get_terms( $tax );
					$terms = array();
					foreach ( $all_terms as $custom_term ) {
						array_push( $terms, $custom_term->slug );
					}
					$tmp_query['field'] = 'slug';
					$tmp_query['terms'] = $terms;
				}
				$tmp_query['include_children'] = true;
				$tmp_query['operator'] = $operator;
				array_push( $tax_queries, $tmp_query );
			}
		}

		$include = array();
		if ( isset( $account ) && $account ) {
			$exclude = ( isset( $this->buffer[ $account ] ) ) ? $this->buffer[ $account ] : array();
		}

		$required = array();
		if ( ! empty( $this->settings->get_selected_posts() ) ) {
			foreach ( $this->settings->get_selected_posts() as $post ) {
				if ( $this->settings->get_exclude_posts() == true ) {
					array_push( $exclude, $post['value'] );
				} else {
					array_push( $include, $post['value'] );
				}
			}
			$required = get_posts( array( 'numberposts' => -1, 'include' => $include, 'no_found_rows' => true ) );
		}

		$args = array(
			'no_found_rows' => true,
			'numberposts' => $this->settings->get_number_of_posts(),
			'post_type' => $post_types,
			'tax_query' => $tax_queries,
			'exclude' => $exclude,
			'date_query' => array(
				'relation' => 'AND',
				array(
					'before' => date( 'Y-m-d', strtotime( '-' . $this->settings->get_minimum_post_age() . ' days' ) ),
				),
				array(
					'after' => date( 'Y-m-d', strtotime( '-' . $this->settings->get_maximum_post_age() . ' days' ) ),
				)
			),
		);

		if ( empty( $this->settings->get_selected_taxonomies() ) ) {
			unset( $args['tax_query'] );
		}

		$results = get_posts( $args );

		$results = wp_parse_args( $results, $required );

		return $results;
	}

	/**
	 * Method to clear buffer.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   bool|string $account The account id to clear buffer filter. Default false, clear all.
	 */
	public function clear_buffer( $account = false ) {
		if ( isset( $account ) && $account ) {
			unset( $this->buffer[ $account ] );
		} else {
			$this->buffer = array();
		}
		$this->set( 'posts_buffer', $this->buffer );
	}
}
