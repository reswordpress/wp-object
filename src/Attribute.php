<?php

namespace Awethemes\WP_Object;

class Attribute {
	public $name;

	public $value;

	/**
	 * Constructor.
	 *
	 * @param string $name
	 * @param mixed  $value
	 */
	public function __construct( $name, $value ) {
		$this->name  = $name;
		$this->value = $value;
	}

	/**
	 * Convert the object to its string representation.
	 *
	 * @return string
	 */
	public function __toString() {
		return (string) $this->value;
	}
}
