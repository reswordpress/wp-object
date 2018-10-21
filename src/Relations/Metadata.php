<?php
namespace Awethemes\WP_Object\Relations;

use Awethemes\WP_Object\Model;

class Metadata {
	/**
	 * The meta type (post, term, user, etc.).
	 *
	 * @var string
	 */
	protected $meta_type;

	/**
	 * Constructor.
	 *
	 * @param string $meta_type
	 */
	public function __construct( Model $model, $meta_type = 'post' ) {
		$this->meta_type = $meta_type;
	}

	/**
	 * Get a metadata by meta key.
	 *
	 * @param  string $key The metadata key.
	 * @return mixed|null
	 */
	public function get_meta( $key ) {
		return get_metadata( $this->meta_type, $this->model->get_id(), $key, true );
	}

	/**
	 * Add metadata.
	 *
	 * @param string $meta_key   Metadata key.
	 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
	 * @return int|false
	 */
	public function add_meta( $meta_key, $meta_value ) {
		return add_metadata( $this->meta_type, $this->get_id(), $meta_key, $meta_value, true );
	}

	/**
	 * Update metadata.
	 *
	 * @param  string $meta_key   Metadata key.
	 * @param  mixed  $meta_value Metadata value. Must be serializable if non-scalar.
	 * @return bool
	 */
	public function update_meta( $meta_key, $meta_value ) {
		$updated = update_metadata( $this->meta_type, $this->get_id(), $meta_key, $meta_value );

		if ( false !== $updated ) {
			$this->metadata[ $meta_key ] = $meta_value;

			if ( $attribute = $this->get_mapping_attribute( $meta_key ) ) {
				$this->set_attribute( $attribute, $meta_value );
			}

			return true;
		}

		return false;
	}

	/**
	 * Delete metadata.
	 *
	 * @param  string $meta_key Metadata key.
	 * @return bool
	 */
	public function delete_meta( $meta_key ) {
		return delete_metadata( $this->meta_type, $this->get_id(), $meta_key, '', false );
	}
}
