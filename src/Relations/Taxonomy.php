<?php
namespace Awethemes\WP_Object\Relations;

use Awethemes\WP_Object\Model;

class Taxonomy {
	/**
	 *
	 *
	 * @var string
	 */
	protected $taxonomy;

	/**
	 *
	 *
	 * @var \Awethemes\WP_Object\Model
	 */
	protected $parent;

	/**
	 * The class name of the custom pivot model to use for the relationship.
	 *
	 * @var string
	 */
	protected $using;

	/**
	 * Constructor.
	 *
	 * @param string                     $taxonomy
	 * @param \Awethemes\WP_Object\Model $parent
	 */
	public function __construct( $taxonomy, Model $parent ) {
		$this->taxonomy = $taxonomy;
		$this->parent = $parent;
	}

	/**
	 * Create a new instance of the related model.
	 *
	 * @param  array  $attributes
	 * @param  array  $joining
	 * @param  bool   $touch
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public function create(array $attributes = [], array $joining = [], $touch = true)
	{
		$instance = $this->related->newInstance($attributes);

		// Once we save the related model, we need to attach it to the base model via
		// through intermediate table so we'll use the existing "attach" method to
		// accomplish this which will insert the record and any more attributes.
		$instance->save( [ 'touch' => false ] );

		$this->attach($instance, $joining, $touch);

		return $instance;
	}
}
