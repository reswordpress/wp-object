<?php
namespace Awethemes\WP_Object;

class Term extends Model {
	/**
	 * Returns the taxonomy of the term.
	 *
	 * @return string
	 */
	public function get_taxonomy() {
		return $this->object_type;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function setup_instance() {
		$wp_term = get_term( $this->get_id(), $this->get_taxonomy() );

		if ( ! is_null( $wp_term ) && ! is_wp_error( $wp_term ) ) {
			$this->set_instance( $wp_term );
		}
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

	/**
	 * {@inheritdoc}
	 */
	protected function perform_delete( $force ) {
		$delete = wp_delete_term( $this->get_id(), $this->get_taxonomy() );

		return ( ! is_wp_error( $delete ) && true === $delete );
	}
}
