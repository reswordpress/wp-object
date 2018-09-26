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

	/**
	 * Perform insert the model into the database.
	 *
	 * @param \Awethemes\WP_Object\Model $model      The model instance.
	 * @param array                      $attributes The attributes to insert.
	 * @return int|null
	 */
	public function doing_insert( $model, $attributes ) {
		$query = $model->new_db_query();

		return $query->insertGetId( $attributes, $key_name = $model->get_key_name() );
	}

	/**
	 * Perform update the model in the database.
	 *
	 * @param \Awethemes\WP_Object\Model $model The model instance.
	 * @param array                      $dirty The attributes to update.
	 * @return int|bool
	 */
	public function doing_update( $model, $dirty ) {
		$updated = $this->get_query_for_save( $model )->update( $dirty );

		return is_int( $updated ) ? $updated : false;
	}

	/**
	 * Perform delete a model from the database.
	 *
	 * @param \Awethemes\WP_Object\Model $model The model instance.
	 * @param bool                       $force Force delete or not.
	 * @return bool
	 */
	public function doing_delete( $model, $force ) {
		// TODO: Support force delete.
		return (bool) $this->get_query_for_save( $model )->delete();
	}

	/**
	 * Gets the query for save action.
	 *
	 * @param  \Awethemes\WP_Object\Model $model The model instance.
	 * @return QueryBuilder
	 */
	protected function get_query_for_save( $model ) {
		$query = $model->new_db_query();

		$query->where( $model->get_key_name(), '=', $model->get_key_for_save() );

		return $query;
	}
}
