<?php
namespace Awethemes\WP_Object\Database;

use Database\ConnectionInterface;

/**
 * Class Database.
 *
 * @method static array                                          fetch( $query, array $bindings = [] )
 * @method static array                                          get_row( $query, array $bindings = [] )
 * @method static array                                          select( $query, array $bindings = [] )
 * @method static array                                          get_results( $query, array $bindings = [] )
 * @method static array                                          fetchAll( $query, array $bindings = [] )
 * @method static mixed                                          get_var( $query, array $bindings = [] )
 * @method static mixed                                          fetchOne( $query, array $bindings = [] )
 * @method static int|false                                      query( $query, array $bindings = [] )
 * @method static mixed                                          transaction( \Closure $callback )
 * @method static array                                          pretend( \Closure $callback )
 * @method static bool                                           pretending()
 * @method static \Psr\Log\LoggerInterface|\Database\QueryLogger getLogger()
 * @method static bool                                           logging()
 * @method static \Awethemes\WP_Object\Database\WP_Connection    enableQueryLog()
 * @method static \Awethemes\WP_Object\Database\WP_Connection    disableQueryLog()
 *
 * @package Awethemes\WP_Object\Database
 */
class Database {
	/**
	 * The database connection.
	 *
	 * @var \Awethemes\WP_Object\Database\WP_Connection
	 */
	protected static $connection;

	/**
	 * An array of forward call methods.
	 *
	 * @var array
	 */
	protected static $forwards = [
		'get_row'     => 'fetch',
		'get_var'     => 'fetchOne',
		'get_results' => 'fetchAll',
		'select'      => 'fetchAll',
	];

	/**
	 * Begin a fluent query against a database table.
	 *
	 * @param  string $table
	 *
	 * @return \Awethemes\WP_Object\Database\Builder
	 */
	public static function table( $table ) {
		return static::get_connection()->table( $table );
	}

	/**
	 * Get the connection instance.
	 *
	 * @return \Database\ConnectionInterface|\Awethemes\WP_Object\Database\WP_Connection
	 */
	public static function get_connection() {
		global $wpdb;

		if ( is_null( static::$connection ) ) {
			static::$connection = new WP_Connection( $wpdb );
		}

		return static::$connection;
	}

	/**
	 * Set the connection implementation.
	 *
	 * @param \Database\ConnectionInterface $connection
	 */
	public static function set_connection( ConnectionInterface $connection ) {
		static::$connection = $connection;
	}

	/**
	 * Handle forward call connection methods.
	 *
	 * @param string $name
	 * @param array  $arguments
	 *
	 * @return mixed
	 */
	public static function __callStatic( $name, $arguments ) {
		$connection = static::get_connection();

		if ( array_key_exists( $name, static::$forwards ) ) {
			$name = static::$forwards[ $name ];
		}

		if ( method_exists( $connection, $name ) ) {
			return $connection->{$name}( ...$arguments );
		}

		throw new \BadMethodCallException( 'Method [' . $name . '] in class [' . get_class( $connection ) . '] does not exist.' );
	}
}
