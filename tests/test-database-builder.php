<?php

use Awethemes\WP_Object\Database\Database;
use Awethemes\WP_Object\Database\WP_Connection;

class Database_Builder_Test extends WP_UnitTestCase {
	/* @var \Awethemes\WP_Object\Database\WP_Connection */
	protected $connection;

	public function setUp() {
		parent::setUp();

		global $wpdb;
		$this->connection = new WP_Connection($wpdb);
	}

	public function testTable() {
		global $wpdb;
		$table = $this->connection->table( 'posts' );

		$this->assertInstanceOf( \Awethemes\WP_Object\Database\Builder::class, $table );
		$this->assertInstanceOf( \Database\Query\Grammars\MySqlGrammar::class, $table->getGrammar() );

		$this->assertEquals( "select * from `{$wpdb->posts}`", $table->toSql() );
	}

	public function testRunQuery() {
		global $wpdb;

		$this->factory->post->create_many( 10 );
		Database::enableQueryLog();

		dump( Database::select( "SELECT * FROM TABLE {$wpdb->posts} LIMIT 10" ) );
		dump( Database::get_connection()->getLogger());
	}
}
