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

	private $buffer = array();

	private $selection = array();

	private $settings = array();

	public function __construct() {
		parent::__construct();
		$this->settings = new Rop_Settings_Model();
		$this->buffer = wp_parse_args( $this->get( 'posts_buffer' ), $this->buffer );
	}

	public function select() {
		$post_types = array();
		$tax_queries = array( 'relation' => 'OR' );
		$operator = ( $this->settings->get_exclude_taxonomies() == true ) ? 'NOT IN' : 'IN';

		foreach ( $this->settings->get_selected_post_types() as $post_type ) {
			array_push( $post_types, $post_type['value'] );
		}

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

		$include = array();
		$exclude = $this->buffer;
		foreach ( $this->settings->get_selected_posts() as $post ) {
			if ( $this->settings->get_exclude_posts() == true ) {
				array_push( $exclude, $post['value'] );
			} else {
				array_push( $include, $post['value'] );
			}
		}

		$required = get_posts( array( 'numberposts' => -1, 'include' => $include, 'no_found_rows' => true ) );

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
					'before' => date( 'Y-m-d', strtotime( '-' . $this->settings->get_maximum_post_age() . ' days' ) ),
				)
			),
		);

		$results = get_posts( $args );

		$results = wp_parse_args( $results, $required );

		return $results;
	}
}
