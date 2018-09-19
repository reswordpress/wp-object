<?php
namespace Awethemes\WP_Object\Query;

class Post_Query extends Abstract_Query {
	/**
	 * {@inheritdoc}
	 */
	public function do_query( $query_vars ) {
		return new \WP_Query( $query_vars );
	}

	/**
	 * {@inheritdoc}
	 */
	public function extract_items( $the_query ) {
		return $the_query->posts;
	}

	/**
	 * {@inheritdoc}
	 */
	public function apply_limit_query( array &$query_vars, $limit ) {
		$query_vars['posts_per_page'] = (int) $limit;
	}

	/**
	 * {@inheritdoc}
	 */
	public function apply_offset_query( &$query_vars, $offset ) {
		$query_vars['offset'] = $offset;
	}

	/**
	 * {@inheritdoc}
	 */
	public function apply_orderby_query( &$query_vars, $orderby, $order ) {
		$query_vars['order']   = $order;
		$query_vars['orderby'] = $orderby;
	}
}
