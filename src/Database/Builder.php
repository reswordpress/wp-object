<?php
namespace Awethemes\WP_Object\Database;

use Awethemes\WP_Object\Collection;
use Database\Query\Builder as QueryBuilder;

class Builder extends QueryBuilder {
	/**
	 * Execute the query as a "select" statement.
	 *
	 * @param  array $columns
	 *
	 * @return \Awethemes\WP_Object\Collection
	 */
	public function get( $columns = [ '*' ] ) {
		$results = $this->onceWithColumns( $columns, function () {
			return $this->connection->fetchAll( $this->toSql(), $this->getBindings() );
		} );

		return $this->newCollection( $results );
	}

	/**
	 * Create new collection.
	 *
	 * @param mixed $items
	 *
	 * @return \Awethemes\WP_Object\Collection
	 */
	protected function newCollection( $items ) {
		return new Collection( $items );
	}

	/**
	 * Execute the given callback while selecting the given columns.
	 *
	 * After running the callback, the columns are reset to the original value.
	 *
	 * @param  array    $columns
	 * @param  callable $callback
	 *
	 * @return mixed
	 */
	protected function onceWithColumns( $columns, $callback ) {
		$original = $this->columns;

		if ( is_null( $original ) ) {
			$this->columns = $columns;
		}

		$result = $callback();

		$this->columns = $original;

		return $result;
	}
}
