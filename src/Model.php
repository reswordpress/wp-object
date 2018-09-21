<?php
namespace Awethemes\WP_Object;

abstract class Model implements \ArrayAccess, \JsonSerializable {
	use Concerns\Has_Attributes,
		Concerns\Has_Events;

	/**
	 * Name of object type.
	 *
	 * @var string
	 */
	protected $object_type;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	protected $primary_key = 'ID';

	/**
	 * Indicates if the object exists.
	 *
	 * @var bool
	 */
	public $exists = false;

	/**
	 * Indicates if the object was inserted during the current request lifecycle.
	 *
	 * @var bool
	 */
	public $recently_created = false;

	/**
	 * Constructor.
	 *
	 * @param array|mixed $attributes The model attributes.
	 */
	public function __construct( $attributes = [] ) {
		// TODO: Back compat constructor as int.
		// Set original to attributes so we can track and reset attributes if needed.
		$this->sync_original();

		// Fill the attributes.
		$this->fill( $attributes );
	}

	/**
	 * Fill the object with an array of attributes.
	 *
	 * @param  array $attributes An array of attributes to fill.
	 * @return $this
	 */
	public function fill( array $attributes ) {
		foreach ( $attributes as $key => $value ) {
			$this->set_attribute( $key, $value );
		}

		return $this;
	}

	/**
	 * Create a new instance of the given model.
	 *
	 * @param  array|mixed $attributes
	 * @param  bool        $exists
	 *
	 * @return static
	 */
	public function new_instance( $attributes = [], $exists = false ) {
		// This method just provides a convenient way for us to generate fresh model
		// instances of this current model. It is particularly useful during the
		// hydration of new objects via the query builder instances.
		$model = new static( $attributes );

		$model->exists = $exists;

		return $model;
	}

	/**
	 * Create a new model instance that is existing.
	 *
	 * @param  array       $attributes
	 * @param  string|null $connection
	 *
	 * @return static
	 */
	public function new_from_builder( $attributes = [], $connection = null ) {
		$model = $this->new_instance( [], true );

		$model->setRawAttributes( (array) $attributes, true );

		$model->trigger( 'retrieved', false );

		return $model;
	}

	/**
	 * Update the model in the database.
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
	 * Save the model to the database.
	 *
	 * @return bool
	 */
	public function save() {
		// If the "saving" event returns false we'll bail out of the save and return
		// false, indicating that the save failed. This provides a chance for any
		// listeners to cancel save operations if validations fail or whatever.
		if ( false === $this->trigger( 'saving' ) ) {
			return false;
		}

		// If the model already exists in the database we can just update our record
		// that is already in this database using the current IDs in this "where"
		// clause to only update this model. Otherwise, we'll just insert them.
		if ( $this->exists() ) {
			$saved = $this->is_dirty() ? $this->perform_update() : true;
		} else {
			$saved = $this->perform_insert();
		}

		if ( $saved ) {
			$this->finish_save();
		}

		return $saved;
	}

	/**
	 * Perform any actions that are necessary after the model is saved.
	 *
	 * @return void
	 */
	protected function finish_save() {
		$this->trigger( 'saved' );

		$this->flush_cache();

		$this->sync_original();
	}

	/**
	 * Flush the cache or whatever if necessary.
	 *
	 * @return void
	 */
	protected function flush_cache() {}

	/**
	 * Perform a model update operation.
	 *
	 * @return bool
	 */
	protected function perform_update() {
		// If the updating event returns false, we will cancel the update operation so
		// developers can hook Validation systems into their models and cancel this
		// operation if the model does not pass validation. Otherwise, we update.
		if ( false === $this->trigger( 'updating', true ) ) {
			return false;
		}

		$dirty = $this->get_dirty();

		if ( count( $dirty ) > 0 ) {
			// Pass the update action into subclass to process.
			if ( false === $this->doing_update( $dirty ) ) {
				return false;
			}

			$this->sync_changes();

			$this->trigger( 'updating' );
		}

		return true;
	}

	/**
	 * Perform the update the model into the database.
	 *
	 * @param  array $dirty The attributes to update.
	 *
	 * @return bool|void
	 */
	protected function doing_update( $dirty ) {
		throw new \RuntimeException( 'The update action is not supported in the [' . get_class( $this ) . ']' );
	}

	/**
	 * Perform a model insert operation.
	 *
	 * @return bool
	 */
	protected function perform_insert() {
		if ( false === $this->trigger( 'creating', true ) ) {
			return false;
		}

		// Pass the action to subclass to process.
		$insert_id = $this->doing_insert(
			$this->get_attributes()
		);

		if ( ! is_int( $insert_id ) || 0 === $insert_id ) {
			return false;
		}

		// We will go ahead and set the exists property to true, so that it is set when
		// the created event is fired, just in case the developer tries to update it
		// during the event. This will allow them to do so and run an update here.
		$this->exists = true;

		$this->recently_created = true;

		// Set the ID on the model.
		$this->set_attribute( $this->get_key_name(), $insert_id );

		$this->trigger( 'created' );

		return true;
	}

	/**
	 * Run perform insert object into database.
	 *
	 * @param  array $attributes The attributes to insert.
	 * @return int|void
	 */
	protected function doing_insert( $attributes ) {
		throw new \RuntimeException( 'The insert action is not supported in the [' . get_class( $this ) . ']' );
	}

