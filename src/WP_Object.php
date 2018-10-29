<?php
namespace Awethemes\WP_Object;

use Awethemes\WP_Object\Utils\Utils;
use Awethemes\WP_Object\Utils\Object_Data;

class WP_Object extends Model {
	use Deprecated\Metadata;
	use Deprecated\Deprecated;

	/**
	 * The object instance data.
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

		if ( $id = Utils::parse_object_id( $object ) ) {
			$this->attributes[ $this->get_key_name() ] = $id;

			$this->setup_instance();

			$this->exists = (bool) $this->instance_data;

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
		if ( ! $key = $this->get_key() ) {
			return;
		}

		if ( $instance = $this->new_query_builder()->raw( $key ) ) {
			$this->instance_data = new Object_Data( $instance );
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
		$this->perform_update_metadata(
			$this->recently_created ? $this->get_dirty() : $this->get_changes()
		);

		$this->resetup();

		parent::finish_save();
	}

	/**
	 * Resetup the object.
	 *
	 * @return void
	 */
	protected function resetup() {
		$this->setup_instance();

		$this->setup_metadata();

		$this->setup();
	}

	/**
	 * {@inheritdoc}
	 */
	public function new_query() {
		return new Query\Post_Query( [
			'post_type'           => $this->get_object_type(),
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
			'posts_per_page'      => -1,
		] );
	}

	/**
	 * {@inheritdoc}
	 */
	public function __get( $key ) {
		if ( 'id' === $key ) {
			return $this->get_key();
		}

		if ( 'instance' === $key ) {
			return $this->instance_data;
		}

		return $this->get_attribute( $key );
	}
}
