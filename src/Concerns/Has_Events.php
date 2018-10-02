<?php
namespace Awethemes\WP_Object\Concerns;

trait Has_Events {
	/**
	 * User exposed observable events.
	 *
	 * These are extra user-defined events observers may subscribe to.
	 *
	 * @var array
	 */
	protected $observables = [];

	/**
	 * Register observers with the model.
	 *
	 * @param  object|array|string $classes
	 *
	 * @return void
	 */
	public static function observe( $classes ) {
		$instance = new static;

		foreach ( Arr::wrap( $classes ) as $class ) {
			$instance->registerObserver( $class );
		}
	}

	/**
	 * Register a single observer with the model.
	 *
	 * @param  object|string $class
	 *
	 * @return void
	 */
	protected function registerObserver( $class ) {
		$className = is_string( $class ) ? $class : get_class( $class );

		// When registering a model observer, we will spin through the possible events
		// and determine if this observer has that method. If it does, we will hook
		// it into the model's event system, making it convenient to watch these.
		foreach ( $this->getObservableEvents() as $event ) {
			if ( method_exists( $class, $event ) ) {
				static::on( $event, $className . '@' . $event );
			}
		}
	}

	/**
	 * Get the observable event names.
	 *
	 * @return array
	 */
	public function getObservableEvents() {
		return array_merge(
			[
				'retrieved',
				'creating',
				'created',
				'updating',
				'updated',
				'saving',
				'saved',
				'restoring',
				'restored',
				'deleting',
				'deleted',
				'forceDeleted',
			],
			$this->observables
		);
	}

	/**
	 * Set the observable event names.
	 *
	 * @param  array $observables
	 *
	 * @return $this
	 */
	public function setObservableEvents( array $observables ) {
		$this->observables = $observables;

		return $this;
	}

	/**
	 * Add an observable event name.
	 *
	 * @param  array|mixed $observables
	 *
	 * @return void
	 */
	public function addObservableEvents( $observables ) {
		$this->observables = array_unique( array_merge(
			$this->observables, is_array( $observables ) ? $observables : func_get_args()
		) );
	}

	/**
	 * Remove an observable event name.
	 *
	 * @param  array|mixed $observables
	 *
	 * @return void
	 */
	public function removeObservableEvents( $observables ) {
		$this->observables = array_diff(
			$this->observables, is_array( $observables ) ? $observables : func_get_args()
		);
	}

	/**
	 * Register a model event with the dispatcher.
	 *
	 * @param  string          $event
	 * @param  \Closure|string $callback
	 *
	 * @return void
	 */
	protected static function on( $event, $callback ) {
	}

	/**
	 * Fire the given event for the model.
	 *
	 * @param  string $event
	 * @param  bool   $halt
	 *
	 * @return mixed
	 */
	protected function trigger( $event, $filter = false ) {
	}

	/**
	 * Prefix for action and filter hooks for this object.
	 *
	 * @param  string $hook_name Hook name without prefix.
	 * @return string
	 */
	protected function prefix( $hook_name ) {
		return sprintf( 'wp_%s_%s', $this->object_type, $hook_name );
	}

	/**
	 * Register a retrieved model event with the dispatcher.
	 *
	 * @param  \Closure|string $callback
	 *
	 * @return void
	 */
	public static function retrieved( $callback ) {
		static::on( 'retrieved', $callback );
	}

	/**
	 * Register a saving model event with the dispatcher.
	 *
	 * @param  \Closure|string $callback
	 *
	 * @return void
	 */
	public static function saving( $callback ) {
		static::on( 'saving', $callback );
	}

	/**
	 * Register a saved model event with the dispatcher.
	 *
	 * @param  \Closure|string $callback
	 *
	 * @return void
	 */
	public static function saved( $callback ) {
		static::on( 'saved', $callback );
	}

	/**
	 * Register an updating model event with the dispatcher.
	 *
	 * @param  \Closure|string $callback
	 *
	 * @return void
	 */
	public static function updating( $callback ) {
		static::on( 'updating', $callback );
	}

	/**
	 * Register an updated model event with the dispatcher.
	 *
	 * @param  \Closure|string $callback
	 *
	 * @return void
	 */
	public static function updated( $callback ) {
		static::on( 'updated', $callback );
	}

	/**
	 * Register a creating model event with the dispatcher.
	 *
	 * @param  \Closure|string $callback
	 *
	 * @return void
	 */
	public static function creating( $callback ) {
		static::on( 'creating', $callback );
	}

	/**
	 * Register a created model event with the dispatcher.
	 *
	 * @param  \Closure|string $callback
	 *
	 * @return void
	 */
	public static function created( $callback ) {
		static::on( 'created', $callback );
	}

	/**
	 * Register a deleting model event with the dispatcher.
	 *
	 * @param  \Closure|string $callback
	 *
	 * @return void
	 */
	public static function deleting( $callback ) {
		static::on( 'deleting', $callback );
	}

	/**
	 * Register a deleted model event with the dispatcher.
	 *
	 * @param  \Closure|string $callback
	 *
	 * @return void
	 */
	public static function deleted( $callback ) {
		static::on( 'deleted', $callback );
	}
}
