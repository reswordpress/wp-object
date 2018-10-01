<?php
namespace Awethemes\WP_Object\Query;

use Awethemes\WP_Object\Utils\Utils;

class Post_Query extends Query {
	/**
	 * An array of query vars to translation.
	 *
	 * @see \WP_Query::parse_query()
	 *
	 * @var array
	 */
	protected $trans_query_vars = [
		'select' => 'fields',
		'limit'  => 'posts_per_page',
	];

	/**
	 * Constructor.
	 *
	 * @param array|\Awethemes\WP_Object\Query\Query_Vars $main_query The main query vars.
	 */
	public function __construct( $main_query = [] ) {
		$this->query_vars = $main_query instanceof Query_Vars
			? $main_query
			: new Query_Vars( $main_query );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_by_id( $id ) {
		$post = get_post( Utils::parse_object_id( $id ), ARRAY_A );

		if ( ! $post || get_post_type( $post ) !== $this->object_type ) {
			return null;
		}

		return $post;
	}

	/**
	 * {@inheritdoc}
	 */
	public function do_query( $query_vars ) {
		return new \WP_Query( $query_vars->to_array() );
	}

	/**
	 * {@inheritdoc}
	 */
	public function extract_items( $the_query ) {
		return $the_query->posts;
	}

	/**
	 * Perform insert the model into the database.
	 *
	 * @param \Awethemes\WP_Object\Model $model      The model instance.
	 * @param array                      $attributes The attributes to insert.
	 * @return int|null
	 */
	public function doing_insert( $model, $attributes ) {
		return wp_insert_post( $attributes, false );
	}

	/**
	 * Perform update the model in the database.
	 *
	 * @param \Awethemes\WP_Object\Model $model The model instance.
	 * @param array                      $dirty The attributes to update.
	 * @return int|bool
	 */
	public function doing_update( $model, $dirty ) {
		return (bool) Utils::update_the_post( $model->get_id(), $dirty );
	}

	/**
	 * Perform delete a model from the database.
	 *
	 * @param \Awethemes\WP_Object\Model $model The model instance.
	 * @param bool                       $force Force delete or not.
	 * @return bool
	 */
	public function doing_delete( $model, $force ) {
		if ( ! $force && EMPTY_TRASH_DAYS && 'trash' !== get_post_status( $model->get_id() ) ) {
			$delete = wp_trash_post( $model->get_id() );
		} else {
			$delete = wp_delete_post( $model->get_id(), true );
		}

		return ( ! is_null( $delete ) && ! is_wp_error( $delete ) && false !== $delete );
	}
}