	/**
	 * TODO: ...
	 *
	 * Destroy the models for the given IDs.
	 *
	 * @param  array|int $ids
	 *
	 * @return int
	 */
	public static function destroy( $ids ) {
		// We'll initialize a count here so we will return the total number of deletes
		// for the operation. The developers can then check this number as a boolean
		// type value or get this total count of records deleted for logging, etc.
		$count = 0;

		$ids = is_array( $ids ) ? $ids : func_get_args();

		// We will actually pull the models from the database table and call delete on
		// each of them individually so that their events get fired properly with a
		// correct set of attributes in case the developers wants to check these.
		$key = ( $instance = new static )->get_key_name();

		foreach ( $instance->whereIn( $key, $ids )->get() as $model ) {
			if ( $model->delete() ) {
				$count ++;
			}
		}

		return $count;
	}

	/**
	 * Delete the model from the database.
	 *
	 * @param  bool $force Optional. Whether to bypass trash and force deletion.
	 * @return bool|null
	 */
	public function delete( $force = false ) {
		// If the model doesn't exist, there is nothing to delete so we'll just return
		// immediately and not do anything else. Otherwise, we will continue with a
		// deletion process on the model, firing the proper events, and so forth.
		if ( ! $this->exists() ) {
			return null;
		}

		if ( false === $this->trigger( 'deleting', true ) ) {
			return false;
		}

		// Pass the action to subclass to process.
		if ( ! $this->doing_delete( $force ) ) {
			return false;
		}

		$this->exists = false;

		$this->flush_cache();

		$this->trigger( 'deleted' );

		return true;
	}

	/**
	 * Perform delete the model from the database.
	 *
	 * @param  bool $force Optional. Whether to bypass trash and force deletion.
	 * @return bool
	 */
	protected function doing_delete( $force ) {
		throw new \RuntimeException( 'The delete action is not supported in the [' . get_class( $this ) . ']' );
	}

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
		return ( new Builder( $this->new_query() ) )->set_model( $this );
	}

	/**
	 * Get a new query instance.
	 *
	 * @return \Awethemes\WP_Object\Query\Query
	 */
	public function new_query() {
		throw new \RuntimeException( 'Query is not supported in the [' . get_class( $this ) . ']' );
	}

	/**
	 * Get a new database query builder.
	 *
	 * @return \Awethemes\WP_Object\Database\Builder
	 */
	public function new_db_query() {
		return Database\Database::get_connection()->newQuery();
	}

	/**
	 * Create a new Collection instance.
	 *
	 * @param mixed $models An array of models.
	 * @param bool  $map    Should map this class into.
	 *
	 * @return \Awethemes\WP_Object\Collection
	 */
	public function new_collection( $models, $map = false ) {
		$collect = new Collection( $models );

		return ! $map ? $collect : $collect->map_into( get_class( $this ) );
	}

	/**
	 * Returns the WP internal type, e.g: "post", "term", "user", etc.
	 *
	 * @return string
	 */
	public function resolve_internal_type() {
		return 'post';
	}

	/**
	 * Get the object ID.
	 *
	 * @return int
	 */
	public function get_id() {
		return (int) $this->get_attribute( $this->get_key_name() );
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
	 * Get the table associated with the model.
	 *
	 * @return string
	 */
	public function get_table() {
		return $this->table ?: $this->object_type;
	}

	/**
	 * Get the primary key for the model.
	 *
	 * @return string
	 */
	public function get_key_name() {
		return $this->primary_key ?: 'ID';
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
	 * Returns the value at specified offset.
	 *
	 * @param  string $offset The offset to retrieve.
	 * @return mixed
	 */
	public function offsetGet( $offset ) {
		return $this->get_attribute( $offset );
	}

	/**
	 * Assigns a value to the specified offset.
	 *
	 * @param string $offset The offset to assign the value to.
	 * @param mixed  $value  The value to set.
	 */
	public function offsetSet( $offset, $value ) {
		$this->set_attribute( $offset, $value );
	}

	/**
	 * Whether or not an offset exists.
	 *
	 * @param  string $offset The offset to check for.
	 * @return bool
	 */
	public function offsetExists( $offset ) {
		return ! is_null( $this->get_attribute( $offset ) );
	}

	/**
	 * Unsets an offset.
	 *
	 * @param string $offset The offset to unset.
	 */
	public function offsetUnset( $offset ) {
		unset( $this->attributes[ $offset ] );
	}

	/**
	 * Dynamically retrieve attributes on the model.
	 *
	 * @param  string $key The attribute key name.
	 * @return mixed
	 */
	public function __get( $key ) {
		return $this->get_attribute( $key );
	}

	/**
	 * Dynamically set attributes on the model.
	 *
	 * @param  string $key   The attribute key name.
	 * @param  mixed  $value The attribute value.
	 * @return void
	 */
	public function __set( $key, $value ) {
		$this->set_attribute( $key, $value );
	}

	/**
	 * Determine if an attribute exists on the model.
	 *
	 * @param  string $key The attribute key name.
	 * @return bool
	 */
	public function __isset( $key ) {
		return $this->offsetExists( $key );
	}

	/**
	 * Unset an attribute on the model.
	 *
	 * @param  string $key The attribute key name to remove.
	 * @return void
	 */
	public function __unset( $key ) {
		$this->offsetUnset( $key );
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
