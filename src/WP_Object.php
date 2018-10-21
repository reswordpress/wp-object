<?php
namespace Awethemes\WP_Object;

use Awethemes\WP_Object\Utils\Utils;

class WP_Object extends Model {
	use Deprecated\Metadata;
	use Deprecated\Deprecated;

	/**
	 * Store the ID.
	 *
	 * @var int
	 */
	protected $id;

	/**
	 * Name of object type.
	 *
	 * @var string
	 */
	protected $object_type;

	/**
	 * WordPress type for object, Ex: "post" and "term".
	 *
	 * @var string
	 */
	protected $wp_type = 'post';

	/**
	 * Prefix for hooks.
	 *
	 * @var string
	 */
	protected $prefix = 'wp';

	/**
	 * WP Object (WP_Post, WP_Term, etc...) instance.
	 *
	 * @var mixed
	 */
	protected $instance_data;

	/**
	 * Initialize the object.
	 *
	 * @param mixed $object Object ID we'll working for.
	 */
	protected function initialize( $object ) {
		if ( empty( $object ) || is_array( $object ) ) {
			return;
		}

		$this->id = Utils::parse_object_id( $object );

		// Setup the wp core object instance.
		if ( ! is_null( $this->id ) ) {
			$this->setup_instance();

			$this->exists = ! is_null( $this->instance_data );

			if ( $this->exists() ) {
				$this->setup_metadata();
				$this->setup();
			}
		}
	}

	/**
	 * Setup WP Core Object based on ID and object-type.
	 *
	 * @return void
	 */
	protected function setup_instance() {
		switch ( $this->wp_type ) {
			case 'post':
				$wp_post = get_post( $this->get_id() );
				if ( ! is_null( $wp_post ) && get_post_type( $wp_post->ID ) === $this->object_type ) {
					$this->set_instance( $wp_post );
				}
				break;

			case 'term':
				$wp_term = get_term( $this->get_id(), $this->object_type );
				if ( ! is_null( $wp_term ) && ! is_wp_error( $wp_term ) ) {
					$this->set_instance( $wp_term );
				}
				break;
		}
	}

	/**
	 * Setup the object attributes.
	 *
	 * @return void
	 */
	protected function setup() {}

	/**
	 * Get the object instance,
	 *
	 * @return mixed
	 */
	public function get_instance() {
		return $this->instance_data;
	}

	/**
	 * Set the object instance,
	 *
	 * @param  mixed $instance The object instance.
	 * @return mixed
	 */
	protected function set_instance( $instance ) {
		$this->instance_data = $instance;

		return $this;
	}

	/**
	 * Perform any actions that are necessary after the model is saved.
	 *
	 * @return void
	 */
	protected function finish_save() {
		parent::finish_save();

		$this->perform_update_metadata(
			$this->recently_created ? $this->get_dirty() : $this->get_changes()
		);

		$this->resetup();
	}

	/**
	 * Resetup the object.
	 *
	 * @return void
	 */
	protected function resetup() {
		$this->setup_instance();

		$this->metadata = $this->fetch_metadata();
		$this->setup_metadata();

		$this->setup();
	}

	/**
	 * Flush the cache or whatever if necessary.
	 *
	 * @return void
	 */
	protected function flush_cache() {
		$this->clean_cache();
	}

	/**
	 * Clean object cache after saved.
	 *
	 * @return void
	 */
	protected function clean_cache() {}

	/**
	 * Helper: Prefix for action and filter hooks for this object.
	 *
	 * @param  string $hook_name Hook name without prefix.
	 * @return string
	 */
	protected function prefix( $hook_name ) {
		return sprintf( '%s/%s/%s', $this->prefix, $this->object_type, $hook_name );
	}

	/**
	 * Return the object type name.
	 *
	 * @return string
	 */
	public function get_object_type() {
		return $this->object_type;
	}

	/**
	 * Dynamically retrieve attributes on the model.
	 *
	 * @param  string $key The attribute key name.
	 * @return mixed
	 */
	public function __get( $key ) {
		if ( 'instance' === $key ) {
			return $this->instance_data;
		}

		return $this->get_attribute( $key );
	}

	/**
	 * Retrieves the attributes as array.
	 *
	 * @return array
	 */
	public function to_array() {
		if ( array_key_exists( 'id', $attributes = $this->get_attributes() ) ) {
			return $attributes;
		}

		return array_merge( [ 'id' => $this->get_id() ], $attributes );
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
