<?php
namespace Awethemes\WP_Object\Query;

use Awethemes\WP_Object\WP_Object;

class Builder implements \ArrayAccess {
	/**
	 * The query to send to WP_Query.
	 *
	 * @var array
	 */
	protected $query_vars;

	/**
	 * The query instance.
	 *
	 * @var \Awethemes\WP_Object\Query\Query
	 */
	protected $query;

	/**
	 * The model being queried.
	 *
	 * @var \Awethemes\WP_Object\WP_Object
	 */
	protected $model;

	/**
	 * Constructor.
	 *
	 * @param array                            $query_vars The query vars.
	 * @param \Awethemes\WP_Object\Query\Query $query      The query instance.
	 */
	public function __construct( $query_vars = [], Query $query = null ) {
		$this->query      = $query;
		$this->query_vars = $query_vars;
	}

	/**
	 * Execute the query.
	 *
	 * @return \Awethemes\WP_Object\Collection
	 */
	public function get() {
		$query_vars = array_merge(
			$this->query->get_main_query(), $this->get_query_vars()
		);

		$models = $this->query->extract_items(
			$this->query->do_query( $query_vars )
		);

		return $this->get_model()->new_collection( $models );
	}

	/**
	 * Execute the query and get the first result.
	 *
	 * @return mixed
	 */
	public function first() {
		return $this->limit( 1 )->get()->first();
	}

	/**
	 * Alias to set the "limit" value of the query.
	 *
	 * @param  int $value The "limit" value. Use -1 to request all models.
	 * @return $this
	 */
	public function take( $value ) {
		return $this->limit( $value );
	}

	/**
	 * Build the "limit" query.
	 *
	 * @param  int $limit The "limit" value. Use -1 to request all models.
	 * @return $this
	 */
	public function limit( $limit ) {
		$this->query->apply_limit_query( $this->query_vars, (int) $limit );

		return $this;
	}

	/**
	 * Alias to set the "offset" value of the query.
	 *
	 * @param  int $value The "offset" value.
	 * @return $this
	 */
	public function skip( $value ) {
		return $this->offset( $value );
	}

	/**
	 * Build the "offset" query.
	 *
	 * @param  int $offset The "offset" value.
	 * @return $this
	 */
	public function offset( $offset ) {
		$this->query->apply_offset_query( $this->query_vars, max( 0, $offset ) );

		return $this;
	}

	/**
	 * Build the "orderby" query.
	 *
	 * @param string $orderby The column to order by.
	 * @param string $order   Order by DESC or ASC.
	 *
	 * @return $this
	 */
	public function orderby( $orderby, $order = 'DESC' ) {
		$this->query->apply_orderby_query( $this->query_vars, $orderby, $order );

		return $this;
	}

	/**
	 * Set the limit and offset for a given page.
	 *
	 * @param int $page     The page number.
	 * @param int $per_page The number items per page.
	 *
	 * @return $this
	 */
	public function for_page( $page, $per_page = 15 ) {
		return $this->skip( ( $page - 1 ) * $per_page )->take( $per_page );
	}

	/**
	 * Merge the current query_vars with given value.
	 *
	 * @param  array $query_vars The query vars.
	 * @return $this
	 */
	public function with( array $query_vars ) {
		$this->query_vars = array_merge( $this->query_vars, $query_vars );

		return $this;
	}

	/**
	 * Returns the query vars.
	 *
	 * @return array
	 */
	public function get_query_vars() {
		return $this->query_vars;
	}

	/**
	 * Get the model instance being queried.
	 *
	 * @return \Awethemes\WP_Object\WP_Object
	 */
	public function get_model() {
		if ( ! $this->model ) {
			throw new \BadMethodCallException( 'The model is not defined.' );
		}

		return $this->model;
	}

	/**
	 * Set a model instance for the model being queried.
	 *
	 * @param \Awethemes\WP_Object\WP_Object $model The model instance.
	 *
	 * @return $this
	 */
	public function set_model( WP_Object $model ) {
		$this->model = $model;

		$this->query = $model->new_query();

		return $this;
	}

	/**
	 * Handle dynamic calls to set query vars.
	 *
	 * @param  string $name       The query name.
	 * @param  array  $parameters The query value.
	 * @return $this
	 */
	public function __call( $name, $parameters ) {
		$value = count( $parameters ) > 0 ? $parameters[0] : true;

		$this->query_vars[ $name ] = $value;

		return $this;
	}

	/**
	 * Determine if the given offset exists.
	 *
	 * @param  string $offset The offset key.
	 * @return bool
	 */
	public function offsetExists( $offset ) {
		return isset( $this->query_vars[ $offset ] );
	}

	/**
	 * Get the value for a given offset.
	 *
	 * @param  string $offset The offset key.
	 * @return mixed
	 */
	public function offsetGet( $offset ) {
		return isset( $this->query_vars[ $offset ] ) ? $this->query_vars[ $offset ] : null;
	}

	/**
	 * Set the value at the given offset.
	 *
	 * @param  string $offset The offset key.
	 * @param  mixed  $value  The offset value.
	 * @return void
	 */
	public function offsetSet( $offset, $value ) {
		$this->query_vars[ $offset ] = $value;
	}

	/**
	 * Unset the value at the given offset.
	 *
	 * @param  string $offset The offset key.
	 * @return void
	 */
	public function offsetUnset( $offset ) {
		unset( $this->query_vars[ $offset ] );
	}

	/**
	 * Dynamically retrieve the value of a query.
	 *
	 * @param  string $key The key name.
	 * @return mixed
	 */
	public function __get( $key ) {
		return $this->offsetGet( $key );
	}

	/**
	 * Dynamically set the value of a query.
	 *
	 * @param  string $key   The key name.
	 * @param  mixed  $value The key value.
	 * @return void
	 */
	public function __set( $key, $value ) {
		$this->offsetSet( $key, $value );
	}

	/**
	 * Dynamically check if a query is set.
	 *
	 * @param  string $key The key name.
	 * @return bool
	 */
	public function __isset( $key ) {
		return $this->offsetExists( $key );
	}

	/**
	 * Dynamically unset a query.
	 *
	 * @param  string $key The key name.
	 * @return void
	 */
	public function __unset( $key ) {
		$this->offsetUnset( $key );
	}
}
