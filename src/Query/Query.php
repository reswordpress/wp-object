<?php
namespace Awethemes\WP_Object\Query;

/**
 * Class Query
 *
 * @method int|null doing_insert( $model, $attributes )
 * @method int|bool doing_update( $mode, $dirty )
 * @method bool     doing_delete( $model, $force )
 *
 * @package Awethemes\WP_Object\Query
 */
abstract class Query {
	/**
	 * The table name.
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * The primary key name.
	 *
	 * @var string
	 */
	protected $primary_key;

	/**
	 * Find a model by its primary key.
	 *
	 * @param  int|mixed $id
	 * @return mixed
	 */
	abstract public function get_by_id( $id );

	/**
	 * Get the query vars.
	 *
	 * @return mixed
	 */
	abstract public function get_query_vars();

	/**
	 * Execute the query to retrieves items.
	 *
	 * @param  mixed $query_vars The query vars.
	 * @return mixed
	 */
	abstract public function do_query( $query_vars );

	/**
	 * Extract items from the query.
	 *
	 * @param  mixed $items The raw items.
	 * @return array
	 */
	public function extract_items( $items ) {
		return $items;
	}

	/**
	 * //
	 *
	 * @param string $name
	 * @param mixed  ...$vars
	 */
	public function apply_query( $name, ...$vars ) {
		throw new \InvalidArgumentException( 'Unsupported query [' . $name . ']' );
	}

	/**
	 * Set the table name.
	 *
	 * @param  string $table The table name.
	 * @return $this
	 */
	public function set_table( $table ) {
		$this->table = $table;

		return $this;
	}

	/**
	 * Set the primary key name.
	 *
	 * @param  string $primary_key The primary key name.
	 * @return $this
	 */
	public function set_primary_key( $primary_key ) {
		$this->primary_key = $primary_key;

		return $this;
	}
}
