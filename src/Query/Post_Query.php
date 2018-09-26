<?php
namespace Awethemes\WP_Object\Query;

use Awethemes\WP_Object\Utils\Utils;

class Post_Query extends Query {
	/**
	 * The query vars.
	 *
	 * @var \Awethemes\WP_Object\Query\Query_Vars
	 */
	protected $query_vars;

	/**
	 * Constructor.
	 *
	 * @param array|Query_Vars $main_query The main query vars.
	 */
	public function __construct( $main_query = [] ) {
		$this->query_vars = $main_query instanceof Query_Vars
			? $main_query
			: new Query_Vars( $main_query );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_by_id( $id ) {
		$post = get_post( Utils::parse_object_id( $id ), ARRAY_A );

		if ( ! $post ) {
			return null;
		}

		if ( isset( $this->query_vars['post_type'] ) &&
			! in_array( get_post_type( $post ), (array) $this->query_vars['post_type'] ) ) {
			return null;
		}

		return $post;
	}

	/**
	 * Execute the query to retrieves items.
	 *
	 * @param  array $query_vars The query vars.
	 * @return \WP_Query
	 */
	public function do_query( $query_vars ) {
		return new \WP_Query( $query_vars );
	}

	/**
	 * Get the query vars.
	 *
	 * @return array
	 */
	public function get_query_vars() {
		return $this->query_vars->to_array();
	}

	/**
	 * Extract items from the query.
	 *
	 * @param  \WP_Query $the_query The WP_Query instance.
	 * @return array
	 */
	public function extract_items( $the_query ) {
		return $the_query->posts;
	}

	/**
	 * {@inheritdoc
	 *
	 * TODO: ...
	 */
	public function apply_query( $name, ...$vars ) {
		switch ( $name ) {
			case 'select':
				// $this->query_vars[]
				break;
			case 'limit':
				$this->query_vars['posts_per_page'] = $vars[0];
				break;
			case 'offset':
				$this->query_vars['offset'] = $vars[0];
				break;
			case 'orderby':
				list( $order, $orderby )     = $vars;
				$this->query_vars['order']   = $order;
				$this->query_vars['orderby'] = $orderby;
				break;
		}
	}

	public function doing_delete( $force ) {
		if ( ! $force && EMPTY_TRASH_DAYS && 'trash' !== get_post_status( $this->get_id() ) ) {
			$delete = wp_trash_post( $this->get_id() );
		} else {
			$delete = wp_delete_post( $this->get_id(), true );
		}

		return ( ! is_null( $delete ) && ! is_wp_error( $delete ) && false !== $delete );
	}
}
