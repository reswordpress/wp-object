<?php

use Awethemes\Database\Database;
use Awethemes\WP_Object\Query\DB_Query;
use Awethemes\WP_Object\Query\Post_Query;
use Awethemes\WP_Object\Query\Query;

class Query_Test extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function testPostQuery() {
		$query = new Post_Query( [ 'post_type' => 'post' ] );
		$query->set_primary_key( 'ID' );
		$query->set_object_type( 'post' );

		$this->assertInstanceOf( Query::class, $query );
		$this->assertInstanceOf( \Awethemes\WP_Object\Query\Query_Vars::class, $query->get_query_vars() );
		$this->assertInstanceOf( \WP_Query::class, $query->do_query( [] ) );

		$this->assertQueryActionsWork( $query );
	}

	public function testDBQuery() {
		$query = new DB_Query( Database::table( 'posts' ) );
		$query->set_table( 'posts' );
		$query->set_primary_key( 'ID' );

		$this->assertInstanceOf( Query::class, $query );
		$this->assertInstanceOf( \Awethemes\Database\Builder::class, $query->get_query_vars() );
		$this->assertInternalType( 'array', $query->do_query( $query->get_query_vars() ) );

		$this->assertQueryActionsWork( $query );
	}

	protected function assertQueryActionsWork( Query $query ) {
		// Find by ID.
		$p1 = $this->factory->post->create();

		$g1 = $query->get_by_id( $p1 );
		$this->assertArrayHasKey( 'ID', $g1 );
		$this->assertEquals( $p1, $g1['ID'] );

		// Actions.
		$insert_id = $query->insert( [ 'post_type' => 'abc' ] );
		clean_post_cache( $insert_id );
		$this->assertEquals( 'abc', get_post_type( $insert_id ) );

		$updated = $query->update( $insert_id, [ 'post_type' => 'ddd' ] );
		clean_post_cache( $insert_id );
		$this->assertNotFalse( $updated );
		$this->assertGreaterThan( 0, $updated );
		$this->assertEquals( 'ddd', get_post_type( $insert_id ) );

		if ( $query instanceof \Awethemes\WP_Object\Post ) {
			$query->delete( $insert_id, false );
			clean_post_cache( $insert_id );
			$this->assertEquals( 'trash', get_post_status( $insert_id ) );
		}

		$query->delete( $insert_id, true );
		clean_post_cache( $insert_id );
		$this->assertFalse( get_post_status( $insert_id ) );
	}
}
