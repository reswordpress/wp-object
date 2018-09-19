<?php

namespace Awethemes\WP_Object\Query;

abstract class Abstract_Query implements Query {
	/**
	 * The main query vars.
	 *
	 * @var array
	 */
	protected $main_query;

	/**
	 * Constructor.
	 *
	 * @param array $main_query The main query vars.
	 */
	public function __construct( $main_query = [] ) {
		$this->main_query = $main_query;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_main_query() {
		return $this->main_query ?: [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_main_query( array $main_query ) {
		$this->main_query = $main_query;

		return $this;
	}
}
