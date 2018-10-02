<?php
namespace Awethemes\WP_Object;

class Term extends Model {
	/**
	 * Name of the taxonomy.
	 *
	 * @var string
	 */
	protected $object_type = 'category';

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'terms';

	/**
	 * Returns name of the taxonomy.
	 *
	 * @return string
	 */
	public function get_taxonomy() {
		return $this->object_type;
	}

	/**
	 * {@inheritdoc}
	 */
	public function new_query() {
		return new Query\Term_Query( [
			'taxonomy'   => $this->get_taxonomy(),
			'hide_empty' => false,
		] );
	}
}
