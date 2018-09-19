<?php
namespace Awethemes\WP_Object;

abstract class WP_Object implements \ArrayAccess, \JsonSerializable {
	use Traits\Has_Attributes,
		Traits\Has_Metadata;

	/**
	 * Name of object type.
	 *
	 * @var string
	 */
	protected $object_type;

	/**
	 * ID for this object.
	 *
	 * @var int
	 */
	protected $id;

	/**
	 * WP Object (WP_Post, WP_Term, etc...) instance.
	 *
	 * @var mixed
	 */
	protected $instance;

	/**
	 * Indicates if the object exists.
	 *
	 * @var bool
	 */
	protected $exists = false;

	/**
	 * Indicates if the object was inserted during the current request lifecycle.
	 *
	 * @var bool
	 */
	protected $recently_created = false;

	/**
	 * WP Object constructor.
	 *
	 * @param mixed $object Object ID we'll working for.
	 */
	public function __construct( $object = 0 ) {
		$this->id = Utils::parse_object_id( $object );

		// Setup the wp core object instance.
		if ( ! is_null( $this->id ) ) {
			$this->setup_instance();
			$this->exists = ! is_null( $this->instance );

			// If object mark exists, setup the attributes.
			if ( $this->exists() ) {
				$this->setup_metadata();
				$this->setup();
			}
		}

		// Set original to attributes so we can track and reset attributes if needed.
		$this->sync_original();
	}

	/**
	 * Setup the object attributes.
	 *
	 * @return void
	 */
	protected function setup() {}

	/**
	 * Setup WP Core Object based on ID and object-type.
	 *
	 * @return void
	 */
	abstract protected function setup_instance();

	/**
	 * Update the object.
	 *
	 * @param  array $attributes The attributes to update.
	 * @return bool
	 */
	public function update( array $attributes = [] ) {
		if ( ! $this->exists() ) {
			return false;
		}

		return $this->fill( $attributes )->save();
	}

	/**
	 * Save the object to the database.
	 *
	 * @return bool
	 */
	public function save() {
		/**
		 * Fires saving action.
		 *
		 * @param static $wp_object Current object instance.
		 */
		do_action( $this->prefix( 'saving' ), $this );

		if ( $this->recently_created ) {
			$this->recently_created = false;
		}

		// Allow sub-class overwrite before_save method, here we can
		// validate the attribute data before save or doing something else.
		$this->before_save();

		// If the object already exists we can update changes.
		// Otherwise, we'll just insert them.
		if ( $this->exists() ) {
			$saved = $this->is_dirty() ? $this->update_object() : true;
		} else {
			$saved = $this->insert_object();
		}

		if ( $saved ) {
			$this->finish_save();

			/**
			 * Fires saved action.
			 *
			 * @param static $wp_object Current object instance.
			 */
			do_action( $this->prefix( 'saved' ), $this );

			$this->sync_original();
		}

		return $saved;
	}

	/**
	 * Do something before doing save.
	 *
	 * @return void
	 */
	protected function before_save() {}

	/**
	 * Do something when finish save.
	 *
	 * @return void
	 */
	protected function finish_save() {
		$this->clean_cache();

		$this->perform_update_metadata(
			$this->recently_created ? $this->get_dirty() : $this->get_changes()
		);

		$this->resetup();
	}

	/**
	 * Clean object cache after saved.
	 *
	 * @return void
	 */
	protected function clean_cache() {}

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
	 * Run update object.
	 *
	 * @return bool
	 */
	protected function update_object() {
		// If the "prev_create" filter returns false we'll bail out of the update and return
		// false, indicating that the save failed. This provides a chance for any
		// hooks to cancel update operations if validations fail or whatever.
		if ( false === apply_filters( $this->prefix( 'prev_create' ), true ) ) {
			return false;
		}

		/**
		 * Fires updating action.
		 *
		 * @param static $wp_object Current object instance.
		 */
		do_action( $this->prefix( 'updating' ), $this );

		$dirty = $this->get_dirty();

		if ( count( $dirty ) > 0 ) {
			$updated = $this->perform_update( $dirty );

			if ( false === $updated ) {
				return false;
			}

			/**
			 * Fires updated action.
			 *
			 * @param static $wp_object Current object instance.
			 */
			do_action( $this->prefix( 'updated' ), $this );

			$this->sync_changes();
		}

		return true;
	}

