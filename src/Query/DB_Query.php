<?php
namespace Awethemes\WP_Object\Query;

use Awethemes\WP_Object\Database\Builder as QueryBuilder;

class DB_Query extends Query {
	/**
	 * The database query builder instance.
	 *
	 * @var \Awethemes\WP_Object\Database\Builder
	 */
	protected $query;

	protected $forward_methods = [
		'orderby' => 'orderBy',
	];

	/**
	 * Constructor.
	 *
	 * @param QueryBuilder $query The database query builder.
	 */
	public function __construct( QueryBuilder $query ) {
		$this->query = $query;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_by_id( $id ) {
		return $this->query->where( $this->primary_key, $id )->first();
	}

	/**
	 * Get the query vars.
	 *
	 * @return \Awethemes\WP_Object\Database\Builder
	 */
	public function get_query_vars() {
		return $this->query;
	}

	/**
	 * Execute the query to retrieves items.
	 *
	 * @param  \Awethemes\WP_Object\Database\Builder $query The query builder instance.
	 * @return array
	 */
	public function do_query( $query ) {
		return $query->get();
	}

	/**
	 * {@inheritdoc}
	 */
	public function apply_query( $name, ...$vars ) {
		if ( array_key_exists( $name, $this->forward_methods ) ) {
			$name = $this->forward_methods[ $name ];
		}

		$this->query->{$name}( ...$vars );
	}
}
