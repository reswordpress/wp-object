<?php
namespace Awethemes\WP_Object;

class WP_Object extends Model {
	use Deprecated\Deprecated;

	/**
	 * Name of object type.
	 *
	 * @var string
	 */
	protected $object_type;

	/**
	 * Return the object type name.
	 *
	 * @return string
	 */
	public function get_object_type() {
		return $this->object_type;
	}

	/**
	 * Retrieves the attributes as array.
	 *
	 * @return array
	 */
	public function to_array() {
		return array_merge( [ 'id' => $this->get_id() ], $this->attributes );
	}
}
