<?php

use Awethemes\WP_Object\Builder;
use Awethemes\WP_Object\Query\Post_Query;

class Query_Builder_Test extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function testConstructor() {
		$query = new Builder( [
			'include' => 1,
			'exclude' => 2,
		], new Post_Query );

		$this->assertEquals( [
			'include' => 1,
			'exclude' => 2,
		], $query->get_query_vars() );
	}

	public function testFluent() {
		$query = new Builder( [ 'post_type' => 'post' ], new Post_Query );

		$query->post__in     = 1;
		$query->post__not_in = 2;

		$this->assertArrayHasKey( 'post__in', $query->get_query_vars() );
		$this->assertArrayHasKey( 'post_type', $query->get_query_vars() );
		$this->assertArrayHasKey( 'post__not_in', $query->get_query_vars() );

		$this->assertEquals( 1, $query->post__in );
		$this->assertEquals( 2, $query['post__not_in'] );
	}

	public function testFluentCallFunction() {
		$query = new Builder( [ 'post_type' => 'post' ], new Post_Query );

		$query
			->post__in( 1 )
			->post__not_in( 2 )
			->suppress_filters();

		$this->assertArrayHasKey( 'post__in', $query->get_query_vars() );
		$this->assertArrayHasKey( 'post__not_in', $query->get_query_vars() );
		$this->assertArrayHasKey( 'suppress_filters', $query->get_query_vars() );

		$this->assertEquals( 1, $query->post__in );
		$this->assertEquals( 2, $query['post__not_in'] );
		$this->assertSame( true, $query['suppress_filters'] );
	}

	public function testQueryPost() {
		$posts = $this->factory->post->create_many( 10, [ 'post_type' => 'custom_post' ] );
		$this->assertCount( 10, Demo_Post_Model::all() );
	}
}

class Demo_Post_Model extends \Awethemes\WP_Object\Post {
	protected $object_type = 'custom_post';
}
