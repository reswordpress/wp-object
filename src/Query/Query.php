<?php
namespace Awethemes\WP_Object\Query;

interface Query {
	/**
	 * Returns the main query vars.
	 *
	 * @return array
	 */
	public function get_main_query();

	/**
	 * Sets the main query vars.
	 *
	 * @param  array $main_query The main query vars.
	 *
	 * @return $this
	 */
	public function set_main_query( array $main_query );

	/**
	 * Perform the query.
	 *
	 * @param array $query_vars The query vars.
	 *
	 * @return mixed One of WP_Query|WP_Term_Query|WP_User_Query
	 */
	public function do_query( $query_vars );

	/**
	 * Extract items from the query.
	 *
	 * @param  mixed $the_query The query instance.
	 * @return array
	 */
	public function extract_items( $the_query );

	/**
	 * Alter the "limit" query.
	 *
	 * @param array $query_vars The query vars.
	 * @param int   $value      The "limit" value.
	 */
	public function apply_limit_query( array &$query_vars, $value );

	/**
	 * Alter the "offset" query.
	 *
	 * @param array $query_vars The query vars.
	 * @param int   $offset     The offset value.
	 */
	public function apply_offset_query( &$query_vars, $offset );

	/**
	 * Alter the "orderby" query.
	 *
	 * @param array  $query_vars The query vars.
	 * @param string $orderby    The column to order by.
	 * @param string $order      Order by DESC or ASC.
	 */
	public function apply_orderby_query( &$query_vars, $orderby, $order );
}
