<?php
namespace Awethemes\WP_Object;

use Awethemes\WP_Object\Query\Query;

class Builder {
	/**
	 * The query instance.
	 *
	 * @var \Awethemes\WP_Object\Query\Query
	 */
	protected $query;

	/**
	 * The model being queried.
	 *
	 * @var \Awethemes\WP_Object\Model
	 */
	protected $model;

	/**
	 * Constructor.
	 *
	 * @param \Awethemes\WP_Object\Query\Query $query The query instance.
	 */
	public function __construct( Query $query ) {
		$this->query = $query;
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
	 * Get the model instance being queried.
	 *
	 * @return \Awethemes\WP_Object\Model
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
	 * @param \Awethemes\WP_Object\Model $model The model instance.
	 *
	 * @return $this
	 */
	public function set_model( Model $model ) {
		$this->model = $model;

		$this->query = $model->new_query();

		return $this;
	}
}