	/**
	 * Run perform update object.
	 *
	 * @param  array $dirty The attributes has been modified.
	 * @return bool|void
	 */
	protected function perform_update( array $dirty ) {}

	/**
	 * Run insert object into database.
	 *
	 * @return bool
	 */
	protected function insert_object() {
		// If the "prev_create" filter returns false we'll bail out of the create and return
		// false, indicating that the save failed. This provides a chance for any
		// hooks to cancel create operations if validations fail or whatever.
		if ( false === apply_filters( $this->prefix( 'prev_create' ), true ) ) {
			return false;
		}

		/**
		 * Fires creating action.
		 *
		 * @param static $wp_object Current object instance.
		 */
		do_action( $this->prefix( 'creating' ), $this );

		$insert_id = $this->perform_insert();

		if ( is_int( $insert_id ) && $insert_id > 0 ) {
			// Set new ID after insert success.
			$this->id = $insert_id;

			$this->exists = true;

			$this->recently_created = true;

			/**
			 * Fires after wp-object is created.
			 *
			 * @param static $wp_object Current object instance.
			 */
			do_action( $this->prefix( 'created' ), $this );

			return true;
		}

		return false;
	}

	/**
	 * Run perform insert object into database.
	 *
	 * @return int|void
	 */
	protected function perform_insert() {}

	/**
	 * Trash or delete a wp-object.
	 *
	 * @param  bool $force Optional. Whether to bypass trash and force deletion.
	 * @return bool|null
	 */
	public function delete( $force = false ) {
		// If the object doesn't exist, there is nothing to delete
		// so we'll just return immediately and not do anything else.
		if ( ! $this->exists() ) {
			return null;
		}

		// If the "prev_delete" filter returns false we'll bail out of the delete
		// and just return. Indicating that the delete failed.
		if ( false === apply_filters( $this->prefix( 'prev_delete' ), true ) ) {
			return null;
		}

		/**
		 * Fires before a wp-object is deleted.
		 *
		 * @param static $wp_object Current object instance.
		 */
		do_action( $this->prefix( 'deleting' ), $this );

		$deleted = $this->perform_delete( $force );

		if ( $deleted ) {
			$this->clean_cache();

			// Now object will not exists.
			$this->exists = false;

			/**
			 * Fires after a WP_Object is deleted.
			 *
			 * @param int $object_id Object ID was deleted.
			 */
			do_action( $this->prefix( 'deleted' ), $this->get_id() );
		}

		return $deleted;
	}

	/**
	 * Perform delete object.
	 *
	 * @see wp_delete_post()
	 * @see wp_delete_term()
	 *
	 * @param  bool $force Force delete or not.
	 * @return bool
	 */
	abstract protected function perform_delete( $force );

	/**
	 * Get all of the models.
	 *
	 * @return \Awethemes\WP_Object\Collection static[]
	 */
	public static function all() {
		return ( new static )->new_builder()->limit( -1 )->get();
	}

	/**
	 * Begin querying the model.
	 *
	 * @param array $query The query.
	 *
	 * @return \Awethemes\WP_Object\Builder
	 */
	public static function query( $query = [] ) {
		return ( new static )->new_builder( $query );
	}

	/**
	 * Get a new query builder for the model's.
	 *
	 * @param array $query_vars The query.
	 *
	 * @return \Awethemes\WP_Object\Builder
	 */
	public function new_builder( $query_vars = [] ) {
		return ( new Builder( $query_vars ) )->set_model( $this );
	}

	/**
	 * Get a new query instance.
	 *
	 * @return \Awethemes\WP_Object\Query\Query
	 */
	public function new_query() {
		throw new \RuntimeException( 'The "' . get_class( $this ) . '" does not support query.' );
	}

