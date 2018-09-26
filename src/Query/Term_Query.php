<?php
namespace Awethemes\WP_Object\Query;

class Term_Query extends Query {
	/**
	 * Find a model by its primary key.
	 *
	 * @param  int|mixed $id
	 * @return mixed
	 */
	public function find( $id ) {
		// TODO: Implement find() method.
	}

	/**
	 * Get the query vars.
	 *
	 * @return mixed
	 */
	public function get_query_vars() {
		// TODO: Implement get_query_vars() method.
	}

	/**
	 * Execute the query to retrieves items.
	 *
	 * @param  array $query_vars The query vars.
	 * @return \WP_Term_Query
	 */
	public function do_query( $query_vars ) {
		return new \WP_Term_Query( $this->query_vars );
	}

	/**
	 * {@inheritdoc}
	 */
	public function extract_items( $term_query ) {
		return $term_query->terms;
	}
}
