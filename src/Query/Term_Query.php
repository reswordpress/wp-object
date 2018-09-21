<?php
namespace Awethemes\WP_Object\Query;

class Term_Query implements Query {
	/**
	 * {@inheritdoc}
	 */
	public function do_query( $query_vars ) {
		return new \WP_Term_Query( $query_vars );
	}

	/**
	 * {@inheritdoc}
	 */
	public function extract_items( $term_query ) {
		return $term_query->terms;
	}

	/**
	 * {@inheritdoc}
	 */
	public function apply_limit_query( array &$query_vars, $limit ) {
		$query_vars['number'] = (int) $limit;
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