	/**
	 * Create a new Collection instance.
	 *
	 * @param  mixed $models An array of models.
	 * @return \Awethemes\WP_Object\Collection
	 */
	public function new_collection( $models ) {
		return ( new Collection( $models ) )->map_into( get_class( $this ) );
	}

	/**
	 * Returns the WP internal type, e.g: "post", "term", "user", etc.
	 *
	 * @return string
	 */
	public function get_wp_type() {
		return 'post';
	}

	/**
	 * Get the object ID.
	 *
	 * @return int
	 */
	public function get_id() {
		return (int) $this->id;
	}

	/**
	 * Get the object instance,
	 *
	 * @return mixed
	 */
	public function get_instance() {
		return $this->instance;
	}

	/**
	 * Determine if object exists.
	 *
	 * @return bool
	 */
	public function exists() {
		return $this->exists;
	}

	/**
	 * Set the object instance,
	 *
	 * @param  mixed $instance The object instance.
	 * @return mixed
	 */
	protected function set_instance( $instance ) {
		$this->instance = $instance;

		return $this;
	}

	/**
	 * Helper: Prefix for action and filter hooks for this object.
	 *
	 * @param  string $hook_name Hook name without prefix.
	 * @return string
	 */
	protected function prefix( $hook_name ) {
		return sprintf( 'wp_%s_%s', $this->object_type, $hook_name );
	}

	/**
	 * Dynamically retrieve attributes on the object.
	 *
	 * @param  string $key The attribute key name.
	 * @return mixed
	 */
	public function __get( $key ) {
		return $this->get_attribute( $key );
	}

	/**
	 * Dynamically set attributes on the object.
	 *
	 * @param  string $key   The attribute key name.
	 * @param  mixed  $value The attribute value.
	 * @return void
	 */
	public function __set( $key, $value ) {
		$this->set_attribute( $key, $value );
	}

	/**
	 * Determine if an attribute exists on the object.
	 *
	 * @param  string $key The attribute key name.
	 * @return bool
	 */
	public function __isset( $key ) {
		return ! is_null( $this->get_attribute( $key ) );
	}

	/**
	 * Unset an attribute on the object.
	 *
	 * @param  string $key The attribute key name to remove.
	 * @return void
	 */
	public function __unset( $key ) {
		unset( $this->attributes[ $key ] );
	}

	/**
	 * Returns the value at specified offset.
	 *
	 * @param  string $offset The offset to retrieve.
	 * @return mixed
	 */
	public function offsetGet( $offset ) {
		return $this->$offset;
	}

	/**
	 * Assigns a value to the specified offset.
	 *
	 * @param string $offset The offset to assign the value to.
	 * @param mixed  $value  The value to set.
	 */
	public function offsetSet( $offset, $value ) {
		$this->$offset = $value;
	}

	/**
	 * Whether or not an offset exists.
	 *
	 * @param  string $offset An offset to check for.
	 * @return bool
	 */
	public function offsetExists( $offset ) {
		return isset( $this->$offset );
	}

	/**
	 * Unsets an offset.
	 *
	 * @param string $offset The offset to unset.
	 */
	public function offsetUnset( $offset ) {
		unset( $this->$offset );
	}

	/**
	 * Retrieves the data for JSON serialization.
	 *
	 * @return array
	 */
	public function jsonSerialize() {
		return $this->to_array();
	}

	/**
	 * Retrieves the attributes as array.
	 *
	 * @return array
	 */
	public function to_array() {
		return array_merge( [ 'id' => $this->get_id() ], $this->attributes );
	}

	/**
	 * Convert the object to its JSON representation.
	 *
	 * @param  int $options JSON encode options.
	 * @return string
	 */
	public function to_json( $options = 0 ) {
		return json_encode( $this->jsonSerialize(), $options );
	}

	/**
	 * Convert the object to its string representation.
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->to_json();
	}
}
