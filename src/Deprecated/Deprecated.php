<?php
namespace Awethemes\WP_Object\Deprecated;

trait Deprecated {
	/**
	 * Returns the attributes were changed but only in scope of $changes.
	 *
	 * @param  array        $changes    Scope of attributes changes.
	 * @param  string|array $attributes The attributes.
	 * @return array
	 */
	protected function get_changes_only( array $changes, $attributes ) {
		return array_intersect( (array) $attributes, array_keys( $changes ) );
	}
}
