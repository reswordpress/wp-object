<?php
namespace Awethemes\WP_Object\Deprecated;

use Awethemes\WP_Object\Utils\Utils;

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

	/**
	 * Parse the object_id.
	 *
	 * @param  mixed $object The object.
	 * @return int|null
	 */
	public static function parse_object_id( $object ) {
		return Utils::parse_object_id( $object );
	}

	/**
	 * Helper: Get terms as IDs from a taxonomy.
	 *
	 * @param  string $taxonomy Taxonomy name.
	 * @return array
	 */
	protected function get_term_ids( $taxonomy ) {
		return Utils::get_term_ids( $this->get_id(), $taxonomy );
	}

	/**
	 * Helper: Safely update a wordpress post.
	 *
	 * @param  array $post_data An array post data to update.
	 * @return bool|null
	 */
	protected function update_the_post( array $post_data ) {
		return Utils::update_the_post( $this->get_id(), $post_data );
	}
}
